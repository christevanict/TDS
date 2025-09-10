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

    private function generatePayablePaymentNumber($date)
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
        $prefix = "TDS/PAP/{$romanMonth}/{$year}-"; //

        $lastPayablePayment = PayablePayment::whereRaw('SUBSTRING(payable_payment_number, 1, ?) = ?', [strlen($prefix), $prefix])
            ->orderBy('payable_payment_number', 'desc')
            ->first();

        if ($lastPayablePayment) {
            $lastNumber = (int)substr($lastPayablePayment->payable_payment_number, -5);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }

        return "$prefix$newNumber";
    }

    public function create()
    {
        $departments = Department::where('department_code', 'DP01')->first();
        $paymentMethods = PaymentMethod::orderBy('id', 'asc')->get();
        $suppliers = Supplier::orderBy('id', 'asc')->get();
        $debts = Debt::whereRaw('FLOOR(debt_balance) != 0')->orderBy('document_date','asc')->get();
        $coas = Coa::whereRelation('coasss','account_sub_type','!=','PM')->orderBy('account_number', 'asc')->get();
        $token = Str::random(16);
        $privileges = Auth::user()->roles->privileges['payable_payment'];
        return view('transaction.payable-payment.input', compact('departments', 'paymentMethods', 'suppliers', 'coas', 'debts', 'privileges', 'token'));
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

                    if($remaining_amount>0){
                        $amount_to_apply = min($invoice['nominal_payment'], $remaining_amount);
                    }else{
                        $amount_to_apply = max($invoice['nominal_payment'], $remaining_amount);
                    }
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
                    }
                }
            }
        }
        $supplier = Supplier::where('supplier_code', $request->supplier_code)->first();
        foreach ($paymentDetails as $key => $detail) {
            $payment_method = PaymentMethod::where('payment_method_code', $detail['payment_method'])->first();
            $debet=0;
            $credit =$detail['payment_nominal'];
            if($detail['payment_nominal']<0){
                $credit = 0;
                $debet = abs($detail['payment_nominal']);
            }

            if($payment_method){
                //Payment Method
                Journal::create([
                    'document_number' => $payable_payment_number,
                    'document_date' => $payable_payment_date,
                    'account_number' => $payment_method->account_number,
                    'notes' => $supplier->supplier_name.' Pelunasan hutang',
                    'debet_nominal' => $debet,
                    'credit_nominal' => $credit,
                    'company_code' => $company_code,
                    'department_code' => $department_code,
                    'created_by' => $currentUser,
                    'updated_by' => $currentUser,
                ]);
            }
        }

        //DISC
        $debetD=0;
        $creditD =$total_discount;
        if($total_discount<0){
            $debetD = abs($total_discount);
            $creditD = 0;
        }

        if ($total_discount != 0) {
            Journal::create([
                'document_number' => $payable_payment_number,
                'document_date' => $payable_payment_date,
                'account_number' => $request->acc_disc,
                'notes' => $supplier->supplier_name.' Pelunasan hutang',
                'debet_nominal' =>$debetD,
                'credit_nominal' => $creditD,
                'company_code' => $company_code,
                'department_code' => $department_code,
                'created_by' => $currentUser,
                'updated_by' => $currentUser,
            ]);
        }

        //Hutang  / Payable
        $debet=$total_nominal;
        $credit =0;
        if($total_nominal<0){
            $credit = abs($total_nominal);
            $debet = 0;
        }

        Journal::create([
            'document_number' => $payable_payment_number,
            'document_date' => $payable_payment_date,
            'account_number' => $supplier->account_payable,
            'notes' => $supplier->supplier_name.' Pelunasan hutang',
            'debet_nominal' => $debet,
            'credit_nominal' => $credit,
            'company_code' => $company_code,
            'department_code' => $department_code,
            'created_by' => $currentUser,
            'updated_by' => $currentUser,
        ]);
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
            $payable_payment_number = $this->generatePayablePaymentNumber($request->document_date);
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
        $debts = Debt::where('supplier_code',$payable->supplier_code)->whereRaw('FLOOR(debt_balance) != 0')->orderBy('document_date','asc')->get();
        $departments = Department::where('department_code', $payable->department_code)->first();
        $paymentMethods = PaymentMethod::orderBy('id', 'asc')->get();
        $coas = Coa::whereRelation('coasss','account_sub_type','!=','PM')->orderBy('account_number', 'asc')->get();
        $privileges = Auth::user()->roles->privileges['payable_payment'];
        return view('transaction.payable-payment.edit', compact('payable', 'payable_details', 'payable_detail_pays', 'departments', 'paymentMethods', 'coas', 'privileges','debts'));
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
                $debt->debt_balance += $value->nominal_payment;
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
