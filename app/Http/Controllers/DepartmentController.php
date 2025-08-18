<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Company;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::orderBy('department_code','asc')->get();
        $companies = Company::orderBy('company_code','asc')->get();
        $user = Users::where('username',Auth::user()->username)->with('roles')->first();
        $privileges = $user->roles->privileges['master_department'];
        return view('master.department',[
            'departments' => $departments,
            'companies'=>$companies,
            'privileges'=>$privileges,
        ]);
    }

    private function generateDepartmentCode() {
        $lastSalesOrder = Department::orderBy('id', 'desc')
            ->first();
        // dd($lastSalesOrder);
        if ($lastSalesOrder) {
            $lastNumber = (int)substr($lastSalesOrder->department_code, -2);
            $newNumber = str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '01';
        }

        return 'DP'. $newNumber;
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
            $code = $this->generateDepartmentCode();
            Department::create([
                'department_code'=>$code,
                'department_name'=>$request->department_name,
                'company_code'=>$request->company_code,
                'address'=>$request->address,
                'phone'=>$request->phone,
                'created_by'=>Auth::user()->username,
                'updated_by'=>Auth::user()->username,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Department added successfully!');


        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            // dd($e);
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
            $dept = Department::where('id',$id)->update([
                'department_name'=>$request->department_name,
                'address'=>$request->address,
                'phone'=>$request->phone,
                'updated_by'=>Auth::user()->username,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Department updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }



    }

    public function delete($id) {
        DB::beginTransaction();  // Begin the transaction
        try {
            $dept = Department::where('id',$id);
            $dept->delete();
            DB::commit();
            return redirect()->back()->with('success', 'Department deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }
}
