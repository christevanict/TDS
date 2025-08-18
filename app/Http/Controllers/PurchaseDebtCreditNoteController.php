<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseDebtCreditNote;
use App\Models\PurchaseDebtCreditNoteDetail;
use App\Models\Department;
use App\Models\Company;
use App\Models\Supplier;
use App\Models\Coa;
use App\Models\PurchaseInvoice;
use App\Models\Journal;
use App\Models\Debt;
use App\Models\DebtHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PurchaseDebtCreditNoteController extends Controller
{
    public function index()
    {
        $debtCreditNotes = PurchaseDebtCreditNote::orderBy('id', 'asc')->where('status', 'debit')->get();
        return view('transaction.purchase-debt-credit-notes.purchase_debt_credit_notes_list', compact('debtCreditNotes'));
    }

    public function index1()
    {
        $debtCreditNotes = PurchaseDebtCreditNote::orderBy('id', 'asc')->where('status', 'credit')->get();
        return view('transaction.purchase-debt-credit-notes.purchase_debt_credit_notes_list2', compact('debtCreditNotes'));
    }

    private function generateDebtCreditNoteNumber($companyCode, $departmentCode)
    {
        $today = date('ym');
        $lastNote = PurchaseDebtCreditNote::whereDate('created_at', now()->format('Y-m-d'))
            ->orderBy('purchase_credit_note_number', 'desc')->where('purchase_credit_note_number', 'like', 'PDN%')
            ->first();

        $newNumber = $lastNote ? str_pad((int)substr($lastNote->purchase_credit_note_number, -4) + 1, 5, '0', STR_PAD_LEFT) : '00001';
        return 'DNP/' . $companyCode . '/' . $departmentCode . '/' . $today . '/' . $newNumber;
    }
    private function generateDebtCreditNoteNumber1($companyCode, $departmentCode)
    {
        $today = date('ym');
        $lastNote = PurchaseDebtCreditNote::whereDate('created_at', now()->format('Y-m-d'))
            ->orderBy('purchase_credit_note_number', 'desc')->where('purchase_credit_note_number', 'like', 'PCN%')
            ->first();

        $newNumber = $lastNote ? str_pad((int)substr($lastNote->purchase_credit_note_number, -4) + 1, 5, '0', STR_PAD_LEFT) : '00001';
        return 'CNP/' . $companyCode . '/' . $departmentCode . '/' . $today . '/' . $newNumber;
    }

    public function create(Request $request)
    {
        $departments = Department::where('department_code', 'DP01')->first();
        $suppliers = Supplier::orderBy('id', 'asc')->get();
        $coas = Coa::orderBy('account_number', 'asc')->get();
        $purchaseInvoices = PurchaseInvoice::orderBy('purchase_invoice_number', 'asc')->get();

        // Determine the status based on the page type
        $noteType = $request->get('type', 'debit'); // Default to 'debt'
        $status = ($noteType == 'credit') ? 'credit' : 'debit';

        if ($noteType == 'credit') {
            return view(
                'transaction.purchase-debt-credit-notes.purchase_credit_notes_input',
                compact('departments', 'suppliers', 'coas', 'purchaseInvoices', 'status')
            );
        }

        return view(
            'transaction.purchase-debt-credit-notes.purchase_debt_notes_input',
            compact('departments', 'suppliers', 'coas', 'purchaseInvoices', 'status')
        );
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $company = Company::first();
            $companyCode = $company ? $company->company_code : null;
            $departmentCode = $request->department_code;

            $currentUser = Auth::user()->username;

            $purchaseInvoice = PurchaseInvoice::where('purchase_invoice_number', $request->invoice_number)->first();
            if (!$purchaseInvoice) {
                return redirect()->back()->with('error', 'Invalid invoice number');
            }

            // Set status based on the form type (purchase_debt_note_input or purchase_credit_note_input)
            $status = $request->status ?? ($request->has('debit') ? 'debit' : 'credit');
            if($status=='debit') {
            $purchaseCreditNoteNumber = $this->generateDebtCreditNoteNumber($companyCode, $departmentCode);}
            if($status=='credit') {
                $purchaseCreditNoteNumber = $this->generateDebtCreditNoteNumber1($companyCode, $departmentCode);}
            // Insert details and calculate the total nominal automatically
            $totalNominal = 0;
            foreach ($request->details as $detail) {
                // Ensure 'nominal' exists before accessing it
                $nominal = isset($detail['nominal']) ? $detail['nominal'] : 0; // Default to 0 if 'nominal' is not set
                $totalNominal += $nominal; // Add to the total

                PurchaseDebtCreditNoteDetail::create([
                    'purchase_credit_note_number' => $purchaseCreditNoteNumber,
                    'account_number' => $detail['account_number'] ?? null,
                    'nominal' => $nominal,
                    'note' => $detail['note'] ?? '',
                    'created_by' => $currentUser,
                    'updated_by' => $currentUser,
                ]);

                if($status=='debit') {
                    $Journals2 = new Journal();
                    $Journals2->document_number = $purchaseCreditNoteNumber;
                    $Journals2->document_date = $request->purchase_credit_note_date;
                    $Journals2->account_number = $detail['account_number'];
                    $Journals2->debet_nominal = $nominal;
                    $Journals2->credit_nominal = 0;
                    $Journals2->notes = 'Debit note, with credit '. $detail['account_number'].') with total of : '.$nominal;
                    $Journals2->company_code = $companyCode;
                    $Journals2->department_code = $departmentCode;
                    $Journals2->created_by = Auth::user()->username;
                    $Journals2->updated_by = Auth::user()->username;

                    $Journals2 -> save();
                }
                if($status=='credit') {
                    $Journals3 = new Journal();
                    $Journals3->document_number = $purchaseCreditNoteNumber;
                    $Journals3->document_date = $request->purchase_credit_note_date;
                    $Journals3->account_number = $detail['account_number'];
                    $Journals3->debet_nominal = 0;
                    $Journals3->credit_nominal = $nominal;
                    $Journals3->notes = 'Credit note, with debit '. $detail['account_number'].' with total of : '.$nominal;
                    $Journals3->company_code = $companyCode;
                    $Journals3->department_code = $departmentCode;
                    $Journals3->created_by = Auth::user()->username;
                    $Journals3->updated_by = Auth::user()->username;

                    $Journals3 -> save();
                }
            }

            // Create PurchaseDebtCreditNote record
            $purchaseDebtCreditNote = PurchaseDebtCreditNote::create([
                'purchase_credit_note_number' => $purchaseCreditNoteNumber,
                'purchase_credit_note_date' => $request->purchase_credit_note_date,
                'invoice_number' => $purchaseInvoice->purchase_invoice_number,
                'company_code' => $companyCode,
                'department_code' => $departmentCode,
                'status' => $status,
                'total' => $totalNominal,
                'account_payable' =>  $request->account_payable,
                'created_by' => $currentUser,
                'updated_by' => $currentUser,
            ]);

            $receive = Debt::where('document_number', $request->invoice_number)->first();
            $receives = Debt::where('document_number', $request->invoice_number)->first();
            if($status=='debit') {
                $Journals = new Journal();
                $Journals->document_number = $purchaseCreditNoteNumber;
                $Journals->document_date = $request->purchase_credit_note_date;
                $Journals->account_number = $request->account_payable;
                $Journals->debet_nominal = 0;
                $Journals->credit_nominal = $totalNominal;
                $Journals->notes = 'Purchase Debit Note for ('. $purchaseInvoice->purchase_invoice_number.') with total of : '.$totalNominal;
                $Journals->company_code = $companyCode;
                $Journals->department_code = $departmentCode;
                $Journals->created_by = Auth::user()->username;
                $Journals->updated_by = Auth::user()->username;

                $Journals -> save();

                $amount = $receive->debt_balance + $totalNominal;
                $receive->update(['debt_balance' => $amount]);
            }
            if($status=='credit') {
                $Journals1 = new Journal();
                $Journals1->document_number = $purchaseCreditNoteNumber;
                $Journals1->document_date = $request->purchase_credit_note_date;
                $Journals1->account_number = $request->account_payable;
                $Journals1->debet_nominal = $totalNominal;
                $Journals1->credit_nominal = 0;
                $Journals1->notes = 'Purchase Credit Note for ('. $purchaseInvoice->purchase_invoice_number.') with total of : '.$totalNominal;
                $Journals1->company_code = $companyCode;
                $Journals1->department_code = $departmentCode;
                $Journals1->created_by = Auth::user()->username;
                $Journals1->updated_by = Auth::user()->username;

                $Journals1 -> save();

                $amount = $receive->debt_balance - $totalNominal;
                $receive->update(['debt_balance' => $amount]);
            }

            if($status=='debit') {
            $debtHist = new DebtHistory();
            $debtHist->document_number = $request->invoice_number;
            $debtHist->document_date = $purchaseInvoice->document_date;
            $debtHist->supplier_code = $purchaseInvoice->supplier_code;
            $debtHist->payment_number = $purchaseCreditNoteNumber;
            $debtHist->payment_date = $request->purchase_credit_note_date;
            $debtHist->total_debt = $purchaseInvoice->total;
            $debtHist->payment = $totalNominal;
            $debtHist->debt_balance = $receives->debt_balance + $totalNominal;
            $debtHist->company_code = $companyCode;
            $debtHist->department_code = $departmentCode;
            $debtHist->created_by = Auth::user()->username;
            $debtHist->updated_by = Auth::user()->username;

            $debtHist->save();
            }
            if($status=='credit') {
            $debtHist = new DebtHistory();
            $debtHist->document_number = $request->invoice_number;
            $debtHist->document_date = $purchaseInvoice->document_date;
            $debtHist->supplier_code = $purchaseInvoice->supplier_code;
            $debtHist->payment_number = $purchaseCreditNoteNumber;
            $debtHist->payment_date = $request->purchase_credit_note_date;
            $debtHist->total_debt = $purchaseInvoice->total;
            $debtHist->payment = $totalNominal;
            $debtHist->debt_balance = $receives->debt_balance - $totalNominal;
            $debtHist->company_code = $companyCode;
            $debtHist->department_code = $departmentCode;
            $debtHist->created_by = Auth::user()->username;
            $debtHist->updated_by = Auth::user()->username;

            $debtHist->save();
            }

            DB::commit();
            if($status=='debit') {
                return redirect()->route('transaction.purchase_debt_credit_notes.index')->with('success', 'Purchase Debit Note added successfully!');}
            if($status=='credit') {
                return redirect()->route('transaction.purchase_debt_credit_notes.index1')->with('success', 'Purchase Credit Note added successfully!');}

        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $purchaseDebtCreditNote = PurchaseDebtCreditNote::with('details')->findOrFail($id);
        $purchaseDebtCreditNote->purchase_credit_note_date = Carbon::parse($purchaseDebtCreditNote->purchase_credit_note_date)->format('Y-m-d'); // or any format you prefer

        $departments = Department::where('department_code', 'DP01')->first();
        $suppliers = Supplier::orderBy('id', 'asc')->get();
        $coas = Coa::orderBy('account_number', 'asc')->get();

        return view('transaction.purchase-debt-credit-notes.purchase_debt_credit_notes_edit', compact('purchaseDebtCreditNote', 'departments', 'suppliers', 'coas'));
    }

    public function update(Request $request, $id)
    {
        // dd($request);
        DB::beginTransaction();
        try {
            $purchaseDebtCreditNote = PurchaseDebtCreditNote::findOrFail($id);
            $company = Company::first();
            $companyCode = $company ? $company->company_code : null;
            $departmentCode = $request->department_code;
            $currentUser = Auth::user()->username;

            $purchaseInvoice = PurchaseInvoice::where('purchase_invoice_number', $request->invoice_number)->first();
            if (!$purchaseInvoice) {
                return redirect()->back()->with('error', 'Invalid invoice number');
            }

            // Clear and reinsert details
            PurchaseDebtCreditNoteDetail::where('purchase_credit_note_number', $purchaseDebtCreditNote->purchase_credit_note_number)->delete();
            Journal::where('document_number', $purchaseDebtCreditNote->purchase_credit_note_number)->delete();

            $totalNominal = 0;
            if (isset($request->details) && is_array($request->details)) {
                foreach ($request->details as $detail) {
                    // Ensure 'nominal' exists before accessing it
                    $nominal = isset($detail['nominal']) ? $detail['nominal'] : 0; // Default to 0 if 'nominal' is not set
                    $totalNominal += $nominal;

                    PurchaseDebtCreditNoteDetail::create([
                        'purchase_credit_note_number' => $purchaseDebtCreditNote->purchase_credit_note_number,
                        'account_number' => $detail['account_number'] ?? null,
                        'nominal' => $nominal,
                        'note' => $detail['note'] ?? '',
                        'created_by' => $currentUser,
                        'updated_by' => $currentUser,
                    ]);

                    if($purchaseDebtCreditNote->status=='debit') {
                        $Journals2 = new Journal();
                        $Journals2->document_number = $purchaseDebtCreditNote->purchase_credit_note_number;
                        $Journals2->document_date = $request->purchase_credit_note_date;
                        $Journals2->account_number = $detail['account_number'];
                        $Journals2->debet_nominal = $nominal;
                        $Journals2->credit_nominal = 0;
                        $Journals2->notes = 'Debit note, with credit '. $detail['account_number'].') with total of : '.$nominal;
                        $Journals2->company_code = $companyCode;
                        $Journals2->department_code = $departmentCode;
                        $Journals2->created_by = Auth::user()->username;
                        $Journals2->updated_by = Auth::user()->username;

                        $Journals2 -> save();
                    }
                    if($purchaseDebtCreditNote->status=='credit') {
                        $Journals3 = new Journal();
                        $Journals3->document_number = $purchaseDebtCreditNote->purchase_credit_note_number;
                        $Journals3->document_date = $request->purchase_credit_note_date;
                        $Journals3->account_number = $detail['account_number'];
                        $Journals3->debet_nominal = 0;
                        $Journals3->credit_nominal = $nominal;
                        $Journals3->notes = 'Credit note, with debit '. $detail['account_number'].' with total of : '.$nominal;
                        $Journals3->company_code = $companyCode;
                        $Journals3->department_code = $departmentCode;
                        $Journals3->created_by = Auth::user()->username;
                        $Journals3->updated_by = Auth::user()->username;

                        $Journals3 -> save();
                    }
                }
            }

            $purchaseDebtCreditNote->update([
                'purchase_credit_note_date' => $request->purchase_credit_note_date,
                'invoice_number' => $purchaseInvoice->purchase_invoice_number,
                'total' => $totalNominal,
                'account_payable' => $request->account_payable,
                'company_code' => $companyCode,
                'department_code' => $departmentCode,
                'updated_by' => $currentUser,
            ]);

            $receive = Debt::where('document_number', $request->invoice_number)->first();
            $receives = Debt::where('document_number', $request->invoice_number)->first();
            if($purchaseDebtCreditNote->status=='debit') {
                $Journals = new Journal();
                $Journals->document_number = $purchaseDebtCreditNote->purchase_credit_note_number;
                $Journals->document_date = $request->purchase_credit_note_date;
                $Journals->account_number = $request->account_payable;
                $Journals->debet_nominal = 0;
                $Journals->credit_nominal = $totalNominal;
                $Journals->notes = 'Purchase Debit Note for ('. $purchaseInvoice->purchase_invoice_number.') with total of : '.$totalNominal;
                $Journals->company_code = $companyCode;
                $Journals->department_code = $departmentCode;
                $Journals->created_by = Auth::user()->username;
                $Journals->updated_by = Auth::user()->username;

                $Journals -> save();

                $amount = $receive->debt_balance + $totalNominal - $request->total_old;
                $receive->update(['debt_balance' => $amount]);
            }
            if($purchaseDebtCreditNote->status=='credit') {
                $Journals1 = new Journal();
                $Journals1->document_number = $purchaseDebtCreditNote->purchase_credit_note_number;
                $Journals1->document_date = $request->purchase_credit_note_date;
                $Journals1->account_number = $request->account_payable;
                $Journals1->debet_nominal = $totalNominal;
                $Journals1->credit_nominal = 0;
                $Journals1->notes = 'Purchase Credit Note for ('. $purchaseInvoice->purchase_invoice_number.') with total of : '.$totalNominal;
                $Journals1->company_code = $companyCode;
                $Journals1->department_code = $departmentCode;
                $Journals1->created_by = Auth::user()->username;
                $Journals1->updated_by = Auth::user()->username;

                $Journals1 -> save();

                $amount = $receive->debt_balance - $totalNominal + $request->total_old;
                $receive->update(['debt_balance' => $amount]);
            }

            DebtHistory::where('payment_number', $purchaseDebtCreditNote->purchase_credit_note_number) ->delete();
            if($purchaseDebtCreditNote->status=='debit') {
                $debtHist = new DebtHistory();
                $debtHist->document_number = $request->invoice_number;
                $debtHist->document_date = $purchaseInvoice->document_date;
                $debtHist->supplier_code = $purchaseInvoice->supplier_code;
                $debtHist->payment_number = $purchaseDebtCreditNote->purchase_credit_note_number;
                $debtHist->payment_date = $request->purchase_credit_note_date;
                $debtHist->total_debt = $purchaseInvoice->total;
                $debtHist->payment = $totalNominal;
                $debtHist->debt_balance = $receives->debt_balance + $totalNominal - $request->total_old;
                $debtHist->company_code = $companyCode;
                $debtHist->department_code = $departmentCode;
                $debtHist->created_by = Auth::user()->username;
                $debtHist->updated_by = Auth::user()->username;

                $debtHist->save();
                }
                if($purchaseDebtCreditNote->status=='credit') {
                $debtHist = new DebtHistory();
                $debtHist->document_number = $request->invoice_number;
                $debtHist->document_date = $purchaseInvoice->document_date;
                $debtHist->supplier_code = $purchaseInvoice->supplier_code;
                $debtHist->payment_number = $purchaseDebtCreditNote->purchase_credit_note_number;
                $debtHist->payment_date = $request->purchase_credit_note_date;
                $debtHist->total_debt = $purchaseInvoice->total;
                $debtHist->payment = $totalNominal;
                $debtHist->debt_balance = $receives->debt_balance - $totalNominal + $request->total_old;
                $debtHist->company_code = $companyCode;
                $debtHist->department_code = $departmentCode;
                $debtHist->created_by = Auth::user()->username;
                $debtHist->updated_by = Auth::user()->username;

                $debtHist->save();
                }


            DB::commit();
            if($purchaseDebtCreditNote->status=='debit') {
                return redirect()->route('transaction.purchase_debt_credit_notes.index')->with('success', 'Purchase Debit Note updated successfully!');}
            if($purchaseDebtCreditNote->status=='credit') {
                return redirect()->route('transaction.purchase_debt_credit_notes.index1')->with('success', 'Purchase Credit Note updated successfully!');}

        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to update: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        // dd($request);
        DB::beginTransaction();
        try {
            $purchaseDebtCreditNote = PurchaseDebtCreditNote::findOrFail($id);
            PurchaseDebtCreditNoteDetail::where('purchase_credit_note_number', $purchaseDebtCreditNote->purchase_credit_note_number)->delete();
            $purchaseDebtCreditNote->delete();

            Journal::where('document_number', $purchaseDebtCreditNote->purchase_credit_note_number)->delete();

            $receive = Debt::where('document_number', $request->order)->first();
            if($request->status=='debit') {
                $amount = $receive->debt_balance - $request->total_old;
                $receive->update(['debt_balance' => $amount]);
            }
            if($request->status=='credit') {
                $amount = $receive->debt_balance + $request->total_old;
                $receive->update(['debt_balance' => $amount]);
            }

            DebtHistory::where('payment_number', $purchaseDebtCreditNote->purchase_credit_note_number) ->delete();


            DB::commit();
            if($request->status=='debit') {
                return redirect()->route('transaction.purchase_debt_credit_notes.index')->with('success', 'Purchase Debit Note deleted successfully!');}
            if($request->status=='credit') {
                return redirect()->route('transaction.purchase_debt_credit_notes.index1')->with('success', 'Purchase Credit Note deleted successfully!');}
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete: ' . $e->getMessage());
        }
    }
}
