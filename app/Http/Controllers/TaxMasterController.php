<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaxMaster;
use App\Models\Company;
use App\Models\Coa;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaxMasterController extends Controller
{
    public function index()
    {
        $taxs = TaxMaster::orderBy('tax_code','asc')->get();
        $companies = Company::orderBy('company_code','asc')->get();
        $coas = Coa::orderBy('account_number','asc')->get();
        $user = Users::where('username',Auth::user()->username)->with('roles')->first();
        $privileges = $user->roles->privileges['master_tax_master'];
        return view('master.tax-master',[
            'taxs' => $taxs,
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
            if(TaxMaster::where('tax_code',$request->tax_code)->count()<1){
                TaxMaster::create([
                    'tax_code'=>$request->tax_code,
                    'tax_name'=>$request->tax_name,
                    'tariff'=>str_replace(',','.',$request->tariff),
                    'tax_base'=>$request->tax_base,
                    'account_number'=>$request->account_number,
                    'company_code'=>$request->company_code,
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
                ]);

                DB::commit();
                return redirect()->back()->with('success', 'Tax added successfully!');
            }else{
                return redirect()->back()->with('error', 'Tax Code  must not be same');

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
                $tax = TaxMaster::where('id',$id)->update([
                    'tax_code'=>$request->tax_code,
                    'tax_name'=>$request->tax_name,
                    'tariff'=>$request->tariff,
                    'tax_base'=>$request->tax_base,
                    'account_number'=>$request->account_number,
                    'company_code'=>$request->company_code,
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
                ]);

                DB::commit();
                return redirect()->back()->with('success', 'Tax updated successfully!');
            }
        catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }

    public function delete($id) {
        DB::beginTransaction();  // Begin the transaction
        try {
            $tax = TaxMaster::where('id',$id);
            $tax->delete();
                DB::commit();
                return redirect()->back()->with('success', 'Tax deleted successfully!');
            }
        catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }
}
