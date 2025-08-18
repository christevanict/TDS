<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CityController extends Controller
{
    public function index()
    {
        $city = City::orderBy('city_code','asc')->get();
        $user = Users::where('username',Auth::user()->username)->with('roles')->first();
        $privileges = $user->roles->privileges['master_city'];
        return view('master.city',[
            'citys' => $city,
            'privileges'=>$privileges
        ]);
    }

    public function insert(Request $request){
        DB::beginTransaction();  // Begin the transaction
        try {
            if(City::where('city_code',$request->city_code)->count()<1){
                $general = new City();
                $general->city_code = $request->city_code;
                $general->city_name = $request->city_name;
                $general->is_active = $request->is_active;
                $general->created_by = Auth::user()->username;
                $general->updated_by = Auth::user()->username;
                $general->save();


                DB::commit();
                return redirect()->back()->with('success', 'City added successfully!');
            }else{
                return redirect()->back()->with('error', 'City code must not be same');

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
            $city  = City::where('city_code',$id)->first();
            $city->is_active = $city->is_active==1?0:1;
            $city->save();
            DB::commit();
            return redirect()->back()->with('success', 'City edited successfully!');
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
            $customer = City::where('city_code',$id)->update([
                    'city_code'=>$id,
                    'city_name'=>$request->city_name,
                    'is_active'=>$request->is_active,
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'City updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }

    public function delete($id) {
        DB::beginTransaction();  // Begin the transaction
        try {
            $salesman = City::where('city_code',$id);
            $salesman->delete();
            DB::commit();

            return redirect()->back()->with('success', 'City deleted successfully!');
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
                'city_code' => '',
                'city_name' => '',
            ]
        ];

        // Use FastExcel to export an empty template with headers
        return (new FastExcel(collect($headers)))->download('city_template.xlsx');
    }
}
