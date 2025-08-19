<?php

namespace App\Http\Controllers;

use App\Models\BeginningBalance;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Coa;
use App\Models\CoaType;
use App\Models\Customer;
use App\Models\Debt;
use App\Models\Department;
use App\Models\Journal;
use App\Models\GeneralJournal;
use App\Models\GeneralJournalDetail;
use App\Models\Receivable;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Log;

class JournalController extends Controller
{
    public function index()
    {
        $companies = Company::all();
        $departments = Department::where('department_code', 'DP01')->first();
        $journals = Journal::where('department_code', 'DP01')->where(function ($query) {
            $query->where('debet_nominal', '>', 0)
                ->orWhere('credit_nominal', '>', 0);
        })
            ->orderBy('document_date', 'asc') // First, order by document_date
            ->orderBy('document_number', 'asc') // Then, order by document_number
            ->orderByRaw('CASE WHEN debet_nominal > 0 THEN 0 ELSE 1 END') // Prioritize debet rows first
            ->orderBy('id', 'desc') // Finally, order by id in descending order
            ->get();
        $coas = Coa::all();

        return view('transaction.journal.journal_list', compact('companies', 'departments', 'journals', 'coas'));
    }

    public function fetchItems(Request $request)
    {

        $dateFrom = $request->input('date_from') . ' 00:00:00'; // Set time to 00:00:00
        $dateTo = $request->input('date_to') . ' 23:59:59'; // Set time to 23:59:59
        $journals = Journal::with('coas')->where('department_code', 'DP01')
            ->whereBetween('document_date', [$dateFrom, $dateTo])
            ->where(function ($query) {
                $query->where('debet_nominal', '>', 0)
                    ->orWhere('credit_nominal', '>', 0);
            })
            ->orderBy('document_date', 'asc') // First, order by document_date
            ->orderBy('document_number', 'asc') // Then, order by document_number
            ->orderByRaw('CASE WHEN debet_nominal > 0 THEN 0 ELSE 1 END') // Prioritize debet rows first
            ->orderBy('id', 'desc') // Finally, order by id in descending order
            ->get();
        $data = $journals->map(function ($journal) {
            return [
                'account_number' => $journal->account_number,
                'account_name' => $journal->coas->account_name,
                'document_number' => $journal->document_number,
                'document_date' => Carbon::parse($journal->document_date)->format('d M Y'), // Format date as needed
                'debet_nominal' => number_format($journal->debet_nominal, 0, '', '.'),
                'credit_nominal' => number_format($journal->credit_nominal, 0, '', '.'),
                'notes' => $journal->notes,
            ];
        });

        return response()->json($data);
    }

    public function fetchLedgerItems(Request $request)
    {
        $dateFrom = $request->input('date_from') . ' 00:00:00';
        $dateTo = $request->input('date_to') . ' 23:59:59';
        $coaId = $request->input('coa_id');

        // Query for SAWAL (journal entries before date_from)
        $sawalQuery = DB::table('journal as j')
            ->join('coa as c', 'j.account_number', '=', 'c.account_number')
            ->select(
                'j.account_number',
                'c.account_name',
                'j.notes',
                'c.normal_balance',
                DB::raw('SUM(j.debet_nominal) as total_debet'),
                DB::raw('SUM(j.credit_nominal) as total_credit'),
                DB::raw('SUM(
                    CASE
                        WHEN c.normal_balance = \'Credit\' THEN j.credit_nominal - j.debet_nominal
                        ELSE j.debet_nominal - j.credit_nominal
                    END
                ) as sawal_balance')
            )
            ->where('j.department_code', 'DP01')
            ->where('j.document_date', '<', $dateFrom)
            ->whereNotNull('j.account_number')
            ->where(function ($query) {
                $query->where('j.debet_nominal', '>', 0)
                    ->orWhere('j.credit_nominal', '>', 0);
            });

        if ($coaId) {
            $sawalQuery->where('j.account_number', $coaId);
        }

        $sawalData = $sawalQuery->groupBy('j.account_number', 'c.account_name', 'j.notes', 'c.normal_balance')
            ->get()
            ->keyBy('account_number');

        // Query for period journal entries
        $journals = DB::table('journal as j')
            ->join('coa as c', 'j.account_number', '=', 'c.account_number')
            ->select(
                'j.account_number',
                'j.document_number',
                'j.document_date',
                'c.account_name',
                'j.notes',
                'j.debet_nominal',
                'j.credit_nominal',
                'c.normal_balance',
                DB::raw('SUM(
                    CASE
                        WHEN c.normal_balance = \'Credit\' THEN j.credit_nominal - j.debet_nominal
                        ELSE j.debet_nominal - j.credit_nominal
                    END
                ) OVER (PARTITION BY j.account_number ORDER BY j.document_date ASC, j.id ASC) AS period_balance')
            )
            ->where('j.department_code', 'DP01')
            ->whereBetween('j.document_date', [$dateFrom, $dateTo])
            ->whereNotNull('j.account_number')
            ->where(function ($query) {
                $query->where('j.debet_nominal', '>', 0)
                    ->orWhere('j.credit_nominal', '>', 0);
            });

        if ($coaId) {
            $journals->where('j.account_number', $coaId);
        }

        $journals = $journals->orderBy('j.account_number')
            ->orderBy('j.document_date', 'asc')
            ->orderBy('j.id', 'asc')
            ->get();

        // Prepare data for DataTable
        $data = [];
        $currentAccount = null;

        foreach ($journals as $journal) {
            if ($currentAccount !== $journal->account_number) {
                // Add SAWAL row for new account
                if (isset($sawalData[$journal->account_number])) {
                    $sawal = $sawalData[$journal->account_number];
                    $debet = $sawal->total_debet > $sawal->total_credit ? $sawal->total_debet - $sawal->total_credit : 0;
                    $credit = $sawal->total_credit > $sawal->total_debet ? $sawal->total_credit - $sawal->total_debet : 0;
                    $balance = $sawal->sawal_balance;

                    $data[] = [
                        'account_number' => $journal->account_number,
                        'document_number' => 'SAWAL',
                        'document_date' => '',
                        'account_name' => $journal->account_name,
                        'notes' => $journal->notes,
                        'debet_nominal' => number_format($debet, 0, '', '.'),
                        'credit_nominal' => number_format($credit, 0, '', '.'),
                        'balance' => number_format($balance, 0, '', '.'),
                        'is_sawal' => true
                    ];
                } else {
                    // No SAWAL data, add zeroed SAWAL row
                    $data[] = [
                        'account_number' => $journal->account_number,
                        'document_number' => 'SAWAL',
                        'document_date' => '',
                        'account_name' => $journal->account_name,
                        'notes' => $journal->notes,
                        'debet_nominal' => number_format(0, 0, '', '.'),
                        'credit_nominal' => number_format(0, 0, '', '.'),
                        'balance' => number_format(0, 0, '', '.'),
                        'is_sawal' => true
                    ];
                }
                $currentAccount = $journal->account_number;
            }

            // Add journal entry with balance including SAWAL
            $sawalBalance = isset($sawalData[$journal->account_number]) ? $sawalData[$journal->account_number]->sawal_balance : 0;
            $totalBalance = $sawalBalance + $journal->period_balance;

            $data[] = [
                'account_number' => $journal->account_number,
                'document_number' => $journal->document_number,
                'document_date' => Carbon::parse($journal->document_date)->format('d M Y'),
                'account_name' => $journal->account_name,
                'notes' => $journal->notes,
                'debet_nominal' => number_format($journal->debet_nominal, 0, '', '.'),
                'credit_nominal' => number_format($journal->credit_nominal, 0, '', '.'),
                'balance' => number_format($totalBalance, 0, '', '.'),
                'is_sawal' => false
            ];
        }

        return response()->json($data);
    }


    public function ledger()
    {
        // Fetch ledger data with balance calculation
        $journals = DB::table('journal as j')
            ->join('coa as c', 'j.account_number', '=', 'c.account_number')
            ->select(
                'j.account_number',
                'j.document_number',
                'j.document_date',
                'c.account_name',
                'c.account_type',
                'j.debet_nominal',
                'j.credit_nominal',
                DB::raw('SUM(
                CASE
                    WHEN c.normal_balance = \'Credit\' THEN j.credit_nominal - j.debet_nominal
                    ELSE j.debet_nominal - j.credit_nominal
                END
            ) OVER (PARTITION BY j.account_number ORDER BY j.id ASC) AS balance')
            )
            ->where('j.department_code','DP01')
            ->whereNotNull('j.account_number')
            ->where(function ($query) {
                $query->where('j.debet_nominal', '>', 0)
                    ->orWhere('j.credit_nominal', '>', 0);
            })
            ->orderBy('j.account_number')
            ->orderBy('j.document_date', 'asc')
            ->get();


        $coas = DB::table('coa')->select('account_number', 'account_name')->get();


        return view('transaction.journal.journal_ledger', compact('journals', 'coas'));
    }

    public function generateTrialBalancePdf(Request $request)
    {
        $data = $this->generateTrialBalanceData($request, 'pdf');
        return view('transaction.journal.trial_balance_pdf', $data);
    }

    public function showTrialBalance(Request $request)
    {
        $coas = DB::table('coa')
        ->join('coa_type', 'coa.account_type', '=', 'coa_type.id')
        ->select('coa.account_number', 'coa.account_name', 'coa.account_type', 'coa_type.id')
        ->get();

        return view('transaction.journal.trial_balance');
    }

    public function fetchTrialBalance(Request $request)
    {
        return $this->generateTrialBalanceData($request, 'json');
    }

    private function generateTrialBalanceData(Request $request, $outputType)
    {
        // Parse the selected date (e.g., "2025-01-01")
        $selectedDate = Carbon::parse($request->input('date'));

        // Derive date_from (start of the month) and date_to (end of the month)
        $dateFrom = $selectedDate->startOfMonth()->toDateTimeString();
        $dateTo = $selectedDate->endOfMonth()->toDateTimeString();

        $coaId = $request->input('coa_id');

        // SAWAL Query (before date_from)
        $sawalQuery = DB::table('journal as j')
            ->join('coa as c', 'j.account_number', '=', 'c.account_number')
            ->join('coa_type as ct', 'c.account_type', '=', 'ct.id')
            ->select(
                'ct.account_sub_type',
                'c.account_name as account',
                'j.account_number as account_code',
                DB::raw("
                    CASE
                        WHEN COALESCE(SUM(j.debet_nominal), 0) >= COALESCE(SUM(j.credit_nominal), 0)
                        THEN COALESCE(SUM(j.debet_nominal), 0) - COALESCE(SUM(j.credit_nominal), 0)
                        ELSE 0
                    END AS sawal_debit
                "),
                DB::raw("
                    CASE
                        WHEN COALESCE(SUM(j.debet_nominal), 0) < COALESCE(SUM(j.credit_nominal), 0)
                        THEN COALESCE(SUM(j.credit_nominal), 0) - COALESCE(SUM(j.debet_nominal), 0)
                        ELSE 0
                    END AS sawal_credit
                ")
            )
            ->where('j.department_code', 'DP01')
            ->where('j.document_date', '<', $dateFrom)
            ->whereNotNull('j.account_number')
            ->where(function ($query) {
                $query->where('j.debet_nominal', '>', 0)
                    ->orWhere('j.credit_nominal', '>', 0);
            })
            ->when($coaId && $coaId !== 'undefined', function ($query) use ($coaId) {
                return $query->where('j.account_number', '=', $coaId);
            })
            ->groupBy('ct.account_sub_type', 'c.account_name', 'j.account_number')
            ->havingRaw('COALESCE(SUM(j.debet_nominal), 0) > 0 OR COALESCE(SUM(j.credit_nominal), 0) > 0');

        $sawalData = $sawalQuery->get()->keyBy('account_code');

        // Period Query (date_from to date_to)
        $periodQuery = DB::table('journal as j')
            ->join('coa as c', 'j.account_number', '=', 'c.account_number')
            ->join('coa_type as ct', 'c.account_type', '=', 'ct.id')
            ->select(
                'ct.account_sub_type',
                'c.account_name as account',
                'j.account_number as account_code',
                DB::raw("SUM(j.debet_nominal) AS transaction_debit"),
                DB::raw("SUM(j.credit_nominal) AS transaction_credit")
            )
            ->where('j.department_code', 'DP01')
            ->whereBetween('j.document_date', [$dateFrom, $dateTo])
            ->whereNotNull('j.account_number')
            ->where('document_number','not like','JP%')
            ->where(function ($query) {
                $query->where('j.debet_nominal', '>', 0)
                    ->orWhere('j.credit_nominal', '>', 0);
            })
            ->when($coaId && $coaId !== 'undefined', function ($query) use ($coaId) {
                return $query->where('j.account_number', '=', $coaId);
            })
            ->groupBy('ct.account_sub_type', 'c.account_name', 'j.account_number')
            ->havingRaw('SUM(j.debet_nominal) > 0 OR SUM(j.credit_nominal) > 0');

        $periodData = $periodQuery->get()->keyBy('account_code');

        // Adjustment Query (from BeginningBalance)
        $adjustmentQuery = BeginningBalance::select(
            'account_number as account_code',
            DB::raw("SUM(CASE WHEN account_number = '3200300' THEN adjust_debit_nominal ELSE adjust_credit_nominal END) AS adjustment_debit"),
            DB::raw("SUM(CASE WHEN account_number = '3200300' THEN adjust_credit_nominal ELSE adjust_debit_nominal END) AS adjustment_credit")
        )
            ->where('account_number', '>=', '3000000')
            ->where('periode', $selectedDate->format('Ym'))
            ->where(function ($query) {
                $query->where('adjust_debit_nominal', '>', 0)
                    ->orWhere('adjust_credit_nominal', '>', 0);
            })
            ->when($coaId && $coaId !== 'undefined', function ($query) use ($coaId) {
                return $query->where('account_number', '=', $coaId);
            })
            ->groupBy('account_number');

        $adjustmentData = $adjustmentQuery->get()->keyBy('account_code');

        // Combine Data
        $allAccounts = collect(array_unique(array_merge(
            $sawalData->pluck('account_code')->toArray(),
            $periodData->pluck('account_code')->toArray(),
            $adjustmentData->pluck('account_code')->toArray()
        )))->map(function ($account_code) use ($sawalData, $periodData, $adjustmentData) {
            $sawal = $sawalData->get($account_code, (object)[
                'account_sub_type' => null,
                'account' => null,
                'account_code' => $account_code,
                'sawal_debit' => 0,
                'sawal_credit' => 0
            ]);
            $period = $periodData->get($account_code, (object)[
                'account_sub_type' => null,
                'account' => null,
                'account_code' => $account_code,
                'transaction_debit' => 0,
                'transaction_credit' => 0
            ]);
            $adjustment = $adjustmentData->get($account_code, (object)[
                'account_code' => $account_code,
                'adjustment_debit' => 0,
                'adjustment_credit' => 0
            ]);

            // Calculate final balance (sawal + transaction + adjustment)
            $netBalance = ($sawal->sawal_debit - $sawal->sawal_credit) +
                         ($period->transaction_debit - $period->transaction_credit) +
                         ($adjustment->adjustment_debit - $adjustment->adjustment_credit);

            $balanceDebit = $netBalance > 0 ? $netBalance : 0;
            $balanceCredit = $netBalance < 0 ? abs($netBalance) : 0;

            return [
                'account_sub_type' => $sawal->account_sub_type ?? $period->account_sub_type ?? 'Unknown',
                'account' => $sawal->account ?? $period->account ?? $account_code,
                'account_code' => $account_code,
                'sawal_debit' => $sawal->sawal_debit,
                'sawal_credit' => $sawal->sawal_credit,
                'transaction_debit' => $period->transaction_debit,
                'transaction_credit' => $period->transaction_credit,
                'adjustment_debit' => $adjustment->adjustment_debit,
                'adjustment_credit' => $adjustment->adjustment_credit,
                'balance_debit' => $balanceDebit,
                'balance_credit' => $balanceCredit
            ];
        })->sortBy('account_code');

        $trialBalanceData = $allAccounts->groupBy('account_sub_type')->map(function ($group) {
            return [
                'accountType' => $group[0]['account_sub_type'],
                'accounts' => $group->toArray()
            ];
        })->values();

        $data = [
            'trialBalanceData' => $trialBalanceData,
        ];

        return view('transaction.journal.trial_balance', compact('trialBalanceData'));
    }

    function convertMonthToStartOfMonth($month) {

        // Get the current year
        $currentYear = Carbon::now()->year;

        // Create a Carbon instance for the first day of the specified month in the current year
        $date = Carbon::createFromFormat('Y-m-d', "$currentYear-$month-01")
                    ->startOfDay(); // Ensure time is 00:00:00

        // Return the formatted date (e.g., "2025-01-01")
        return $date->toDateString();
    }

    public function payableReport(Request $request)
    {

        // Base query for suppliers and their debts within the date range
        $query = Supplier::with(['debts' => function($query) use ($request) {
            $query->select(
                'supplier_code',
                'document_number',
                'document_date',
                'due_date',
                'total_debt',
                DB::raw('total_debt - debt_balance as "Amount Paid"'),
                'debt_balance'
            )
            ->where('department_code', 'DP01');

            // Apply date filters if provided
            if ($request->filled('date_from')) {
                $query->where('document_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->where('document_date', '<=', $request->date_to);
            }

            $query->orderBy('document_date', 'asc')
                ->orderBy('document_number', 'asc');
        }])
        ->select('supplier_code', 'supplier_name')
        ->orderBy('supplier_name', 'asc');

        $suppliers = $query->get();

        // Calculate beginning balance for each supplier (debt_balance before date_from)
        $date_from = $request->filled('date_from') ? $request->date_from : null;
        foreach ($suppliers as $supplier) {
            $beginning_balance_query = DB::table('debt')
                ->where('supplier_code', $supplier->supplier_code)
                ->where('department_code', 'DP01');

            if ($date_from) {
                $beginning_balance_query->where('document_date', '<', $date_from);
            }

            $supplier->beginning_balance = $beginning_balance_query->sum('debt_balance');
        }

        return view('transaction.journal.payable_report', compact('suppliers'));
    }

    public function payableReportPdf(){
        $suppliers = Supplier::with(['debts' => function($query) {
            $query->select(
                'supplier_code', // Include supplier_id for the relationship
                'document_number',
                'document_date',
                'due_date',
                'total_debt',
                'debt_balance'
            )->where('department_code','DP01');
        }])
        ->select('supplier_code', 'supplier_name') // Select supplier code and name
        ->get();

        // Initialize a new collection to hold duplicated suppliers


        // Initialize totals
        $totalTotalDebt = 0;
        $totalDebtBalance = 0;

        // Calculate "Umur" and totals per supplier
        foreach ($suppliers as $supplier) {
            $supplier->total_debt = $supplier->debts->sum('total_debt');
            $supplier->debt_balance = $supplier->debts->sum('debt_balance');

            // Calculate "Umur"
            foreach ($supplier->debts as $debt) {
                if ($debt->due_date < now()) {
                    $debt->umur = now()->diffInDays($debt->due_date);
                } else {
                    $debt->umur = 0; // Set to 0 if due date is not yet reached
                }
            }

            // Accumulate overall totals
            $totalTotalDebt += $supplier->total_debt;
            $totalDebtBalance += $supplier->debt_balance;
        }
        $suppliers = $suppliers;
        // dd($suppliers,$totalTotalDebt,$totalDebtBalance);

        return view('transaction.journal.payable_report_pdf',compact('suppliers', 'totalTotalDebt', 'totalDebtBalance'));
    }

    public function receivableReport(Request $request){
        $customers = Customer::with(['receivables' => function($query) use ($request){
            $query->select(
                'customer_code', // Include supplier_id for the relationship
                'document_number',
                'document_date',
                'due_date',
                'total_debt',
                DB::raw('total_debt - debt_balance as "Amount Paid"'),
                'debt_balance'
            )->where('department_code','DP01');

            // Apply date filters if provided
            if ($request->filled('date_from')) {
                $query->where('document_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->where('document_date', '<=', $request->date_to);
            }
        }])
        ->select('customer_code', 'customer_name') // Select supplier code and name
        ->orderBy('group_customer','asc')
        ->orderBy('customer_name','asc')
        ->get();

        // Group customers by base customer code
        $groupedCustomers = [];
        foreach ($customers as $customer) {
            // Extract base customer code (e.g., '7000' from 'DP02.7000' or '7000')
            $baseCode = preg_replace('/^(DP\d{2}\.)?/', '', $customer->customer_code);

            if (!isset($groupedCustomers[$baseCode])) {
                $groupedCustomers[$baseCode] = [
                    'customer_name' => $customer->customer_name, // Use the name from the first occurrence
                    'customer_codes' => [], // Store all customer codes
                    'receivables' => [], // Store all receivables
                    'beginning_balance' => 0, // Initialize beginning balance
                ];
            }

            // Add customer code to the group
            $groupedCustomers[$baseCode]['customer_codes'][] = $customer->customer_code;

            // Add receivables to the group
            if ($customer->receivables) {
                $groupedCustomers[$baseCode]['receivables'] = array_merge(
                    $groupedCustomers[$baseCode]['receivables'],
                    $customer->receivables->toArray()
                );
            }
        }

        // Calculate beginning balance for each group
        $date_from = $request->filled('date_from') ? $request->date_from : null;
        foreach ($groupedCustomers as $baseCode => &$group) {
            $beginning_balance_query = DB::table('receivable')
                ->whereIn('customer_code', $group['customer_codes'])
                ->where('department_code', 'DP01');

            if ($date_from) {
                $beginning_balance_query->where('document_date', '<', $date_from);
            }

            $group['beginning_balance'] = $beginning_balance_query->sum('debt_balance');
        }

        // Convert grouped customers to a collection for easier handling in the view
        $groupedCustomers = collect($groupedCustomers);

        return view('transaction.journal.receivable_report', compact('groupedCustomers'));
    }

    public function receivableReportPdf(){
        $customers = Customer::with(['receivables' => function($query) {
            $query->select(
                'customer_code', // Include supplier_id for the relationship
                'document_number',
                'document_date',
                'due_date',
                'total_debt',
                'debt_balance'
            )->where('department_code','DP01');
        }])
        ->select('customer_code', 'customer_name') // Select supplier code and name
        ->get();

        // Initialize totals
        $totalTotalDebt = 0;
        $totalDebtBalance = 0;

        // Calculate "Umur" and totals per customer
        foreach ($customers as $customer) {
            $customer->total_debt = $customer->receivables->sum('total_debt');
            $customer->debt_balance = $customer->receivables->sum('debt_balance');

            // Calculate "Umur"
            foreach ($customer->receivables as $debt) {
                if ($debt->due_date < now()) {
                    $debt->umur = now()->diffInDays($debt->due_date);
                } else {
                    $debt->umur = 0; // Set to 0 if due date is not yet reached
                }
            }

            // Accumulate overall totals
            $totalTotalDebt += $customer->total_debt;
            $totalDebtBalance += $customer->debt_balance;
        }
        // dd($suppliers,$totalTotalDebt,$totalDebtBalance);

        return view('transaction.journal.receivable_report_pdf',compact('customers', 'totalTotalDebt', 'totalDebtBalance'));
    }
    public function receivableReportDetail(){
        $customers = Customer::with(['receivables' => function($query) {
            $query->select(
                'customer_code', // Include supplier_id for the relationship
                'document_number',
                'document_date',
                'due_date',
                'total_debt',
                DB::raw('total_debt - debt_balance as "Amount Paid"'),
                'debt_balance'
            )->where('department_code','DP01');
        }])
        ->select('customer_code', 'customer_name','sales','zone') // Select supplier code and name
        ->get();
        // dd($suppliers);

        return view('transaction.journal.receivable_detail_report',compact('customers'));
    }

    public function receivableReportDetailPdf(Request $request)
    {
        // Get parameters from request
        $salesman = $request->query('salesman');
        $zone = $request->query('zone');

        // Build the query
        $query = Customer::with(['receivables' => function($query) {
            $query->select(
                'customer_code',
                'document_number',
                'document_date',
                'due_date',
                'total_debt',
                'debt_balance'
            )->where('department_code', 'DP01');
        }])
        ->select('customer_code', 'customer_name', 'sales', 'zone');

        // Apply filters if parameters exist
        if ($salesman) {
            $query->where('sales', $salesman);
        }

        if ($zone) {
            $query->where('zone', $zone);
        }

        $customers = $query->get();

        // Initialize totals
        $totalTotalDebt = 0;
        $totalDebtBalance = 0;

        // Calculate "Umur" and totals per customer
        foreach ($customers as $customer) {
            $customer->total_debt = $customer->receivables->sum('total_debt');
            $customer->debt_balance = $customer->receivables->sum('debt_balance');

            // Calculate "Umur"
            foreach ($customer->receivables as $debt) {
                if ($debt->due_date < now()) {
                    $debt->umur = now()->diffInDays($debt->due_date);
                } else {
                    $debt->umur = 0;
                }
            }

            // Accumulate overall totals
            $totalTotalDebt += $customer->total_debt;
            $totalDebtBalance += $customer->debt_balance;
        }

        return view('transaction.journal.receivable_detail_report_pdf', compact('customers', 'totalTotalDebt', 'totalDebtBalance'));
    }

    private function getAgingReport($startDate = null, $endDate = null)
    {
        $referenceDate = $endDate ? Carbon::parse($endDate) : Carbon::now();

        $query = Debt::whereRaw('FLOOR(debt_balance) != 0')
            ->where('department_code', 'DP01')
            ->with('supplier')
            ->select('supplier_code', 'document_number', 'document_date', 'debt_balance', 'due_date');

        if ($startDate && $endDate) {
            $query->whereBetween('document_date', [$startDate, $endDate]);
        }

        $debts = $query->get();

        // Query for SAWAL (debts before start_date)
        $sawalQuery = Debt::whereRaw('FLOOR(debt_balance) != 0')
            ->where('department_code', 'DP01')
            ->with('supplier')
            ->select('supplier_code', 'document_number', 'document_date', 'debt_balance', 'due_date');

        if ($startDate) {
            $sawalQuery->where('document_date', '<', $startDate);
        }

        $sawalDebts = $sawalQuery->get();

        $agingReport = [];

        // Initialize supplier data with default sawal for all suppliers
        $allSuppliers = $debts->pluck('supplier')->unique('supplier_code');
        foreach ($allSuppliers as $supplier) {
            $supplierName = $supplier ? $supplier->supplier_name : 'Unknown Supplier';
            $agingReport[$supplierName] = [
                'total_amount_due' => 0,
                'debts' => [],
                'aging' => [
                    '0' => 0,
                    '1-30' => 0,
                    '31-60' => 0,
                    '61-90' => 0,
                    'over_90' => 0,
                ],
                'sawal' => [
                    'total' => 0,
                    'aging' => [
                        '0' => 0,
                        '1-30' => 0,
                        '31-60' => 0,
                        '61-90' => 0,
                        'over_90' => 0,
                    ],
                ],
            ];
        }

        // Process SAWAL debts
        foreach ($sawalDebts as $debt) {
            $supplier = $debt->supplier;
            $supplierName = $supplier ? $supplier->supplier_name : 'Unknown Supplier';

            // Calculate the age of the debt
            if ($debt->due_date < $referenceDate) {
                $debt->umur = $referenceDate->diffInDays($debt->due_date);
            } else {
                $debt->umur = 0;
            }

            // Initialize supplier data if not set
            if (!isset($agingReport[$supplierName])) {
                $agingReport[$supplierName] = [
                    'total_amount_due' => 0,
                    'debts' => [],
                    'aging' => [
                        '0' => 0,
                        '1-30' => 0,
                        '31-60' => 0,
                        '61-90' => 0,
                        'over_90' => 0,
                    ],
                    'sawal' => [
                        'total' => 0,
                        'aging' => [
                            '0' => 0,
                            '1-30' => 0,
                            '31-60' => 0,
                            '61-90' => 0,
                            'over_90' => 0,
                        ],
                    ],
                ];
            }

            // Update SAWAL totals
            $agingReport[$supplierName]['sawal']['total'] += $debt->debt_balance;
            $agingReport[$supplierName]['total_amount_due'] += $debt->debt_balance;

            // Update SAWAL aging totals
            if ($debt->umur == 0) {
                $agingReport[$supplierName]['sawal']['aging']['0'] += $debt->debt_balance;
                $agingReport[$supplierName]['aging']['0'] += $debt->debt_balance;
            } elseif ($debt->umur > 0 && $debt->umur <= 30) {
                $agingReport[$supplierName]['sawal']['aging']['1-30'] += $debt->debt_balance;
                $agingReport[$supplierName]['aging']['1-30'] += $debt->debt_balance;
            } elseif ($debt->umur > 30 && $debt->umur <= 60) {
                $agingReport[$supplierName]['sawal']['aging']['31-60'] += $debt->debt_balance;
                $agingReport[$supplierName]['aging']['31-60'] += $debt->debt_balance;
            } elseif ($debt->umur > 60 && $debt->umur <= 90) {
                $agingReport[$supplierName]['sawal']['aging']['61-90'] += $debt->debt_balance;
                $agingReport[$supplierName]['aging']['61-90'] += $debt->debt_balance;
            } else {
                $agingReport[$supplierName]['sawal']['aging']['over_90'] += $debt->debt_balance;
                $agingReport[$supplierName]['aging']['over_90'] += $debt->debt_balance;
            }

        }

        foreach ($debts as $debt) {
            $supplier = $debt->supplier;
            $supplierName = $supplier ? $supplier->supplier_name : 'Unknown Supplier';

            // Calculate the age of the debt using the reference date
            if ($debt->due_date < $referenceDate) {
                $debt->umur = $referenceDate->diffInDays($debt->due_date);
            } else {
                $debt->umur = 0;
            }

            // Initialize aging categories if not already set
            if (!isset($agingReport[$supplierName])) {
                $agingReport[$supplierName] = [
                    'total_amount_due' => 0,
                    'debts' => [],
                    'aging' => [
                        '0' => 0,
                        '1-30' => 0,
                        '31-60' => 0,
                        '61-90' => 0,
                        'over_90' => 0,
                    ],
                ];
            }

            // Add the current debt to the supplier's debts
            $agingReport[$supplierName]['debts'][] = $debt;

            // Update totals
            $agingReport[$supplierName]['total_amount_due'] += $debt->debt_balance;

            // Update aging totals based on the calculated umur
            if ($debt->umur == 0) {
                $agingReport[$supplierName]['aging']['0'] += $debt->debt_balance;
            } elseif ($debt->umur > 0 && $debt->umur <= 30) {
                $agingReport[$supplierName]['aging']['1-30'] += $debt->debt_balance;
            } elseif ($debt->umur > 30 && $debt->umur <= 60) {
                $agingReport[$supplierName]['aging']['31-60'] += $debt->debt_balance;
            } elseif ($debt->umur > 60 && $debt->umur <= 90) {
                $agingReport[$supplierName]['aging']['61-90'] += $debt->debt_balance;
            } else {
                $agingReport[$supplierName]['aging']['over_90'] += $debt->debt_balance;
            }
        }
        return $agingReport;
    }

    public function payableAging()
    {
        $startDate = request()->input('start_date');
        $endDate = request()->input('end_date');
        $today = Carbon::now();

        $agingReport = $this->getAgingReport($startDate, $endDate);


        return view('transaction.journal.payable_aging',compact('agingReport','today','startDate','endDate'));
    }

    public function payableAgingPdf()
    {
        $startDate = request()->input('start_date');
        $endDate = request()->input('end_date');


        $agingReport = $this->getAgingReport($startDate, $endDate);

        return view('transaction.journal.payable_aging_pdf', compact('agingReport','startDate','endDate'));
    }

    private function getAgingReport2($startDate = null, $endDate = null)
    {
        $referenceDate = $endDate ? Carbon::parse($endDate) : Carbon::now();

        $query = Receivable::where('debt_balance', '>', 0)
            ->join('customer as child', 'receivable.customer_code', '=', 'child.customer_code')
            ->select(
                'child.customer_name as head_customer_name',
                'receivable.document_number',
                'receivable.document_date',
                'receivable.debt_balance',
                'receivable.due_date'
            )
            ->orderBy('child.group_customer', 'asc')
            ->orderBy('child.customer_name', 'asc');

        if ($startDate && $endDate) {
            $query->whereBetween('document_date', [$startDate, $endDate]);
        }

        $receivables = $query->get();

        // Query for SAWAL (debts before start_date)
        $sawalQuery = Receivable::whereRaw('FLOOR(debt_balance) != 0')
            ->join('customer as child', 'receivable.customer_code', '=', 'child.customer_code')
            ->select(
                'child.customer_name as head_customer_name',
                'receivable.document_number',
                'receivable.document_date',
                'receivable.debt_balance',
                'receivable.due_date'
            )
            ->orderBy('child.group_customer', 'asc')
            ->orderBy('child.customer_name', 'asc');

        if ($startDate) {
            $sawalQuery->where('document_date', '<', $startDate);
        }

        $sawalDebts = $sawalQuery->get();

        $agingReport = [];

        // Get all unique customers from both receivables and sawal debts
        $allCustomers = $receivables->pluck('head_customer_name')
            ->merge($sawalDebts->pluck('head_customer_name'))
            ->unique();

        // Initialize supplier data with default values for all customers
        foreach ($allCustomers as $customer) {
            $agingReport[$customer] = [
                'total_amount_due' => 0,
                'debts' => [],
                'aging' => [
                    '0' => 0,
                    '1-30' => 0,
                    '31-60' => 0,
                    '61-90' => 0,
                    'over_90' => 0,
                ],
                'sawal' => [
                    'total' => 0,
                    'aging' => [
                        '0' => 0,
                        '1-30' => 0,
                        '31-60' => 0,
                        '61-90' => 0,
                        'over_90' => 0,
                    ],
                ],
            ];
        }

        foreach ($sawalDebts as $debt) {
            $customerName = $debt->head_customer_name;

            // Calculate the age of the debt
            if ($debt->due_date < $referenceDate) {
                $debt->umur = $referenceDate->diffInDays($debt->due_date);
            } else {
                $debt->umur = 0;
            }

            // Update SAWAL totals
            $agingReport[$customerName]['sawal']['total'] += $debt->debt_balance;
            $agingReport[$customerName]['total_amount_due'] += $debt->debt_balance;

            // Update SAWAL aging totals
            if ($debt->umur == 0) {
                $agingReport[$customerName]['sawal']['aging']['0'] += $debt->debt_balance;
                $agingReport[$customerName]['aging']['0'] += $debt->debt_balance;
            } elseif ($debt->umur > 0 && $debt->umur <= 30) {
                $agingReport[$customerName]['sawal']['aging']['1-30'] += $debt->debt_balance;
                $agingReport[$customerName]['aging']['1-30'] += $debt->debt_balance;
            } elseif ($debt->umur > 30 && $debt->umur <= 60) {
                $agingReport[$customerName]['sawal']['aging']['31-60'] += $debt->debt_balance;
                $agingReport[$customerName]['aging']['31-60'] += $debt->debt_balance;
            } elseif ($debt->umur > 60 && $debt->umur <= 90) {
                $agingReport[$customerName]['sawal']['aging']['61-90'] += $debt->debt_balance;
                $agingReport[$customerName]['aging']['61-90'] += $debt->debt_balance;
            } else {
                $agingReport[$customerName]['sawal']['aging']['over_90'] += $debt->debt_balance;
                $agingReport[$customerName]['aging']['over_90'] += $debt->debt_balance;
            }
        }

        foreach ($receivables as $rei) {
            $customerName = $rei->head_customer_name;

            // Calculate the age of the debt using the reference date
            if ($rei->due_date < $referenceDate) {
                $rei->umur = $referenceDate->diffInDays($rei->due_date);
            } else {
                $rei->umur = 0;
            }

            // Add the current debt to the supplier's debts
            $agingReport[$customerName]['debts'][] = $rei;

            // Update totals
            $agingReport[$customerName]['total_amount_due'] += $rei->debt_balance;

            // Update aging totals based on the calculated umur
            if ($rei->umur == 0) {
                $agingReport[$customerName]['aging']['0'] += $rei->debt_balance;
            } elseif ($rei->umur > 0 && $rei->umur <= 30) {
                $agingReport[$customerName]['aging']['1-30'] += $rei->debt_balance;
            } elseif ($rei->umur > 30 && $rei->umur <= 60) {
                $agingReport[$customerName]['aging']['31-60'] += $rei->debt_balance;
            } elseif ($rei->umur > 60 && $rei->umur <= 90) {
                $agingReport[$customerName]['aging']['61-90'] += $rei->debt_balance;
            } else {
                $agingReport[$customerName]['aging']['over_90'] += $rei->debt_balance;
            }
        }

        return $agingReport;
    }

    public function receivableAging(){
        $startDate = request()->input('start_date');
        $endDate = request()->input('end_date');
        $today = Carbon::now();

        $agingReport = $this->getAgingReport2($startDate, $endDate);

        return view('transaction.journal.receivable_aging',compact('agingReport','today','startDate','endDate'));

    }

    public function receivableAgingPdf(){
        $startDate = request()->input('start_date');
        $endDate = request()->input('end_date');


        $agingReport = $this->getAgingReport2($startDate, $endDate);

        return view('transaction.journal.receivable_aging_pdf', compact('agingReport','startDate','endDate'));
    }


    public function showBalanceSheet(Request $request)
    {
        try {
            // Get date inputs from request
            $dateFrom = $request->query('date_from');
            $dateTo = $request->query('date_to');

            // If no dates are selected, return an empty view with a message
            if (empty($dateFrom) || empty($dateTo)) {
                return view('transaction.journal.balance_sheet', [
                    'groupedData' => [],
                    'dateFrom' => $dateFrom,
                    'dateTo' => $dateTo,
                ]);
            }

            // Fetch and calculate balances for chart of accounts between the given date range
            $coas = DB::table('coa')
                ->join('coa_type', 'coa.account_type', '=', 'coa_type.id')
                ->join('journal as j', 'coa.account_number', '=', 'j.account_number')
                ->select(
                    'coa.account_number',
                    'coa.account_name',
                    'coa.account_type',
                    'coa_type.account_sub_type as account_sub_type_name',
                    'coa_type.account_type as account_type_name', // Added account type
                    DB::raw('SUM(j.debet_nominal - j.credit_nominal) as balance')
                )
                ->where('j.department_code','DP01')
                ->whereBetween('j.document_date', [$dateFrom, $dateTo])
                ->groupBy('coa.account_number', 'coa.account_name', 'coa.account_type', 'coa_type.account_sub_type', 'coa_type.account_type')
                ->get();

            // Group the data by account type first, then account sub type, and finally individual account
            $groupedData = $coas->groupBy(function ($item) {
                return $item->account_type_name; // Group by account_type_name
            })->map(function ($accountTypes) {
                return $accountTypes->groupBy(function ($item) {
                    return $item->account_sub_type_name; // Group by account_sub_type_name within account type
                });
            });

            return view('transaction.journal.balance_sheet', compact('groupedData', 'dateFrom', 'dateTo'));
        } catch (\Exception $e) {
            \Log::error('Error fetching balance sheet data: ' . $e->getMessage());
            // dd($e);
            return response()->json(['error' => 'An error occurred while fetching data.'], 500);
        }
    }


    public function filterBalanceSheetData(Request $request)
    {
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $types = ['Asset','Payable','Equity','Sales'];
        $coas = DB::table('coa')
            ->join('coa_type', 'coa.account_type', '=', 'coa_type.id')
            ->join('journal as j', 'coa.account_number', '=', 'j.account_number')
            ->where('j.department_code', 'DP01')
            ->whereIn('coa_type.account_type', $types)
            ->orderBy('coa.account_number', 'asc')
            ->select(
                'coa.account_number',
                'coa.account_name',
                'coa.account_type',
                'coa_type.account_sub_type as account_sub_type_name',
                'coa_type.account_type as account_type_name',
                DB::raw('SUM(CASE
                    WHEN coa.normal_balance = \'Debit\' THEN j.debet_nominal - j.credit_nominal
                    WHEN coa.normal_balance = \'Credit\' THEN j.credit_nominal - j.debet_nominal
                    ELSE 0
                END) as balance')
            );

        // Apply date filter only if both dates are provided
        if ( $dateTo) {
            $coas->where('j.document_date', '<=', $dateTo);
        }

        // Execute the query and group results by account type
        $coas = $coas->groupBy('coa.account_number', 'coa.account_name', 'coa.account_type', 'coa_type.account_sub_type', 'coa_type.account_type')->get();
        Log::info($coas);
        // Group data by account type first, then account sub type, and finally individual account
        $groupedData = $coas->groupBy(function ($item) {
            return $item->account_type_name; // Group by account_type_name
        })->map(function ($accountTypes) {
            return $accountTypes->groupBy(function ($item) {
                return $item->account_sub_type_name; // Group by account_sub_type_name within account type
            });
        });

        return response()->json(['data' => $groupedData]);
    }
    public function balanceSheetPrint(Request $request)
    {
        // Get the date range from the request query
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        // Start building the query for the COA data
        $coas = DB::table('coa')
            ->join('coa_type', 'coa.account_type', '=', 'coa_type.id')
            ->join('journal as j', 'coa.account_number', '=', 'j.account_number')
            ->where('j.department_code','DP01')
            ->select(
                'coa.account_number',
                'coa.account_name',
                'coa.account_type',
                'coa_type.account_sub_type as account_sub_type_name',
                'coa_type.account_type as account_type_name',
                DB::raw('SUM(j.debet_nominal - j.credit_nominal) as balance')
            );

        // If dates are provided, apply date range filter
        if ($dateFrom && $dateTo) {
            $coas->whereBetween('j.document_date', [$dateFrom, $dateTo]);
        }

        // Execute the query and group the data
        $coas = $coas->groupBy('coa.account_number', 'coa.account_name', 'coa.account_type', 'coa_type.account_sub_type', 'coa_type.account_type')->get();

        // Group data by account type and account sub-type
        $groupedData = collect($coas)->groupBy(function ($item) {
            return $item->account_type_name;
        })->map(function ($accountTypes) {
            return $accountTypes->groupBy(function ($item) {
                return $item->account_sub_type_name;
            });
        });

        // Prepare the data to pass to the view
        $data = [
            'dateFrom' => $dateFrom ? $dateFrom : 'N/A', // Show 'N/A' if no date is selected
            'dateTo' => $dateTo ? $dateTo : 'N/A',     // Show 'N/A' if no date is selected
            'groupedData' => $groupedData
        ];

        // Return the view with the data
        return view('transaction.journal.balance_sheet_print', $data);
    }




    public function showIncomeStatement(Request $request)
    {
        return view('transaction.journal.income_statement');
    }

    public function fetchIncomeStatement(Request $request)
    {
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $coas = DB::table('coa')
            ->join('coa_type', 'coa.account_type', '=', 'coa_type.id')
            ->join('journal as j', 'coa.account_number', '=', 'j.account_number')
            ->where('j.document_number','not like','JP%')
            ->select(
                'coa.account_number',
                'coa.account_name',
                'coa.account_type',
                'coa_type.account_sub_type as account_sub_type_name',
                'coa_type.account_type as account_type_name', // Added account type
                DB::raw('SUM(j.credit_nominal - j.debet_nominal) as balance')
            )->orderBy('coa.account_number');

        // Apply date filter only if both dates are provided
        if ($dateFrom && $dateTo) {
            $coas->whereBetween('j.document_date', [$dateFrom, $dateTo]);
        }

        $coas->whereIn('coa_type.account_type', ['Sales', 'Revenue', 'Expense', 'Other Revenue', 'Other Expenses','COGS']);
        // Execute the query and group results by account type
        $coas = $coas->groupBy('coa.account_number', 'coa.account_name', 'coa.account_type', 'coa_type.account_sub_type', 'coa_type.account_type')->get();

        // Group data by account type first, then account sub type, and finally individual account
        $groupedData = $coas->groupBy(function ($item) {
            return $item->account_type_name; // Group by account_type_name
        })->map(function ($accountTypes) {
            return $accountTypes->groupBy(function ($item) {
                return $item->account_sub_type_name; // Group by account_sub_type_name within account type
            });
        });

        return response()->json(['data' => $groupedData]);
    }
    public function generateIncomeStatementPdf(Request $request)
    {
        // Get the date range from the request query
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $company = Company::first();

        // Start building the query for the COA data
        $coas = DB::table('coa')
            ->join('coa_type', 'coa.account_type', '=', 'coa_type.id')
            ->join('journal as j', 'coa.account_number', '=', 'j.account_number')
            ->where('j.department_code','DP01')
            ->select(
                'coa.account_number',
                'coa.account_name',
                'coa.account_type',
                'coa_type.account_sub_type as account_sub_type_name',
                'coa_type.account_type as account_type_name', // Added account type
                DB::raw('SUM(j.credit_nominal - j.debet_nominal) as balance')
            )->orderBy('coa.account_number');

        // If dates are provided, apply date range filter
        if ($dateFrom && $dateTo) {
            $coas->whereBetween('j.document_date', [$dateFrom, $dateTo]);
        }

        // Filter for specific account types
        $coas->whereIn('coa_type.account_type', ['Sales', 'Revenue', 'Expense', 'Other Revenue', 'Other Expenses','COGS']);

        // Execute the query and group the data
        $coas = $coas->groupBy('coa.account_number', 'coa.account_name', 'coa.account_type', 'coa_type.account_sub_type', 'coa_type.account_type')->get();

        // Group data by account type and account sub-type
        $groupedData = collect($coas)->groupBy(function ($item) {
            return $item->account_type_name;
        })->map(function ($accountTypes) {
            return $accountTypes->groupBy(function ($item) {
                return $item->account_sub_type_name;
            });
        });

        // Prepare the data to pass to the view
        $data = [
            'dateFrom' => $dateFrom ? $dateFrom : 'N/A', // Show 'N/A' if no date is selected
            'dateTo' => $dateTo ? $dateTo : 'N/A',     // Show 'N/A' if no date is selected
            'groupedData' => $groupedData,
            'company'=>$company
        ];

        // Return the view and pass the data array to the view
        return view('transaction.journal.income_statement_print', $data);
    }

    private function getAgingReportSummary($startDate = null, $endDate = null)
    {
        $referenceDate = $endDate ? Carbon::parse($endDate) : Carbon::now();

        // Main query for debts within the date range
        $query = Receivable::whereRaw('FLOOR(debt_balance) != 0')
            ->join('customer as child', 'receivable.customer_code', '=', 'child.customer_code')
            ->select(
                'child.customer_name as head_customer_name',
                'receivable.debt_balance',
                'receivable.due_date'
            )
            ->orderBy('child.group_customer', 'asc')
            ->orderBy('child.customer_name', 'asc');

        if ($startDate && $endDate) {
            $query->whereDate('receivable.document_date','<=', $endDate);
        }

        $debts = $query->get();

        $agingReport = [];

        // Initialize aging report structure for all head customers
        $allHeadCustomers = $debts->pluck('head_customer_name')->unique()->values();
        foreach ($allHeadCustomers as $headCustomerName) {
            $agingReport[$headCustomerName] = [
                'total_amount_due' => 0,
                'aging' => [
                    '0' => 0,
                    '1-30' => 0,
                    '31-60' => 0,
                    '61-90' => 0,
                    'over_90' => 0,
                ],
                'sawal' => [
                    'total' => 0,
                    'aging' => [
                        '0' => 0,
                        '1-30' => 0,
                        '31-60' => 0,
                        '61-90' => 0,
                        'over_90' => 0,
                    ],
                ],
            ];
        }


        // Process main debts
        foreach ($debts as $debt) {
            $headCustomerName = $debt->head_customer_name;

            // Calculate the age of the debt
            $umur = $debt->due_date < $referenceDate ? $referenceDate->diffInDays($debt->due_date) : 0;

            // Update totals
            $agingReport[$headCustomerName]['total_amount_due'] += $debt->debt_balance;

            // Update aging totals
            if ($umur == 0) {
                $agingReport[$headCustomerName]['aging']['0'] += $debt->debt_balance;
            } elseif ($umur > 0 && $umur <= 30) {
                $agingReport[$headCustomerName]['aging']['1-30'] += $debt->debt_balance;
            } elseif ($umur > 30 && $umur <= 60) {
                $agingReport[$headCustomerName]['aging']['31-60'] += $debt->debt_balance;
            } elseif ($umur > 60 && $umur <= 90) {
                $agingReport[$headCustomerName]['aging']['61-90'] += $debt->debt_balance;
            } else {
                $agingReport[$headCustomerName]['aging']['over_90'] += $debt->debt_balance;
            }
        }

        return $agingReport;
    }

    public function receivableAgingSummary(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $today = Carbon::now();

        // Call the getAgingReportSummary function
        $agingReport = $this->getAgingReportSummary($startDate, $endDate);

        return view('transaction.journal.receivable_aging_summary', compact('agingReport', 'startDate', 'endDate', 'today'));
    }

    private function getAgingReportSummary2($startDate = null, $endDate = null)
    {
        $referenceDate = $endDate ? Carbon::parse($endDate) : Carbon::now();

        // Main query for debts within the date range
        $query = Debt::whereRaw('FLOOR(debt_balance) != 0')
            ->join('supplier as child', 'debt.supplier_code', '=', 'child.supplier_code')
            ->select(
                'child.supplier_name',
                'debt.debt_balance',
                'debt.due_date'
            );

        if ($startDate && $endDate) {
            $query->whereDate('debt.document_date','<=', $endDate);
        }

        $debts = $query->get();

        $agingReport = [];

        // Initialize aging report structure for all head customers
        $allHeadCustomers = $debts->pluck('supplier_name')->unique()->values();
        foreach ($allHeadCustomers as $headCustomerName) {
            $agingReport[$headCustomerName] = [
                'total_amount_due' => 0,
                'aging' => [
                    '0' => 0,
                    '1-30' => 0,
                    '31-60' => 0,
                    '61-90' => 0,
                    'over_90' => 0,
                ],
                'sawal' => [
                    'total' => 0,
                    'aging' => [
                        '0' => 0,
                        '1-30' => 0,
                        '31-60' => 0,
                        '61-90' => 0,
                        'over_90' => 0,
                    ],
                ],
            ];
        }


        // Process main debts
        foreach ($debts as $debt) {
            $headCustomerName = $debt->supplier_name;

            // Calculate the age of the debt
            $umur = $debt->due_date < $referenceDate ? $referenceDate->diffInDays($debt->due_date) : 0;

            // Update totals
            $agingReport[$headCustomerName]['total_amount_due'] += $debt->debt_balance;

            // Update aging totals
            if ($umur == 0) {
                $agingReport[$headCustomerName]['aging']['0'] += $debt->debt_balance;
            } elseif ($umur > 0 && $umur <= 30) {
                $agingReport[$headCustomerName]['aging']['1-30'] += $debt->debt_balance;
            } elseif ($umur > 30 && $umur <= 60) {
                $agingReport[$headCustomerName]['aging']['31-60'] += $debt->debt_balance;
            } elseif ($umur > 60 && $umur <= 90) {
                $agingReport[$headCustomerName]['aging']['61-90'] += $debt->debt_balance;
            } else {
                $agingReport[$headCustomerName]['aging']['over_90'] += $debt->debt_balance;
            }
        }

        return $agingReport;
    }

    public function payableAgingSummary(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $today = Carbon::now();

        // Call the getAgingReportSummary function
        $agingReport = $this->getAgingReportSummary2($startDate, $endDate);

        return view('transaction.journal.payable_aging_summary', compact('agingReport', 'startDate', 'endDate', 'today'));
    }

    public function showIncomeStatementAccumulated()
    {
        return view('transaction.journal.income_statement_accumulated');
    }

    public function fetchIncomeStatementAccumulated(Request $request)
    {
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        // Validate and parse date range
        $startDate = Carbon::parse($dateFrom)->startOfMonth();
        $endDate = Carbon::parse($dateTo)->endOfMonth();
        if ($startDate > $endDate) {
            return response()->json(['error' => 'Start date must be before end date'], 400);
        }

        // Generate array of months
        $dateRanges = [];
        $currentYear = $startDate->year;
        $currentMonth = $startDate->month;
        $endYear = $endDate->year;
        $endMonth = $endDate->month;

        while ($currentYear < $endYear || ($currentYear == $endYear && $currentMonth <= $endMonth)) {
            $currentDate = Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
            $dateRanges[] = [
                'date_from' => $currentDate->startOfMonth()->format('Y-m-d H:i:s'),
                'date_to' => $currentDate->endOfMonth()->format('Y-m-d H:i:s'),
                'month_key' => $currentDate->format('Y-m')
            ];
            $currentMonth++;
            if ($currentMonth > 12) {
                $currentMonth = 1;
                $currentYear++;
            }
        }

        // Initialize grouped data
        $groupedData = [];
        Log::info($dateRanges);
        foreach ($dateRanges as $range) {
            $monthKey = $range['month_key'];

            $coas = DB::table('coa')
                ->join('coa_type', 'coa.account_type', '=', 'coa_type.id')
                ->join('journal as j', 'coa.account_number', '=', 'j.account_number')
                ->where('j.department_code', 'DP01')
                ->where('j.document_number','not like','JP%')
                ->select(
                    'coa.account_number',
                    'coa.account_name',
                    'coa.account_type',
                    'coa_type.account_sub_type as account_sub_type_name',
                    'coa_type.account_type as account_type_name',
                    DB::raw('SUM(j.credit_nominal - j.debet_nominal) as balance')
                )
                ->whereBetween('j.document_date', [$range['date_from'], $range['date_to']])
                ->whereIn('coa_type.account_type', ['Sales', 'Revenue', 'Expense', 'Other Revenue', 'Other Expense', 'COGS'])
                ->groupBy('coa.account_number', 'coa.account_name', 'coa.account_type', 'coa_type.account_sub_type', 'coa_type.account_type')
                ->orderBy('coa.account_number')
                ->get();

            // Group data by account type and subtype
            $monthData = $coas->groupBy('account_type_name')->map(function ($accountTypes) {
                return $accountTypes->groupBy('account_sub_type_name')->map(function ($accounts) {
                    return $accounts->map(function ($item) {
                        return [
                            'account_number' => $item->account_number,
                            'account_name' => $item->account_name,
                            'balance' => $item->balance
                        ];
                    });
                });
            });

            // Merge into groupedData
            foreach ($monthData as $accountType => $subTypes) {
                if (!isset($groupedData[$accountType])) {
                    $groupedData[$accountType] = [];
                }
                foreach ($subTypes as $subType => $accounts) {
                    if (!isset($groupedData[$accountType][$subType])) {
                        $groupedData[$accountType][$subType] = [];
                    }
                    foreach ($accounts as $account) {
                        $accountKey = $account['account_number'];
                        if (!isset($groupedData[$accountType][$subType][$accountKey])) {
                            $groupedData[$accountType][$subType][$accountKey] = [
                                'account_number' => $account['account_number'],
                                'account_name' => $account['account_name'],
                                'balances' => []
                            ];
                        }
                        $groupedData[$accountType][$subType][$accountKey]['balances'][$monthKey] = $account['balance'];
                    }
                }
            }
        }

        // Ensure all accounts have entries for all months (fill with 0 if no data)
        foreach ($groupedData as $accountType => &$subTypes) {
            foreach ($subTypes as $subType => &$accounts) {
                foreach ($accounts as &$account) {
                    foreach ($dateRanges as $range) {
                        $monthKey = $range['month_key'];
                        if (!isset($account['balances'][$monthKey])) {
                            $account['balances'][$monthKey] = 0;
                        }
                    }
                }
            }
        }

        return response()->json(['data' => $groupedData]);
    }


}
