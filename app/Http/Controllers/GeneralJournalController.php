<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Coa;
use App\Models\Department;
use App\Models\Journal;
use App\Models\GeneralJournal;
use App\Models\GeneralJournalDetail;
use App\Models\Periode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GeneralJournalController extends Controller
{
    public function index() {
        $companies = Company::all();
        $departments = Department::where('department_code', 'DP01')->first();
        $generalJournals = GeneralJournal::all();
        $coas = COA::all();
        $privileges = Auth::user()->roles->privileges['general_journal'];

        return view('transaction.general-journal.general_journal_list', compact('companies', 'departments', 'generalJournals', 'coas','privileges'));
    }

    private function generateGeneralJournalNumber($date) {
        $today = Carbon::parse($date);
        $month = $today->format('n'); // Numeric representation of a month (1-12)
        $year = $today->format('y');
        // Convert month to Roman numeral
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        $romanMonth = $romanMonths[$month];
        $prefix = "TDS/GJN/{$romanMonth}/{$year}-"; //

        $lastGeneralJournal = GeneralJournal::whereRaw('SUBSTRING(general_journal_number, 1, ?) = ?', [strlen($prefix), $prefix])
            ->orderBy('general_journal_number', 'desc')
            ->first();

        if ($lastGeneralJournal) {
            $lastNumber = (int)substr($lastGeneralJournal->general_journal_number, -5);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }

        return "$prefix$newNumber";
    }

    public function create() {
        $companies = Company::all();
        $departments = Department::where('department_code', 'DP01')->first();
        $coas = Coa::whereRelation('coasss','account_sub_type','!=','PM')->orderBy('account_number', 'asc')->get();
        $token = str()->random(16);
        $privileges = Auth::user()->roles->privileges['general_journal'];
        return view('transaction.general-journal.general_journal_input', compact('companies', 'departments', 'coas','privileges','token'));
    }

    public function store(Request $request) {
        $exist = GeneralJournal::where('token',$request->token)->where('department_code','DP01')->whereDate('created_at',Carbon::today())->first();
        if($exist){
            $id = GeneralJournal::where('created_by',Auth::user()->username)->orderBy('id','desc')->select('id')->first()->id;
            return redirect()->route('transaction.general_journal.create')->with('success', 'General Journal created successfully.')->with('id',$id);
        }
        DB::beginTransaction(); // Begin transaction to ensure atomicity
        try {

            $general_journal_number = $this->generateGeneralJournalNumber($request->general_journal_date);
            $totalNominalDebet = 0;
            $totalNominalCredit = 0;
            // Validate and accumulate the total nominal
            foreach ($request->details as $detail) {
                $detail['nominal_debet'] = str_replace(',', '', $detail['nominal_debet']??0);
                $detail['nominal_credit'] = str_replace(',', '', $detail['nominal_credit']??0);
                if (isset($detail['nominal_debet'])) {
                    $totalNominalDebet += $detail['nominal_debet'];
                }
                if (isset($detail['nominal_credit'])) {
                    $totalNominalCredit += $detail['nominal_credit'];
                }
            }


            if(GeneralJournal::where('general_journal_number', $request->general_journal_number)->count() < 1) {

            $general = new GeneralJournal();
            $general->general_journal_number = $general_journal_number;
            $general->general_journal_date = $request->general_journal_date;
            $general->token = $request->token;
            $general->nominal_debet = $totalNominalDebet;
            $general->nominal_credit = $totalNominalCredit;
            $general->note = $request->note??'';
            $general->company_code = $request->company_code;
            $general->department_code = $request->department_code;
            $general->created_by = Auth::user()->username;
            $general->updated_by = Auth::user()->username;

            $general->save(); // Save the main cash in entry


            // Save the details
            $this->saveGeneralJournalDetails($request->details, $general->general_journal_number, $request->company_code, $request->department_code, $general->general_journal_date);


            $id = GeneralJournal::where('general_journal_number',$general->general_journal_number)->select('id')->first()->id;
            DB::commit(); // Commit transaction
            return redirect()->route('transaction.general_journal.create')->with('success', 'General Journal created successfully.')->with('id',$id);

            } else {
                return redirect()->back()->with('error', 'General Journal Number must not be the same');
            };

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to create General Journal: ' . $e->getMessage());
        }
    }

    public function edit($id) {
        try {
            $companies = Company::all();
            $departments = Department::where('department_code', 'DP01')->first();
            $coas = Coa::whereRelation('coasss','account_sub_type','!=','PM')->orderBy('account_number', 'asc')->get();
            $generalJournal = GeneralJournal::with('details')->findOrFail($id);
            $generalJournal->general_journal_date = Carbon::parse($generalJournal->general_journal_date)->format('Y-m-d');
            $editable= true;
            $periodeClosed = Periode::where('periode_active', 'closed')
            ->where('periode_start', '<=', $generalJournal->general_journal_date)
            ->where('periode_end', '>=', $generalJournal->general_journal_date)
            ->first();
            if($periodeClosed){
                $editable = false;
            }
            $privileges = Auth::user()->roles->privileges['general_journal'];
            return view('transaction.general-journal.general_journal_edit', compact('generalJournal', 'companies', 'departments', 'coas','privileges','editable'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load edit form: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction(); // Mulai transaksi
        try {
            $general = GeneralJournal::findOrFail($id);

            $general->general_journal_date = Carbon::createFromFormat('Y-m-d', $request->general_journal_date); // Menggunakan Carbon untuk parsing
            $general->note = $request->note ??'';
            $general->company_code = $request->company_code ?? $general->company_code;
            $general->department_code = $request->department_code ?? $general->department_code;
            $general->updated_by = Auth::user()->username;

            // Hapus semua detail yang lama sebelum menyimpan detail yang baru
            GeneralJournalDetail::where('general_journal_number', $general->general_journal_number)->delete();

            // Total nominal yang diupdate
            $totalNominalDebet = 0;
            $totalNominalCredit = 0;

            // Jika ada detail baru, simpan ulang detailnya
            if ($request->has('details')) {
                foreach ($request->details as $detail) {
                    $detail['nominal_debet'] = str_replace(',', '', $detail['nominal_debet']??0);
                    $detail['nominal_credit'] = str_replace(',', '', $detail['nominal_credit']??0);
                    if (isset($detail['nominal_debet'])) {
                        $totalNominalDebet += $detail['nominal_debet'];
                    }
                    if (isset($detail['nominal_credit'])) {
                        $totalNominalCredit += $detail['nominal_credit'];
                    }
                }


                $this->saveGeneralJournalDetails($request->details, $general->general_journal_number, $request->company_code, $request->department_code, $general->general_journal_date);
            }

            $general->nominal_debet = $totalNominalDebet;
            $general->nominal_credit = $totalNominalCredit;
            $general->save();

            DB::commit(); // Commit transaksi
            return redirect()->route('transaction.general_journal')->with('success', 'General Journal updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaksi jika terjadi error
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to update: ' . $e->getMessage());
        }
    }

    public function destroy($id) {
        DB::beginTransaction();
        try {
            $general = GeneralJournal::findOrFail($id);
            GeneralJournal::where('general_journal_number', $general->general_journal_number)->delete();
            GeneralJournalDetail::where('general_journal_number', $general->general_journal_number)->delete();
            $general->delete();

            DB::commit(); // Commit transaction
            return redirect()->route('transaction.general_journal')->with('success', 'General Journal deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->route('transaction.general_journal')->with('error', 'Error deleting: ' . $e->getMessage());
        }
    }

    public function printPDF($id){
        $generals = GeneralJournal::with('users','details','details.coa')->find($id);
        $totalHuruf = ucfirst($this->numberToWords($generals->nominal_debet));
        // dd($bankCashIn);
        return view('transaction.general-journal.general_journal_print', compact('generals','totalHuruf'));
    }

    private function saveGeneralJournalDetails(array $generalJournalDetails, $general_journal_number, $company_code, $department_code, $date) {
        Journal::where('document_number', $general_journal_number)->delete();
        foreach ($generalJournalDetails as $index => $detail) {
            $detail['nominal_debet'] = str_replace(',', '', $detail['nominal_debet']??0);
            $detail['nominal_credit'] = str_replace(',', '', $detail['nominal_credit']??0);
            // Ensure index is the same as the row number from the form input
            $detail['general_journal_number'] = $general_journal_number;
            $detail['row_number'] = $index + 1; // Correctly assign row number
            $detail['company_code'] = $company_code;
            $detail['department_code'] = $department_code;
            $detail['created_by'] = Auth::user()->username;
            $detail['updated_by'] = Auth::user()->username;
            $detail['note'] = $detail['note'] ?? ''; // Ensure note is included, default to empty string if not provided
            GeneralJournalDetail::create($detail);


            $journal = new Journal();
            $journal->document_number = $general_journal_number;
            $journal->document_date = $date;
            $journal->account_number = $detail['account_number'];
            $journal->debet_nominal = $detail['nominal_debet'];
            $journal->credit_nominal = $detail['nominal_credit'];
            $journal->notes = $detail['note']??'';
            $journal->company_code = $detail['company_code'];
            $journal->department_code = $detail['department_code'];
            $journal->created_by = Auth::user()->username;
            $journal->updated_by = Auth::user()->username;

            $journal->save(); // Save the main cash in entry
        }
    }

    function numberToWords($number) {
        $number = floor($number);
        $words = [
            'nol', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'
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
}
