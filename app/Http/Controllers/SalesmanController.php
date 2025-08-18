<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Salesman;
use App\Models\City;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SalesmanController extends Controller
{
    public function index()
    {
        $salesman = Salesman::orderBy('salesman_code','asc')->get();
        $citys = City::where("is_active",1)->get();
        $user = Users::where('username',Auth::user()->username)->with('roles')->first();
        $privileges = $user->roles->privileges['master_salesman'];
        return view('master.salesman',[
            'salesmans' => $salesman,
            'citys' => $citys,
            'privileges'=>$privileges
        ]);
    }

    public function insert(Request $request){
        DB::beginTransaction();  // Begin the transaction
        try {
            if(Salesman::where('salesman_code',$request->salesman_code)->count()<1){
                $general = new Salesman();
                $general->salesman_code = $request->salesman_code;
                $general->salesman_name = $request->salesman_name;
                $general->is_active = $request->is_active;
                $general->city_code = $request->city_code;
                $general->created_by = Auth::user()->username;
                $general->updated_by = Auth::user()->username;
                $general->save();


                DB::commit();
                return redirect()->back()->with('success', 'Salesman added successfully!');
            }else{
                return redirect()->back()->with('error', 'Salesman code must not be same');

            }
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }

    public function inactive($id){
        DB::beginTransaction();
        try {
            $salesman  = Salesman::where('salesman_code',$id)->first();
            $salesman->is_active = $salesman->is_active==1?0:1;
            $salesman->save();
            DB::commit();
            return redirect()->back()->with('success', 'Salesman edited successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Please try again');
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
            $customer = Salesman::where('salesman_code',$id)->update([
                    'salesman_code'=>$id,
                    'salesman_name'=>$request->salesman_name,
                    'is_active'=>$request->is_active,
                    'city_code' => $request->city_code,
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Salesman updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }

    public function delete($id) {
        DB::beginTransaction();  // Begin the transaction
        try {
            $salesman = Salesman::where('salesman_code',$id);
            $salesman->delete();
            DB::commit();

            return redirect()->back()->with('success', 'Salesman deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }
    }

    public function export()
    {
        // Prepare headers for the customer template
        $headers = [
            [
                'salesman_code' => '',
                'salesman_name' => '',
            ]
        ];

        // Use FastExcel to export an empty template with headers
        return (new FastExcel(collect($headers)))->download('salesman_template.xlsx');
    }
}
