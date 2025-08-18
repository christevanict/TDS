<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GroupCustomer;
use App\Models\Company;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GroupCustomerController extends Controller
{
    public function index()
    {
        $groupCustomers = GroupCustomer::orderBy('code_group','asc')->get();
        $companies = Company::orderBy('company_code')->get();
        $user = Users::where('username',Auth::user()->username)->with('roles')->first();
        $privileges = $user->roles->privileges['master_customer'];
        return view('master.group-customer',compact('groupCustomers','companies','privileges'));

    }

    public function insert(Request $request){
        DB::beginTransaction();  // Begin the transaction
        try {
            if(GroupCustomer::where('code_group',$request->code_group)->count()<1){
                GroupCustomer::create([
                    'code_group' => $request->code_group,
                    'name_group' => $request->name_group,
                    'detail_customer_name' => $request->detail_customer_name,
                    'company_code' => $request->company_code,
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
                ]);

                DB::commit();  // Commit the transaction
                return redirect()->back()->with('success', 'Group Customer added successfully!');
            }else{
                return redirect()->route('group-customers.index')->with('error', 'Group customer code  must not be same');
            }

        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }
    }

    public function update(Request $request,$id)
    {
        DB::beginTransaction();  // Begin the transaction
        try {
            $request->validate([
                'code_group' => 'required',
                'name_group' => 'required',
                'detail_customer_name' => 'required',
            ]);
            $groupCustomer = GroupCustomer::where('code_group',$id)->update(['code_group'=>$request->code_group,
            'name_group' => $request->name_group,
            'detail_customer_name'=>$request->detail_customer_name]);

            DB::commit();

            return redirect()->back()->with('success', 'Group Customer updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }

    public function delete($id) {
        DB::beginTransaction();  // Begin the transaction
        try {
            $groupCustomer = GroupCustomer::where('code_group',$id);
            $groupCustomer->delete();
            DB::commit();

            return redirect()->back()->with('success', 'Group Customer deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }
}
