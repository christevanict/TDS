<?php

namespace App\Http\Controllers;

use App\Models\Coa;
use App\Models\Company;
use App\Models\Debt;
use App\Models\DebtOther;
use App\Models\DebtOtherDetail;
use App\Models\DeleteLog;
use App\Models\Department;
use App\Models\Journal;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DebtOtherController extends Controller
{
    //
    public function index()
    {
        $debts = DebtOther::where('department_code','DP01')->get();
        $privileges = Auth::user()->roles->privileges['debt_other'];
        return view('transaction.debt-other.debt_other_list',compact('debts','privileges'));
    }

    public function create()
    {
        $suppliers = Supplier::where('department_code','DP01')->get();
        $coas = Coa::all();
        $dp_code = 'DP01';
        $dpName = Department::where('department_code','DP01')->first()->department_name;
        $token = str()->random(16);
        $privileges = Auth::user()->roles->privileges['debt_other'];
        return view('transaction.debt-other.debt_other_input',compact('suppliers','coas','dp_code','dpName','token','privileges'));
    }

    public function store(Request $request)
    {
        $exist = DebtOther::where('token',$request->token)->where('department_code','DP01')->whereDate('created_at',Carbon::today())->first();
        if($exist){
            $id = DebtOther::where('created_by',Auth::user()->username)->orderBy('id','desc')->select('id')->first()->id;
            return redirect()->route('transaction.debt_other.create')->with('success', 'Hutang Lain created successfully.')->with('id',$id);
        }
        DB::beginTransaction(); // Begin transaction to ensure atomicity
        try {
            $debt_other_number = $this->generateDebtOtherNumber();
            $general = new DebtOther();
            $general->debt_other_number = $debt_other_number;
            $general->document_date = $request->document_date;
            $general->due_date = $request->due_date;
            $general->supplier_code = $request->supplier_code;
            $general->token = $request->token;
            $general->notes = $request->notes;
            $company_code = Company::first()->company_code;
            $general->company_code = $company_code;
            $general->department_code = $request->department_code;
            $general->created_by = Auth::user()->username;
            $general->updated_by = Auth::user()->username;

            // Save the details
            $this->saveDebtOtherDetails($request->details, $general, $request);

            $id = DebtOther::where('debt_other_number',$debt_other_number)->select('id')->first()->id;

            DB::commit(); // Commit transaction
            return redirect()->route('transaction.debt_other.create')->with('success', 'Hutang Lain created successfully.')->with('id',$id);

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to create Hutang Lain: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $debts = DebtOther::find($id);
        $details = DebtOtherDetail::where('debt_other_number',$debts->debt_other_number)->get();
        $coas = Coa::all();
        $dp_code = 'DP01';
        $dpName = Department::where('department_code','DP01')->first()->department_name;
        $privileges = Auth::user()->roles->privileges['debt_other'];
        return view('transaction.debt-other.debt_other_edit',compact('debts','details','coas','dp_code','dpName','privileges'));
    }

    public function update(Request $request,$id)
    {
        DB::beginTransaction(); // Begin transaction to ensure atomicity
        try {
            $general = DebtOther::find($id);
            $general->document_date = $request->document_date;
            $general->due_date = $request->due_date;
            $general->supplier_code = $request->supplier_code;
            $general->notes = $request->notes;
            $general->updated_by = Auth::user()->username;

            DebtOtherDetail::where('debt_other_number',$general->debt_other_number)->delete();
            Debt::where('document_number',$general->debt_other_number)->delete();
            Journal::where('document_number',$general->debt_other_number)->delete();
            // Save the details
            $this->saveDebtOtherDetails($request->details, $general, $request);


            DB::commit(); // Commit transaction
            return redirect()->route('transaction.debt_other')->with('success', 'Hutang Lain updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to create Hutang Lain: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction(); // Begin transaction to ensure atomicity
        try {
            $general = DebtOther::find($id);
            DebtOtherDetail::where('debt_other_number',$general->debt_other_number)->delete();
            Debt::where('document_number',$general->debt_other_number)->delete();
            Journal::where('document_number',$general->debt_other_number)->delete();

            $reason = $request->input('reason');
            DeleteLog::create([
                'document_number' => $general->debt_other_number,
                'document_date' => $general->document_date,
                'delete_notes' => $reason,
                'type' => 'OPI',
                'company_code' => $general->company_code,
                'department_code' => $general->department_code,
                'deleted_by' => Auth::user()->username,
            ]);

            $general->delete();
            DB::commit(); // Commit transaction
            return redirect()->route('transaction.debt_other')->with('success', 'Hutang Lain deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to create Hutang Lain: ' . $e->getMessage());
        }
    }

    public function printPDF($id)
    {
        $debts = DebtOther::where('id',$id)->with('details')->first();
        $totalHuruf = ucfirst($this->numberToWords($debts->total));
        return view('transaction.debt-other.debt_other_pdf',compact('debts','totalHuruf'));
    }

    public function summary(Request $request)
    {
        // Initialize query for fetching sales invoices
        $query = DebtOther::query();

        // Apply date filtering if 'from_date' and 'to_date' are present in the request
        if ($request->filled('from_date') && $request->filled('to_date')) {
            // Convert dates to Carbon instances for proper formatting and range filtering
            $fromDate = Carbon::parse($request->input('from_date'))->startOfDay();
            $toDate = Carbon::parse($request->input('to_date'))->endOfDay();

            // Apply the date range filter on the 'document_date' column
            $query->whereBetween('document_date', [$fromDate, $toDate]);
        }

        // Retrieve filtered sales invoices
        $debts = $query->where('department_code','DP01')->orderBy('id','asc')->get();

        // Calculate the total amount from all filtered sales invoices
        $totalAmount = $debts->sum('total');
        return view('transaction.debt-other.debt_other_summary',
        compact('debts', 'totalAmount'))
        ->with([
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date')
        ]);
    }

    public function summaryDetail(Request $request)
    {
        // Initialize query for fetching sales invoices
        $query = DebtOther::query();

        // Apply date filtering if 'from_date' and 'to_date' are present in the request
        if ($request->filled('from_date') && $request->filled('to_date')) {
            // Convert dates to Carbon instances for proper formatting and range filtering
            $fromDate = Carbon::parse($request->input('from_date'))->startOfDay();
            $toDate = Carbon::parse($request->input('to_date'))->endOfDay();

            // Apply the date range filter on the 'document_date' column
            $query->whereBetween('document_date', [$fromDate, $toDate]);
        }

        // Retrieve filtered sales invoices
        $debts = $query->with('details')->where('department_code','DP01')->orderBy('id','asc')->get();

        // Calculate the total amount from all filtered sales invoices
        $totalAmount = $debts->sum('total');
        return view('transaction.debt-other.debt_other_summary_detail',compact('debts', 'totalAmount'))
        ->with([
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date')
        ]);
    }

    function numberToWords($number) {
        $number = floor($number);
        $words = [
            '', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'
        ];

        if ($number < 12) {
            return $words[$number];
        } else if ($number < 20) {
            return $words[$number - 10] . ' belas';
        } else if ($number < 100) {
            $result = $words[floor($number / 10)] . ' puluh ' . $words[$number % 10];
        } else if ($number < 200) {
            $result = 'seratus ' . $this->numberToWords($number - 100);
        } else if ($number < 1000) {
            $result = $words[floor($number / 100)] . ' ratus ' . $this->numberToWords($number % 100);
        } else if ($number < 2000) {
            $result = 'seribu ' . $this->numberToWords($number - 1000);
        } else if ($number < 1000000) {
            $result = $this->numberToWords(floor($number / 1000)) . ' ribu ' . $this->numberToWords($number % 1000);
        } else if ($number < 1000000000) {
            $result = $this->numberToWords(floor($number / 1000000)) . ' juta ' . $this->numberToWords($number % 1000000);
        } else if ($number < 1000000000000) {
            $result = $this->numberToWords(floor($number / 1000000000)) . ' milyar ' . $this->numberToWords($number % 1000000000);
        } else if ($number < 1000000000000000) {
            $result = $this->numberToWords(floor($number / 1000000000000)) . ' triliun ' . $this->numberToWords($number % 1000000000000);
        } else {
            return 'Jumlah terlalu besar';
        }

        // Remove double spaces and trim
        return trim(preg_replace('/\s+/', ' ', $result));
    }

    private function generateDebtOtherNumber() {
        // Get today's date components
        $today = now();
        $month = $today->format('n'); // Numeric representation of a month (1-12)
        $year = $today->format('y'); // Last two digits of the year

        // Convert month to Roman numeral
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        $romanMonth = $romanMonths[$month];

        // Fetch the last Sales debt_other created
        $lastDebtOther = DebtOther::whereYear('created_at', $today->year)
            ->whereMonth('created_at', $month)
            ->where('department_code','DP01')
            ->orderBy('id', 'desc')
            ->first();

        // Determine the new invoice number
        if ($lastDebtOther) {
            // Extract the last number from the last invoice number
            $lastNumber = (int)substr($lastDebtOther->debt_other_number, strrpos($lastDebtOther->debt_other_number, '-') + 1);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            // Reset counter to 00001 if no invoices found for the current month
            $newNumber = '00001';
        }

        // Return the new invoice number in the desired format
        return "TDS/OPI/{$romanMonth}/{$year}-{$newNumber}";
    }

    private function saveDebtOtherDetails(array $details,$general,$request)
    {
        $total =0;
        foreach ($details as $detail) {
            $detail['nominal'] = str_replace(',', '', $detail['nominal']??0);
            $detail['debt_other_number'] = $general->debt_other_number;
            $detail['department_code'] = $general->department_code;
            $detail['company_code'] = $general->company_code;
            $detail['created_by'] = $general->created_by;
            $detail['updated_by'] = $general->updated_by;
            $total+=$detail['nominal'];
            DebtOtherDetail::create($detail);

            $acc = Coa::where('account_number',$detail['account_number'])->first();
            //Journal Detail
            $PIJournalD = new Journal();
            $PIJournalD->document_number = $detail['debt_other_number'];
            $PIJournalD->document_date = $general->document_date;
            $PIJournalD->account_number = $detail['account_number']??'1001';
            $PIJournalD->debet_nominal = $detail['nominal'];
            $PIJournalD->credit_nominal = 0;
            $PIJournalD->notes = 'Hutang lain dari akun '.$acc->account_name;
            $PIJournalD->company_code = $general->company_code;
            $PIJournalD->department_code = $general->department_code;
            $PIJournalD->created_by = $general->created_by;
            $PIJournalD->updated_by = $general->updated_by;
            $PIJournalD -> save();
        }
        $general->total = $total;
        $general->save();

        $supplier = Supplier::where('supplier_code',$general->supplier_code)->first();
        //Journal Header
        $acc = Coa::where('account_number',$detail['account_number'])->first();
        //Journal Detail
        $PIJournalD = new Journal();
        $PIJournalD->document_number = $general->debt_other_number;
        $PIJournalD->document_date = $general->document_date;
        $PIJournalD->account_number = $supplier->account_payable??'1001';
        $PIJournalD->debet_nominal = 0;
        $PIJournalD->credit_nominal = $total;
        $PIJournalD->notes = 'Hutang lain untuk supplier '.$request->supplier_name;
        $PIJournalD->company_code = $general->company_code;
        $PIJournalD->department_code = $general->department_code;
        $PIJournalD->created_by = $general->created_by;
        $PIJournalD->updated_by = $general->updated_by;
        $PIJournalD -> save();

        Debt::create([
            'document_number'=>$general->debt_other_number,
            'document_date'=>$general->document_date,
            'due_date'=>$general->due_date,
            'total_debt'=>$general->total,
            'debt_balance'=>$general->total,
            'supplier_code'=>$general->supplier_code,
            'due_date'=>$general->due_date,
            'company_code'=>$general->company_code,
            'department_code'=>$general->department_code,
            'created_by' => Auth::user()->username,
            'updated_by' => Auth::user()->username,
        ]);
    }
}
