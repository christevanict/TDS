<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\Company;
use App\Models\Coa;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $pays = PaymentMethod::orderBy('payment_method_code','asc')->get();
        $companies = Company::orderBy('company_code','asc')->get();
        $coas = Coa::orderBy('account_number','asc')->get();
        $user = Users::where('username',Auth::user()->username)->with('roles')->first();
        $privileges = $user->roles->privileges['master_payment_method'];
        return view('master.payment-method',[
            'pays' => $pays,
            'coas' => $coas,
            'companies'=>$companies,
            'privileges'=>$privileges,
        ]);
    }

    public function insert(Request $request){
        // $result = $request->validate([
        //     'account_number' => 'required',
        //     'account_number' => 'required',
        //     'account_type' => 'required',
        //     'company_code' => 'required',
        // ]);
        DB::beginTransaction();  // Begin the transaction
        try {
            if(PaymentMethod::where('payment_method_code',$request->payment_method_code)->count()<1){
                PaymentMethod::create([
                    'payment_method_code'=>$request->payment_method_code,
                    'payment_name'=>$request->payment_name,
                    'cost_payment'=>$request->cost_payment,
                    'account_number'=>$request->account_number,
                    'acc_number_cost'=>$request->acc_number_cost,
                    'company_code'=>$request->company_code,
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
                ]);

                DB::commit();
                return redirect()->back()->with('success', 'Payment Method added successfully!');
            }else{
                return redirect()->back()->with('error', 'Payment Method Code  must not be same');

            }
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }

    public function update(Request $request,$id)
    {
        // $result = $request->validate([
        //     'account_number' => 'required',
        //     'account_number' => 'required',
        //     'account_type' => 'required',
        //     'company_code' => 'required',
        // ]);
        DB::beginTransaction();  // Begin the transaction
        try {
            $pay = PaymentMethod::where('payment_method_code',$id)->update([
                    'payment_method_code'=>$request->payment_method_code,
                    'payment_name'=>$request->payment_name,
                    'cost_payment'=>$request->cost_payment,
                    'account_number'=>$request->account_number,
                    'acc_number_cost'=>$request->acc_number_cost,
                    'company_code'=>$request->company_code,
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
            ]);



            DB::commit();

            return redirect()->back()->with('success', 'Payment Method updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }

    public function delete($id) {
        DB::beginTransaction();  // Begin the transaction
        try {
            $pay = PaymentMethod::where('payment_method_code',$id);
            $pay->delete();
            DB::commit();

            return redirect()->back()->with('success', 'Payment Method deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }
}
