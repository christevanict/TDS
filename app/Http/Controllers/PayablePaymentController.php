<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PayablePayment;
use App\Models\PayablePaymentDetail;
use App\Models\PayablePaymentDetailPay;
use App\Models\Department;
use App\Models\Company;
use App\Models\PaymentMethod;
use App\Models\Supplier;
use App\Models\Coa;
use App\Models\Journal;
use App\Models\Debt;
use App\Models\DebtHistory;
use App\Models\PurchaseInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PayablePaymentController extends Controller
{
    public function index()
    {
        $pays = PayablePayment::orderBy('id', 'desc')->get();
        $privileges = Auth::user()->roles->privileges['payable_payment'];
        return view('transaction.payable-payment.index', compact('pays', 'privileges'));
    }

    private function generatePayablePaymentNumber($company, $department)
    {
        $today = date('ym');
        $lastPayablePayment = PayablePayment::orderBy('created_at', 'desc')->first();

        if ($lastPayablePayment) {
            $lastMonth = date('ym', strtotime($lastPayablePayment->created_at));
            if ($lastMonth === $today) {
                $lastNumber = (int)substr($lastPayablePayment->payable_payment_number, -4);
                $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '00001';
            }
        } else {
            $newNumber = '00001';
        }
        return 'PAP/' . $company . '/' . $department . '/' . $today . '/' . $newNumber;
    }

    public function create()
    {
        $departments = Department::where('department_code', 'DP01')->first();
        $paymentMethods = PaymentMethod::orderBy('id', 'asc')->get();
        $suppliers = Supplier::orderBy('id', 'asc')->get();
        $purchaseInvoices = PurchaseInvoice::with('debts')->whereHas('debts', function ($query) {
            $query->where('debt_balance', '>', 0);
        })->get();
        $coas = Coa::orderBy('id', 'asc')->get();
        $token = Str::random(16);
        $privileges = Auth::user()->roles->privileges['payable_payment'];
        return view('transaction.payable-payment.input', compact('departments', 'paymentMethods', 'suppliers', 'coas', 'purchaseInvoices', 'privileges', 'token'));
    }

    private function saveDetails($request, $payable_payment_number, $payable_payment_date, $supplier_code, $company_code, $department_code, &$total_nominal, &$total_discount)
    {
        $currentUser = Auth::user()->username;
        $payment_details = $request->payment_details;
        $paymentDetails = [];
        if ($payment_details) {
            foreach ($payment_details as $value) {
                $pay_detail_data = json_decode($value['payment'], true);
                $paymentDetails[] = $pay_detail_data;
            }
        }

        $details = $request->details;
        $invoices = [];
        foreach ($details as $detail) {
            $invoices[] = [
                'document_number' => $detail['document_number'],
                'document_date' => $detail['document_date'],
                'nominal_payment' => str_replace(',', '', $detail['nominal_payment'] ?? 0),
                'discount' => str_replace(',', '', $detail['discount'] ?? 0),
            ];
        }

        foreach ($details as $key => $detail) {
            $detail['nominal_payment'] = str_replace(',', '', $detail['nominal_payment'] ?? 0);
            $detail['discount'] = str_replace(',', '', $detail['discount'] ?? 0);
            $payment_detail_id = $payable_payment_number . '_' . ($key + 1);
            $oldDebt = Debt::where('document_number', $detail['document_number'])->first();
            $balance = $oldDebt->debt_balance - $detail['nominal_payment'];
            $total_nominal += $detail['nominal_payment'];
            $total_discount += $detail['discount'];

            PayablePaymentDetail::create([
                'payable_payment_number' => $payable_payment_number,
                'payable_payment_date' => $payable_payment_date,
                'payable_payment_detail_id' => $payment_detail_id,
                'supplier_code' => $supplier_code,
                'document_number' => $detail['document_number'] ?? '',
                'document_date' => $detail['document_date'],
                'document_nominal' => $oldDebt->total_debt ?? 0,
                'document_payment' =>  $detail['nominal_payment'] ?? 0,
                'nominal_payment' => $detail['nominal_payment'] ?? 0,
                'discount' => $detail['discount'] ?? 0,
                'balance' => $balance,
                'acc_debt' => $detail['acc_debt'] ?? null,
                'company_code' => $company_code,
                'department_code' => $department_code,
                'created_by' => $currentUser,
                'updated_by' => $currentUser,
            ]);

            if ($detail['discount'] > 0 || $detail['nominal_payment'] > 0) {
                DebtHistory::create([
                    'document_number' => $detail['document_number'],
                    'document_date' => $detail['document_date'],
                    'supplier_code' => $supplier_code,
                    'payment_number' => $payable_payment_number,
                    'payment_method' => null,
                    'payment_date' => $payable_payment_date,
                    'total_debt' => $oldDebt->total_debt,
                    'payment' => $detail['nominal_payment'],
                    'discount' => $detail['discount'],
                    'debt_balance' => $balance,
                    'company_code' => $company_code,
                    'department_code' => $department_code,
                    'created_by' => $currentUser,
                    'updated_by' => $currentUser,
                ]);

                $oldDebt->debt_balance = $balance;
                $oldDebt->save();
            }
        }

        if (!empty($paymentDetails)) {
            foreach ($paymentDetails as $pay_detail_data) {
                $remaining_amount = $pay_detail_data['payment_nominal'];

                foreach ($invoices as $index => &$invoice) {
                    $invoice['nominal_payment'] = str_replace(',', '', $invoice['nominal_payment'] ?? 0);
                    $invoice['discount'] = str_replace(',', '', $invoice['discount'] ?? 0);

                    if ($remaining_amount <= 0) break;

                    if ($invoice['nominal_payment'] <= 0) continue;

                    $amount_to_apply = min($invoice['nominal_payment'], $remaining_amount);
                    $invoices[$index]['nominal_payment'] -= $amount_to_apply;
                    $remaining_amount -= $amount_to_apply;

                    $payment_detail_id = $payable_payment_number . '_' . ($index + 1);
                    $payment_method = PaymentMethod::where('payment_method_code', $pay_detail_data['payment_method'])->first();

                    if ($amount_to_apply > 0) {
                        PayablePaymentDetailPay::create([
                            'payable_payment_number' => $payable_payment_number,
                            'payable_payment_date' => $payable_payment_date,
                            'payable_payment_detail_id' => $payment_detail_id,
                            'payment_method' => $pay_detail_data['payment_method'],
                            'payment_nominal' => $amount_to_apply,
                            'bg_check_number' => $pay_detail_data['bg_check_number'] ?? null,
                            'acc_debt_bg' => $payment_method->account_number ?? null,
                            'company_code' => $company_code,
                            'department_code' => $department_code,
                            'created_by' => $currentUser,
                            'updated_by' => $currentUser,
                        ]);

                        Journal::create([
                            'document_number' => $payable_payment_number,
                            'document_date' => $payable_payment_date,
                            'account_number' => $payment_method->account_number,
                            'notes' => 'Payment for ' . $invoice['document_number'] . ' by ' . $payment_method->payment_name,
                            'debet_nominal' => 0,
                            'credit_nominal' => $amount_to_apply,
                            'company_code' => $company_code,
                            'department_code' => $department_code,
                            'created_by' => $currentUser,
                            'updated_by' => $currentUser,
                        ]);
                    }
                }
            }
        }
    }

    public function store(Request $request)
    {
        $exist = PayablePayment::where('token', $request->token)->where('department_code', 'DP01')->whereDate('created_at', Carbon::today())->first();
        if ($exist) {
            $id = PayablePayment::where('created_by', Auth::user()->username)->orderBy('id', 'desc')->select('id')->first()->id;
            return redirect()->route('transaction.payable_payment.create')->with('success', 'Payable Payment added successfully!')->with('id', $id);
        }

        DB::beginTransaction();
        try {
            $company = Company::first();
            $company_code = $company->company_code;
            $department_code = $request->department_code;
            $payable_payment_number = $this->generatePayablePaymentNumber($company_code, $department_code);
            $payable_payment_date = $request->document_date;
            $supplier_code = $request->supplier_code;
            $supplier = Supplier::where('supplier_code', $request->supplier_code)->first();
            $currentUser = Auth::user()->username;
            $total_nominal = 0;
            $total_discount = 0;

            $this->saveDetails($request, $payable_payment_number, $payable_payment_date, $supplier_code, $company_code, $department_code, $total_nominal, $total_discount);

            PayablePayment::create([
                'payable_payment_number' => $payable_payment_number,
                'payable_payment_date' => $payable_payment_date,
                'supplier_code' => $supplier_code,
                'total_debt' => $total_nominal ,
                'acc_total' => '',
                'token' => $request->token,
                'acc_disc' => $request->acc_disc,
                'company_code' => $company_code,
                'department_code' => $department_code,
                'created_by' => $currentUser,
                'updated_by' => $currentUser,
            ]);

            if ($total_discount > 0) {
                Journal::create([
                    'document_number' => $payable_payment_number,
                    'document_date' => $payable_payment_date,
                    'account_number' => $request->acc_disc,
                    'notes' => 'Discount on payment for ' . $payable_payment_number,
                    'debet_nominal' => 0,
                    'credit_nominal' => $total_discount,
                    'company_code' => $company_code,
                    'department_code' => $department_code,
                    'created_by' => $currentUser,
                    'updated_by' => $currentUser,
                ]);
            }

            Journal::create([
                'document_number' => $payable_payment_number,
                'document_date' => $payable_payment_date,
                'account_number' => $supplier->account_payable,
                'notes' => 'Total payment for ' . $payable_payment_number,
                'debet_nominal' => $total_nominal ,
                'credit_nominal' => 0,
                'company_code' => $company_code,
                'department_code' => $department_code,
                'created_by' => $currentUser,
                'updated_by' => $currentUser,
            ]);

            $id = PayablePayment::where('payable_payment_number', $payable_payment_number)->select('id')->first()->id;

            DB::commit();
            return redirect()->route('transaction.payable_payment.create')->with('success', 'Payable Payment added successfully!')->with('id', $id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $payable = PayablePayment::findOrFail($id);
        $payable_details = PayablePaymentDetail::where('payable_payment_number', $payable->payable_payment_number)->get();
        $payable_detail_pays = PayablePaymentDetailPay::where('payable_payment_number', $payable->payable_payment_number)->get();
        $purchaseInvoices = PurchaseInvoice::where('supplier_code',$payable->supplier_code)->with('debts')->whereHas('debts', function ($query) {
            $query->where('debt_balance', '>', 0);
        })->get();
        $departments = Department::where('department_code', $payable->department_code)->first();
        $paymentMethods = PaymentMethod::orderBy('id', 'asc')->get();
        $coas = Coa::orderBy('id', 'asc')->get();
        $privileges = Auth::user()->roles->privileges['payable_payment'];
        return view('transaction.payable-payment.edit', compact('payable', 'payable_details', 'payable_detail_pays', 'departments', 'paymentMethods', 'coas', 'privileges','purchaseInvoices'));
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $payablePayment = PayablePayment::findOrFail($id);
            $company = Company::first();
            $company_code = $company->company_code;
            $department_code = $payablePayment->department_code;
            $payable_payment_number = $payablePayment->payable_payment_number;
            $payable_payment_date = $payablePayment->payable_payment_date;
            $supplier_code = $payablePayment->supplier_code;
            $supplier = Supplier::where('supplier_code', $payablePayment->supplier_code)->first();
            $currentUser = Auth::user()->username;
            $total_nominal = 0;
            $total_discount = 0;

            $old_details = PayablePaymentDetail::where('payable_payment_number', $payable_payment_number)->get();
            // Delete existing details and restore debt balances
            PayablePaymentDetailPay::where('payable_payment_number', $payable_payment_number)->delete();
            PayablePaymentDetail::where('payable_payment_number', $payable_payment_number)->delete();
            Journal::where('document_number', $payable_payment_number)->delete();
            DebtHistory::where('payment_number', $payable_payment_number)->delete();

            foreach ($old_details as $detail) {
                $debt = Debt::where('document_number', $detail->document_number)->first();
                if ($debt) {
                    $debt->debt_balance += ($detail->nominal_payment);
                    $debt->save();
                }
            }

            // Save new details
            $this->saveDetails($request, $payable_payment_number, $payable_payment_date, $supplier_code, $company_code, $department_code, $total_nominal, $total_discount);

            // Update PayablePayment
            $payablePayment->update([
                'total_debt' => $total_nominal ,
                'acc_disc' => $request->acc_disc,
                'updated_by' => $currentUser,
            ]);

            // Create journal entries for discounts
            if ($total_discount > 0) {
                Journal::create([
                    'document_number' => $payable_payment_number,
                    'document_date' => $payable_payment_date,
                    'account_number' => $request->acc_disc,
                    'notes' => 'Discount on payment for ' . $payable_payment_number,
                    'debet_nominal' => 0,
                    'credit_nominal' => $total_discount,
                    'company_code' => $company_code,
                    'department_code' => $department_code,
                    'created_by' => $currentUser,
                    'updated_by' => $currentUser,
                ]);
            }

            // Create journal entry for total payment
            Journal::create([
                'document_number' => $payable_payment_number,
                'document_date' => $payable_payment_date,
                'account_number' => $supplier->account_payable,
                'notes' => 'Total payment for ' . $payable_payment_number,
                'debet_nominal' => $total_nominal ,
                'credit_nominal' => 0,
                'company_code' => $company_code,
                'department_code' => $department_code,
                'created_by' => $currentUser,
                'updated_by' => $currentUser,
            ]);

            DB::commit();
            return redirect()->route('transaction.payable_payment')->with('success', 'Payable Payment updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to update: ' . $e->getMessage());
        }
    }

    public function printPDF($id)
    {
        $generals = PayablePayment::with('supplier', 'details')->find($id);
        $pdf = \PDF::loadView('transaction.payable-payment.print', compact('generals'));
        $nameFile = Str::replace("/", "", $generals->payable_payment_number);
        return $pdf->stream("PayablePayment_{$nameFile}.pdf");
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $payablePayment = PayablePayment::findOrFail($id);
            $payable_payment_number = $payablePayment->payable_payment_number;
            $details = PayablePaymentDetail::where('payable_payment_number', $payable_payment_number)->get();
            foreach ($details as $value) {
                $debt = Debt::where('document_number', $value->document_number)->first();
                $debt->debt_balance = $debt->debt_balance + $value->nominal_payment + $value->discount;
                $debt->save();
            }
            PayablePaymentDetailPay::where('payable_payment_number', $payable_payment_number)->delete();
            PayablePaymentDetail::where('payable_payment_number', $payable_payment_number)->delete();
            Journal::where('document_number', $payable_payment_number)->delete();
            DebtHistory::where('payment_number', $payable_payment_number)->delete();
            $payablePayment->delete();
            DB::commit();
            return redirect()->route('transaction.payable_payment')->with('success', 'Payable Payment deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete: ' . $e->getMessage());
        }
    }
}
