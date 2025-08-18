<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalesInvoice;
use App\Models\ReceivablePayment;
use App\Models\ReceivablePaymentDetail;
use App\Models\ReceivablePaymentDetailPay;
use App\Models\Department;
use App\Models\Company;
use App\Models\PaymentMethod;
use App\Models\Customer;
use App\Models\Coa;
use App\Models\Journal;
use App\Models\Periode;
use App\Models\Receivable;
use App\Models\ReceivableHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReceivablePaymentController extends Controller
{
    public function index()
    {
        $receivables = ReceivablePayment::orderBy('id', 'desc')->get();
        $privileges = Auth::user()->roles->privileges['receivable_payment'];
        return view('transaction.receivable-payment.index', compact('receivables', 'privileges'));
    }

    private function generateReceivablePaymentNumber($date)
    {
        $today = Carbon::parse($date);
        $month = $today->format('n'); // Numeric representation of a month (1-12)
        $year = $today->format('y');
        // Convert month to Roman numeral
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        $romanMonth = $romanMonths[$month];
        $prefix = "TDS/RCP/{$romanMonth}/{$year}-"; //

        $lastReceivablePayment = ReceivablePayment::whereRaw('SUBSTRING(receivable_payment_number, 1, ?) = ?', [strlen($prefix), $prefix])
            ->orderBy('receivable_payment_number', 'desc')
            ->first();

        if ($lastReceivablePayment) {
            $lastNumber = (int)substr($lastReceivablePayment->receivable_payment_number, -5);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }

        return "$prefix/$newNumber";
    }

    public function create()
    {
        $departments = Department::where('department_code', 'DP01')->first();
        $paymentMethods = PaymentMethod::orderBy('id', 'asc')->get();
        $customers = Customer::orderBy('id', 'asc')->whereHas('hasGroup')->get();
        $salesInvoices = SalesInvoice::with('receivables')->where('status', 'Open')->whereHas('receivables', function ($query) {
            $query->where('debt_balance', '>', 0);
        })->get();
        $token = Str::random(16);
        $coas = COA::whereRelation('coasss','account_sub_type','!=','PM')->orderBy('account_number', 'asc')->get();
        $privileges = Auth::user()->roles->privileges['receivable_payment'];
        return view('transaction.receivable-payment.input', compact('departments', 'paymentMethods', 'customers', 'coas', 'salesInvoices', 'privileges', 'token'));
    }

    public function store(Request $request)
    {
        $exist = ReceivablePayment::where('token', $request->token)->where('department_code', 'DP01')->whereDate('created_at', Carbon::today())->first();
        if ($exist) {
            $id = ReceivablePayment::where('created_by', Auth::user()->username)->orderBy('id', 'desc')->select('id')->first()->id;
            return redirect()->route('transaction.receivable_payment.create')->with('success', 'Receivable Payment added successfully!')->with('id', $id);
        }

        DB::beginTransaction();
        try {
            $company = Company::first();
            $company_code = $company->company_code;
            $department_code = $request->department_code;
            $receivable_payment_number = $this->generateReceivablePaymentNumber($request->document_date);
            $receivable_payment_date = $request->document_date;
            $customer_code = $request->customer_code;
            $customer = Customer::where('customer_code', $customer_code)->first();
            $currentUser = Auth::user()->username;

            $total_nominal = 0;
            $total_discount = 0;

            $payment_details = $request->payment_details;
            $paymentDetails = [];
            if ($payment_details) {
                foreach ($payment_details as $value) {
                    $rei_detail_data = json_decode($value['payment'], true);
                    $paymentDetails[] = $rei_detail_data;
                }
            }

            $details = $request->details;
            $result = $this->saveDetails($receivable_payment_number, $receivable_payment_date, $customer_code, $details, $paymentDetails, $company_code, $department_code, $currentUser);
            $total_nominal = $result['total_nominal'];
            $total_discount = $result['total_discount'];

            ReceivablePayment::create([
                'receivable_payment_number' => $receivable_payment_number,
                'receivable_payment_date' => $receivable_payment_date,
                'customer_code' => $customer_code,
                'total_debt' => $total_nominal,
                'acc_disc' => $request->acc_disc,
                'token' => $request->token,
                'company_code' => $company_code,
                'department_code' => $department_code,
                'created_by' => $currentUser,
                'updated_by' => $currentUser,
            ]);

            if ($total_discount > 0) {
                Journal::create([
                    'document_number' => $receivable_payment_number,
                    'document_date' => $receivable_payment_date,
                    'account_number' => $request->acc_disc,
                    'notes' => 'Discount on payment for ' . $receivable_payment_number,
                    'debet_nominal' => $total_discount,
                    'credit_nominal' => 0,
                    'company_code' => $company_code,
                    'department_code' => $department_code,
                    'created_by' => $currentUser,
                    'updated_by' => $currentUser,
                ]);
            }

            Journal::create([
                'document_number' => $receivable_payment_number,
                'document_date' => $receivable_payment_date,
                'account_number' => $customer->account_receivable,
                'notes' => 'Total payment for ' . $receivable_payment_number,
                'debet_nominal' => 0,
                'credit_nominal' => $total_nominal,
                'company_code' => $company_code,
                'department_code' => $department_code,
                'created_by' => $currentUser,
                'updated_by' => $currentUser,
            ]);

            $id = ReceivablePayment::where('receivable_payment_number', $receivable_payment_number)->select('id')->first()->id;

            DB::commit();
            return redirect()->route('transaction.receivable_payment.create')->with('success', 'Receivable Payment added successfully!')->with('id', $id);
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save: ' . $e->getMessage());
        }
    }

    private function saveDetails($receivable_payment_number, $receivable_payment_date, $customer_code, $details, $paymentDetails, $company_code, $department_code, $currentUser)
    {
        $total_nominal = 0;
        $total_discount = 0;

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
            $payment_detail_id = $receivable_payment_number . '_' . ($key + 1);
            $oldReceivable = Receivable::where('document_number', $detail['document_number'])->first();
            $balance = $oldReceivable->debt_balance  - $detail['nominal_payment'];
            $total_nominal += $detail['nominal_payment'];
            $total_discount += $detail['discount'];

            ReceivablePaymentDetail::create([
                'receivable_payment_number' => $receivable_payment_number,
                'receivable_payment_date' => $receivable_payment_date,
                'receivable_payment_detail_id' => $payment_detail_id,
                'customer_code' => $customer_code,
                'document_number' => $detail['document_number'] ?? '',
                'document_date' => $detail['document_date'],
                'document_nominal' => $oldReceivable->total_debt ?? 0,
                'document_payment' => $detail['nominal_payment'] ?? 0,
                'nominal' => $detail['nominal_payment'] ?? 0,
                'discount' => $detail['discount'] ?? 0,
                'balance' => $balance,
                'acc_debt' => $detail['acc_debt'] ?? null,
                'company_code' => $company_code,
                'department_code' => $department_code,
                'created_by' => $currentUser,
                'updated_by' => $currentUser,
            ]);

            // Update Receivable and ReceivableHistory even if fully paid by discount
            if ($detail['discount'] > 0 || $detail['nominal_payment'] > 0) {
                ReceivableHistory::create([
                    'document_number' => $detail['document_number'],
                    'document_date' => $detail['document_date'],
                    'customer_code' => $customer_code,
                    'payment_method' => null, // No payment method for discount-only payments
                    'payment_number' => $receivable_payment_number,
                    'payment_date' => $receivable_payment_date,
                    'total_debt' => $oldReceivable->total_debt,
                    'payment' => $detail['nominal_payment'],
                    'discount' => $detail['discount'],
                    'debt_balance' => $balance,
                    'company_code' => $company_code,
                    'department_code' => $department_code,
                    'created_by' => $currentUser,
                    'updated_by' => $currentUser,
                ]);

                $oldReceivable->debt_balance = $balance;
                $oldReceivable->save();
            }
        }

        // Process payment details only if they exist and nominal_payment > 0
        if (!empty($paymentDetails)) {
            foreach ($paymentDetails as $rei_detail_data) {
                $remaining_amount = $rei_detail_data['payment_nominal'];

                foreach ($invoices as $index => &$invoice) {
                    $invoice['nominal_payment'] = str_replace(',', '', $invoice['nominal_payment'] ?? 0);
                    $invoice['discount'] = str_replace(',', '', $invoice['discount'] ?? 0);

                    if ($remaining_amount <= 0) break;

                    if ($invoice['nominal_payment'] <= 0) continue; // Skip if no payment is needed

                    $amount_to_apply = min($invoice['nominal_payment'], $remaining_amount);
                    $invoices[$index]['nominal_payment'] -= $amount_to_apply;
                    $remaining_amount -= $amount_to_apply;

                    $payment_detail_id = $receivable_payment_number . '_' . ($index + 1);
                    $payment_method = PaymentMethod::where('payment_method_code', $rei_detail_data['payment_method'])->first();

                    if ($amount_to_apply > 0) {
                        ReceivablePaymentDetailPay::create([
                            'receivable_payment_number' => $receivable_payment_number,
                            'receivable_payment_date' => $receivable_payment_date,
                            'receivable_payment_detail_id' => $payment_detail_id,
                            'payment_method' => $rei_detail_data['payment_method'] ?? null,
                            'payment_nominal' => $amount_to_apply,
                            'bg_check_number' => $rei_detail_data['bg_check_number'] ?? null,
                            'company_code' => $company_code,
                            'department_code' => $department_code,
                            'created_by' => $currentUser,
                            'updated_by' => $currentUser,
                        ]);

                        Journal::create([
                            'document_number' => $receivable_payment_number,
                            'document_date' => $receivable_payment_date,
                            'account_number' => $payment_method->account_number,
                            'notes' => 'Payment for ' . $invoice['document_number'] . ' by ' . $payment_method->payment_name,
                            'debet_nominal' => $amount_to_apply,
                            'credit_nominal' => 0,
                            'company_code' => $company_code,
                            'department_code' => $department_code,
                            'created_by' => $currentUser,
                            'updated_by' => $currentUser,
                        ]);
                    }
                }
            }
        }

        return ['total_nominal' => $total_nominal, 'total_discount' => $total_discount];
    }

    public function edit($id)
    {
        $receivable = ReceivablePayment::findOrFail($id);
        $originCustomer = Customer::where('customer_code',$receivable->customer_code)->first();
        $receivable_details = ReceivablePaymentDetail::where('receivable_payment_number', $receivable->receivable_payment_number)->get();
        $receivable_detail_pays = ReceivablePaymentDetailPay::where('receivable_payment_number', $receivable->receivable_payment_number)->get();
        $departments = Department::where('department_code', 'DP01')->first();
        $paymentMethods = PaymentMethod::orderBy('id', 'asc')->get();
        $salesInvoices = SalesInvoice::where('customer_code',$receivable->customer_code)->with('receivables')->where('status', 'Open')->whereHas('receivables', function ($query) {
            $query->where('debt_balance', '>', 0);
        })->get();
        $coas = COA::whereRelation('coasss','account_sub_type','!=','PM')->orderBy('account_number', 'asc')->get();
        $privileges = Auth::user()->roles->privileges['receivable_payment'];
        $editable= true;
        $periodeClosed = Periode::where('periode_active', 'closed')
        ->where('periode_start', '<=', $receivable->receivable_payment_date)
        ->where('periode_end', '>=', $receivable->receivable_payment_date)
        ->first();
        if($periodeClosed){
            $editable = false;
        }

        return view('transaction.receivable-payment.edit', compact('receivable', 'receivable_details', 'receivable_detail_pays', 'departments', 'paymentMethods', 'coas', 'privileges','salesInvoices','editable'));
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $receivablePayment = ReceivablePayment::findOrFail($id);
            $company = Company::first();
            $company_code = $company->company_code;
            $department_code = $receivablePayment->department_code;
            $receivable_payment_number = $receivablePayment->receivable_payment_number;
            $receivable_payment_date = $receivablePayment->receivable_payment_date;
            $customer_code = $receivablePayment->customer_code;
            $customer = Customer::where('customer_code', $customer_code)->first();
            $currentUser = Auth::user()->username;

            $old_details = ReceivablePaymentDetail::where('receivable_payment_number', $receivable_payment_number)->get();
            // Delete existing records
            ReceivablePaymentDetailPay::where('receivable_payment_number', $receivable_payment_number)->delete();
            ReceivablePaymentDetail::where('receivable_payment_number', $receivable_payment_number)->delete();
            Journal::where('document_number', $receivable_payment_number)->delete();
            ReceivableHistory::where('payment_number', $receivable_payment_number)->delete();

            // Restore original debt balances
            foreach ($old_details as $detail) {
                $receivable = Receivable::where('document_number', $detail->document_number)->first();
                if ($receivable) {
                    $receivable->debt_balance += ($detail->nominal);
                    $receivable->save();
                }
            }

            $payment_details = $request->payment_details;
            $paymentDetails = [];
            if ($payment_details) {
                foreach ($payment_details as $value) {
                    $rei_detail_data = json_decode($value['payment'], true);
                    $paymentDetails[] = $rei_detail_data;
                }
            }

            $details = $request->details;
            $result = $this->saveDetails($receivable_payment_number, $receivable_payment_date, $customer_code, $details, $paymentDetails, $company_code, $department_code, $currentUser);
            $total_nominal = $result['total_nominal'];
            $total_discount = $result['total_discount'];

            $receivablePayment->update([
                'total_debt' => $total_nominal,
                'acc_disc' => $request->acc_disc,
                'updated_by' => $currentUser,
            ]);

            if ($total_discount > 0) {
                Journal::create([
                    'document_number' => $receivable_payment_number,
                    'document_date' => $receivable_payment_date,
                    'account_number' => $request->acc_disc,
                    'notes' => 'Discount on payment for ' . $receivable_payment_number,
                    'debet_nominal' => $total_discount,
                    'credit_nominal' => 0,
                    'company_code' => $company_code,
                    'department_code' => $department_code,
                    'created_by' => $currentUser,
                    'updated_by' => $currentUser,
                ]);
            }

            Journal::create([
                'document_number' => $receivable_payment_number,
                'document_date' => $receivable_payment_date,
                'account_number' => $customer->account_receivable,
                'notes' => 'Total payment for ' . $receivable_payment_number,
                'debet_nominal' => 0,
                'credit_nominal' => $total_nominal,
                'company_code' => $company_code,
                'department_code' => $department_code,
                'created_by' => $currentUser,
                'updated_by' => $currentUser,
            ]);

            DB::commit();
            return redirect()->route('transaction.receivable_payment')->with('success', 'Receivable Payment updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to update: ' . $e->getMessage());
        }
    }

    public function printPDF($id)
    {
        $generals = ReceivablePayment::with('customer', 'details')->find($id);
        $pdf = \PDF::loadView('transaction.receivable-payment.print', compact('generals'));
        $nameFile = Str::replace("/", "", $generals->receivable_payment_number);
        return $pdf->stream("ReceivablePayment_{$nameFile}.pdf");
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $receivable = ReceivablePayment::findOrFail($id);
            $receivable_payment_number = $receivable->receivable_payment_number;
            $details = ReceivablePaymentDetail::where('receivable_payment_number', $receivable_payment_number)->get();

            foreach ($details as $detail) {
                $receiv = Receivable::where('document_number', $detail->document_number)->first();
                $receiv->debt_balance = $receiv->debt_balance + $detail->nominal + $detail->discount;
                $receiv->save();
            }

            ReceivablePaymentDetail::where('receivable_payment_number', $receivable_payment_number)->delete();
            ReceivablePaymentDetailPay::where('receivable_payment_number', $receivable_payment_number)->delete();
            Journal::where('document_number', $receivable_payment_number)->delete();
            ReceivableHistory::where('payment_number', $receivable_payment_number)->delete();

            $receivable->delete();

            DB::commit();
            return redirect()->route('transaction.receivable_payment')->with('success', 'Receivable Payment deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete: ' . $e->getMessage());
        }
    }
}
