<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BankCashIn;
use App\Models\BankCashInDetail;
use App\Models\Company;
use App\Models\Journal;
use App\Models\Coa;
use App\Models\Department;
use App\Models\Periode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use TCPDF;

class BankCashInController extends Controller
{
    public function index() {
        $companies = Company::all();
        $departments = Department::where('department_code', 'DP01')->first();
        $bankCashInRecords = BankCashIn::all();
        $coas = Coa::orderBy('account_number', 'asc')->get();
        $privileges = Auth::user()->roles->privileges['bank_in'];
        return view('transaction.bank-cash-in.bank_cash_in_list', compact('companies', 'departments', 'bankCashInRecords', 'coas','privileges'));
    }

    private function generateBankCashInNumber($date) {
        $today = Carbon::parse($date);
        $month = $today->format('n'); // Numeric representation of a month (1-12)
        $year = $today->format('y');
        // Convert month to Roman numeral
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        $romanMonth = $romanMonths[$month];
        $prefix = "TDS/BCI/{$romanMonth}/{$year}-"; //

        $lastBankCashIn = BankCashIn::whereRaw('SUBSTRING(bank_cash_in_number, 1, ?) = ?', [strlen($prefix), $prefix])
            ->orderBy('bank_cash_in_number', 'desc')
            ->first();

        if ($lastBankCashIn) {
            $lastNumber = (int)substr($lastBankCashIn->bank_cash_in_number, -5);
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
        // Generate bank cash in number
        $privileges = Auth::user()->roles->privileges['bank_in'];
        return view('transaction.bank-cash-in.bank_cash_in_input', compact('companies', 'departments', 'coas','privileges','token'));
    }

    public function store(Request $request) {
        $exist = BankCashIn::where('token',$request->token)->where('department_code','DP01')->whereDate('created_at',Carbon::today())->first();
        if($exist){
            $id = BankCashIn::where('created_by',Auth::user()->username)->orderBy('id','desc')->select('id')->first()->id;
            return redirect()->route('transaction.bank_cash_in.create')->with('success', 'Bank Cash In created successfully.')->with('id',$id);
        }
        DB::beginTransaction(); // Begin transaction to ensure atomicity
        try {
            $bank_cash_in_number = $this->generateBankCashInNumber($request->bank_cash_in_date);
            // Calculate total nominal before creating BankCashIn entry
            $totalNominal = 0;
            $notes='';

            // Validate and accumulate the total nominal
            foreach ($request->details as $detail) {
                if (isset($detail['nominal'])) {
                    $detail['nominal'] = str_replace(',', '', $detail['nominal']??0);
                    $totalNominal += $detail['nominal'];
                    $notes .=$detail['note'].' | ';
                }
            }

            if(BankCashIn::where('bank_cash_in_number', $request->bank_cash_in_number)->count() < 1) {
            // Create a new BankCashIn entry with the calculated total nominal
            $cashIn = new BankCashIn();
            $cashIn->bank_cash_in_number = $bank_cash_in_number;
            $cashIn->bank_cash_in_date = $request->bank_cash_in_date;
            $cashIn->token = $request->token;
            $cashIn->account_number = $request->account_number;
            $cashIn->note = $request->note??''; // Save note from the main entry
            $cashIn->company_code = $request->company_code; // Nullable
            $cashIn->department_code = $request->department_code; // Nullable
            $cashIn->created_by = Auth::user()->username;
            $cashIn->updated_by = Auth::user()->username;
            $cashIn->nominal = $totalNominal; // Set nominal directly before saving

            $cashIn->save(); // Save the main cash in entry

            $journal = new Journal();
            $journal->document_number = $cashIn->bank_cash_in_number;
            $journal->document_date = $cashIn->bank_cash_in_date;
            $journal->account_number = $request->account_number;
            $journal->debet_nominal = $cashIn->nominal;
            $journal->credit_nominal = 0;
            $journal->notes = $notes;
            $journal->company_code = $request->company_code;
            $journal->department_code = $request->department_code;
            $journal->created_by = Auth::user()->username;
            $journal->updated_by = Auth::user()->username;

            $journal->save(); // Save the main cash in entry

            // Save the details
            $this->saveBankCashInDetails($request->details, $cashIn->bank_cash_in_number, $request->account_number, $request->company_code, $request->department_code, $cashIn->bank_cash_in_date);

            $id = BankCashIn::where('bank_cash_in_number',$cashIn->bank_cash_in_number)->select('id')->first()->id;


            DB::commit(); // Commit transaction
            return redirect()->route('transaction.bank_cash_in.create')->with('success', 'Bank Cash In created successfully.')->with('id',$id);

            } else {
                return redirect()->back()->with('error', 'Bank Cash In Number must not be the same');
            };

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to create Bank Cash In: ' . $e->getMessage());
        }
    }

    public function edit($id) {
        try {
            $companies = Company::all();
            $departments = Department::where('department_code', 'DP01')->first();
            $coas = Coa::whereRelation('coasss','account_sub_type','!=','PM')->orderBy('account_number', 'asc')->get();

            // Fetch BankCashIn with its related details
            $bankCashIn = BankCashIn::with('details')->findOrFail($id);



            // Pastikan bank_cash_in_date diubah menjadi objek Carbon
            $bankCashIn->bank_cash_in_date = Carbon::parse($bankCashIn->bank_cash_in_date)->format('Y-m-d');
            $editable= true;
            $periodeClosed = Periode::where('periode_active', 'closed')
            ->where('periode_start', '<=', $bankCashIn->bank_cash_in_date)
            ->where('periode_end', '>=', $bankCashIn->bank_cash_in_date)
            ->first();
            if($periodeClosed){
                $editable = false;
            }
            $privileges = Auth::user()->roles->privileges['bank_in'];
            return view('transaction.bank-cash-in.bank_cash_in_edit', compact('bankCashIn', 'companies', 'departments', 'coas','privileges','editable'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load edit form: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction(); // Mulai transaksi
        try {
            // Cari BankCashIn berdasarkan ID
            $bankCashIn = BankCashIn::findOrFail($id);


            // Update data utama BankCashIn
            $bankCashIn->bank_cash_in_date = Carbon::createFromFormat('Y-m-d', $request->bank_cash_in_date); // Menggunakan Carbon untuk parsing
            $bankCashIn->account_number = $request->account_number ?? $bankCashIn->account_number;
            $bankCashIn->note = $request->note ?? $bankCashIn->note;
            $bankCashIn->company_code = $request->company_code ?? $bankCashIn->company_code;
            $bankCashIn->department_code = $request->department_code ?? $bankCashIn->department_code;
            $bankCashIn->updated_by = Auth::user()->username;

            // Hapus semua detail yang lama sebelum menyimpan detail yang baru
            BankCashInDetail::where('bank_cash_in_number', $bankCashIn->bank_cash_in_number)->delete();

            // Total nominal yang diupdate
            $totalNominal = 0;
            $notes='';

            // Jika ada detail baru, simpan ulang detailnya
            if ($request->has('details')) {
                foreach ($request->details as $detail) {
                    if (isset($detail['nominal'])) {
                        $detail['nominal'] = str_replace(',', '', $detail['nominal']??0);
                        $totalNominal += $detail['nominal']; // Kalkulasi total nominal
                        $notes .=$detail['note'].' | ';
                    }
                }

                Journal::where('document_number', $bankCashIn->bank_cash_in_number)->delete();
                $journal = new Journal();
                $journal->document_number = $bankCashIn->bank_cash_in_number;
                $journal->document_date = $bankCashIn->bank_cash_in_date;
                $journal->account_number = $request->account_number;
                $journal->debet_nominal = $totalNominal;
                $journal->credit_nominal = 0;
                $journal->notes = $notes;
                $journal->company_code = $request->company_code;
                $journal->department_code = $request->department_code;
                $journal->created_by = Auth::user()->username;
                $journal->updated_by = Auth::user()->username;

                $journal->save(); // Save the main cash in entry

                // Panggil method untuk menyimpan ulang detail BankCashIn
                $this->saveBankCashInDetails($request->details, $bankCashIn->bank_cash_in_number, $request->account_number, $request->company_code, $request->department_code, $bankCashIn->bank_cash_in_date);
            }

            // Update total nominal di BankCashIn
            $bankCashIn->nominal = $totalNominal;
            $bankCashIn->save(); // Simpan perubahan di BankCashIn

            DB::commit(); // Commit transaksi
            return redirect()->route('transaction.bank_cash_in')->with('success', 'Bank Cash In updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaksi jika terjadi error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to update: ' . $e->getMessage());
        }
    }

    public function destroy($id) {
        DB::beginTransaction();
        try {
            $bankCashIn = BankCashIn::findOrFail($id);
            BankCashInDetail::where('bank_cash_in_number', $bankCashIn->bank_cash_in_number)->delete(); // Use bank_cash_in_number for detail linking
            $bankCashIn->delete();

            DB::commit(); // Commit transaction
            return redirect()->route('transaction.bank_cash_in')->with('success', 'Bank Cash In deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->route('transaction.bank_cash_in')->with('error', 'Error deleting Bank Cash In: ' . $e->getMessage());
        }
    }

    public function printPDF($id){
        $bankCashIn = BankCashIn::with('coa','users','details','details.coa')->find($id);
        $totalHuruf = ucfirst($this->numberToWords($bankCashIn->nominal));
        // dd($bankCashIn);
        //return view('transaction.bank-cash-in.bank_cash_in_print_webservice', compact('bankCashIn'));
        return view('transaction.bank-cash-in.bank_cash_in_print', compact('bankCashIn','totalHuruf'));
    }

    public function printTc($id)
    {
        $bankCashIn = BankCashIn::with('coa','users','details','details.coa')->find($id);
        $totalHuruf = ucfirst($this->numberToWords($bankCashIn->nominal));

        // Initialize TCPDF
        $pdf = new TCPDF('L', 'mm', [145, 210], true, 'UTF-8', false); // Landscape, 145mm x 152mm
        $pdf->SetCreator('Your App');
        $pdf->SetAuthor('Your Name');
        $pdf->SetTitle('Bank Cash In - ' . $bankCashIn->bank_cash_in_number);
        $pdf->SetSubject('Other Payment');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(5, 5, 5);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();
        $pdf->SetFont('dejavusansmono', '', 10.5);

        // Build content with tables
        $content = '<style>
            table { font-family: dejavusansmono; font-size: 10.5pt; width: 100%; border-collapse: collapse; }
            td { padding-left: 2px; padding-right: 2px; line-height: 1; }
            .right { text-align: right; }
            .left { text-align: left; }
            .divider { border-bottom: 1px solid black; padding: 0; margin: 0; height: 0; line-height: 0; }
        </style>';

        // Top spacer
        $content .= '<table><tr><td style="height: 5mm;"></td></tr></table>';

        // Header Table
        $content .= '<table>';
        $content .= '<tr><td style="width: 40%;font-size:14px;font-weight:bold;">TDS, CV</td><td style="width: 60%;font-size:14px;font-weight:bold;">OTHER PAYMENT</td></tr> <br>';
        $content .= '<tr><td style="width: 40%;">Paid From : ' . htmlspecialchars($bankCashIn->account_number . '-' . $bankCashIn->coa->account_name ?? 'N/A') . '</td>';
        $content .= '<td style="width: 60%; text-align: center;">Voucher No. ' . $bankCashIn->bank_cash_in_number . '</td></tr>';
        $content .= '<tr><td style="width: 40%;">Tanggal   : ' . Carbon::parse($bankCashIn->bank_cash_in_date)->format('d M Y') . '</td>';
        $content .= '<td style="width: 60%;"></td></tr>';
        $content .= '</table>';

        // Divider
        $content .= '<table><tr><td style="height: 2mm;"></td></tr></table>';
        $content .= '<table><tr><td class="divider"></td></tr></table>';

        // Details Table
        $content .= '<table>';
        $content .= '<tr>';
        $content .= '<td style="width: 15%; border-right: 1px solid black;border-left: 1px solid black;line-height: 2;">Account No.</td>';
        $content .= '<td style="width: 40%; border-right: 1px solid black;line-height: 2;">Account Name</td>';
        $content .= '<td style="width: 20%; border-right: 1px solid black; text-align: right;line-height: 2;">Amount</td>';
        $content .= '<td style="width: 25%; text-align: right;border-right: 1px solid black;line-height: 2;">Memo</td>';
        $content .= '</tr>';
        $content .= '<tr><td colspan="4" class="divider"></td></tr>';

        foreach ($bankCashIn->details as $index => $detail) {
            $content .= '<tr>';
            $content .= '<td style="width: 15%; text-align: left; border-right: 1px solid black;border-left: 1px solid black;line-height: 1.5;">' . $detail->account_number . '</td>';
            $content .= '<td style="width: 40%; text-align: left; border-right: 1px solid black;line-height: 1.5;">' . $detail->coa->account_name . '</td>';
            $content .= '<td style="width: 20%; text-align: right; border-right: 1px solid black;line-height: 1.5;">' . number_format($detail->nominal, 0) . '</td>';
            $content .= '<td style="width: 25%; text-align: right;border-right: 1px solid black;line-height: 1.5;">' . $detail->note . '</td>';
            $content .= '</tr>';
        }
        $content .= '<tr><td colspan="4" class="divider"></td></tr> <br>';


        // First line: Terbilang + Total Payment
        $content .= '<tr>';
        $content .= '<td colspan="2" style="width: 55%; vertical-align: top;">' . $totalHuruf . '</td>';
        $content .= '<td style="width: 20%; text-align: right; border-top: 1px solid black;border-left: 1px solid black;border-bottom: 1px solid black;">Total Payment:</td>';
        $content .= '<td style="width: 25%; text-align: right;border-top: 1px solid black;border-right: 1px solid black;border-bottom: 1px solid black;">' . number_format($bankCashIn->nominal, 0) . '</td>';
        $content .= '</tr>';


        $content .= '<table><tr><td style="height: 2mm;"></td></tr></table>';
        // Memo Section
        $content .= '<tr><td colspan="4" style="width: 100%;">Memo</td></tr>';

        $content .= '<tr><td colspan="4" style="width: 100%; border:1px solid black; line-height:1.5;">' . $bankCashIn->note . '</td></tr>';

        $content .= '</table>';
        $content .= '<table><tr><td style="height: 2mm;"></td></tr></table>';
        // Signature Section
        $content .= '<table>';
        $content .= '<tr>';
        $content .= '<td style="width: 33%; text-align: center;">Disiapkan</td>';
        $content .= '<td style="width: 33%; text-align: center;">Dibayar oleh</td>';
        $content .= '<td style="width: 33%; text-align: center;">Diterima oleh</td>';
        $content .= '</tr>';
        $content .= '<table><tr><td style="height: 10mm;"></td></tr></table>';
        $content .= '<tr>';
        $content .= '<td style="width: 33%; text-align: center;">--------</td>';
        $content .= '<td style="width: 33%; text-align: center;">------------</td>';
        $content .= '<td style="width: 33%; text-align: center;">-------------</td>';
        $content .= '</tr>';
        $content .= '<tr>';
        $content .= '<td style="width: 33%; text-align: left;">          Tgl.</td>';
        $content .= '<td style="width: 33%; text-align: left;">        Tgl.</td>';
        $content .= '<td style="width: 33%; text-align: left;">        Tgl.</td>';
        $content .= '</tr>';
        $content .= '</table>';

        // Write content
        $pdf->writeHTML($content, true, false, true, false, '');

        // Output PDF
        $pdf->Output('bank_cash_in_' . $bankCashIn->bank_cash_in_number . '.pdf', 'I');

    }

    private function splitAtWordBoundary($text, $maxLength)
    {
        if (strlen($text) <= $maxLength) return [$text];
        $pos = strrpos(substr($text, 0, $maxLength + 1), ' ');
        if ($pos === false) $pos = $maxLength;
        return [substr($text, 0, $pos), substr($text, $pos + 1)];
    }

    private function saveBankCashInDetails(array $bankCashInDetails, $bank_cash_in_number, $account_number, $company_code, $department_code, $date) {
        foreach ($bankCashInDetails as $index => $detail) {
            $detail['nominal'] = str_replace(',', '', $detail['nominal']??0);
            // Ensure index is the same as the row number from the form input
            $detail['bank_cash_in_number'] = $bank_cash_in_number; // Match bank_cash_in_number
            $detail['account_number_header'] = $account_number; // Use account_number from BankCashIn
            $detail['account_number'] = $detail['account_number']; // Ensure account_number is included
            $detail['row_number'] = $index + 1; // Correctly assign row number
            $detail['company_code'] = $company_code;
            $detail['department_code'] = $department_code;
            $detail['created_by'] = Auth::user()->username;
            $detail['updated_by'] = Auth::user()->username;
            $detail['note'] = $detail['note'] ?? ''; // Ensure note is included, default to empty string if not provided

            BankCashInDetail::create($detail);

            $journal = new Journal();
            $journal->document_number = $bank_cash_in_number;
            $journal->document_date = $date;
            $journal->account_number = $detail['account_number'];
            $journal->debet_nominal = 0;
            $journal->credit_nominal = $detail['nominal'];
            $journal->notes = $detail['note'];
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

    public function printWebservice($id){
        $bankCashIn = BankCashIn::with('coa','users','details','details.coa')->find($id);
        $totalHuruf = ucfirst($this->numberToWords($bankCashIn->nominal));

        $ret = [];
        $ret["account_number"] = $bankCashIn->account_number;
        $ret["account_name"] = $bankCashIn->coa->account_name;
        $ret["bank_cash_out_number"] = $bankCashIn->bank_cash_in_number;
        $ret["bank_cash_out_date"] = Carbon::parse($bankCashIn->bank_cash_in_date)->format('d M Y');
        $ret["nominal"] = number_format($bankCashIn->nominal, 0);
        $ret["terbilangLines"] = $totalHuruf;
        $ret["memo"] = $bankCashIn->note;

        $det = [];
        foreach($bankCashIn->details as $index => $d){
            $det[] = [
                "account_number" => $d->account_number,
                "account_name" => $d->coa->account_name,
                "amount" => number_format($d->nominal, 0),
                "memo" => $d->note
            ];
        }
        $ret["detail"] = $det;

        return response()->json($ret);
    }
}
