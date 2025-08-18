<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Warehouse;
use App\Models\Company;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::orderBy('warehouse_code','asc')->get();
        $companies = Company::orderBy('company_code','asc')->get();
        $user = Users::where('username',Auth::user()->username)->with('roles')->first();
        $privileges = $user->roles->privileges['master_warehouse'];
        return view('master.warehouse',[
            'warehouses' => $warehouses,
            'companies' => $companies,
            'privileges'=>$privileges,
        ]);
    }

    private function generateWarehouseCode() {
        $lastSalesOrder = Warehouse::whereDate('created_at', now()->format('Y-m-d'))
            ->orderBy('created_at', 'desc')
            ->first();
        if ($lastSalesOrder) {
            $lastNumber = (int)substr($lastSalesOrder->warehouse_code, -5);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }

        return 'WR'. $newNumber;
    }

    public function insert(Request $request){
        // $result = $request->validate([
        //     'company_code' => 'required',
        //     'company_name' => 'required',
        //     'address' => 'required',
        //     'phone_number' => 'required',
        //     'npwp' => 'required',
        //     'type_company' => 'required',
        // ]);
        DB::beginTransaction();  // Begin the transaction
        try {
            $code = $this->generateWarehouseCode();
            if(Warehouse::where('warehouse_code',$request->warehouse_code)->count()<1){
                Warehouse::create([
                    'warehouse_code'=>$request->warehouse_code,
                    'warehouse_name' => $request->warehouse_name,
                    'company_code' => $request->company_code,
                    'created_by' => Auth::user()->username,
                    'updated_by' => Auth::user()->username,
                ]);

 

                DB::commit();  // Commit the transaction
                return redirect()->back()->with('success', 'Warehouse added successfully!');
            }else{
                return redirect()->back()->with('error', 'Warehouse code  must not be same');
            }

        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }

    public function update(Request $request,$id)
    {
        // $request->validate([
        //     'company_code' => 'required',
        //     'company_name' => 'required',
        //     'address' => 'required',
        //     'phone_number' => 'required',
        //     'npwp' => 'required',
        //     'type_company' => 'required',
        // ]);
        DB::beginTransaction();  // Begin the transaction
        try {
            $warehouse = Warehouse::where('id',$id)->update(['warehouse_code'=>$request->warehouse_code,
            'warehouse_name' => $request->warehouse_name,
                'company_code' => $request->company_code,
                'updated_by' => Auth::user()->username,
            ]);




            DB::commit();  // Commit the transaction


            return redirect()->back()->with('success', 'Warehouse updated successfully!');


        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }

    public function delete($id) {

        DB::beginTransaction();  // Begin the transaction
        try {
            $warehouse = Warehouse::findOrFail($id);
            $warehouse->delete();
            DB::commit();  // Commit the transaction
            return redirect()->back()->with('success', 'Warehouse deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }
}
