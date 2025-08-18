<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Coa;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Rap2hpoutre\FastExcel\FastExcel;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('supplier_code','asc')->where('department_code','DP01')->get();
        $companies = Company::orderBy('company_code','asc')->get();
        $currencies = Currency::orderBy('currency_code','asc')->get();
        $coas = Coa::orderBy('account_number','asc')->get();
        $user = Users::where('username',Auth::user()->username)->with('roles')->first();
        $privileges = $user->roles->privileges['master_supplier'];
        return view('master.supplier',[
            'suppliers' => $suppliers,
            'companies'=>$companies,
            'currencies'=>$currencies,
            'coas'=>$coas,
            'privileges'=>$privileges,
        ]);
    }

    private function generateSupplierNumber($company, $name) {
        $lastSupplier = Supplier::orderBy('id', 'desc')
            ->first();
        if ($lastSupplier) {
            $lastNumber = (int)substr($lastSupplier->supplier_code, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return  'VI-TDS' . $newNumber;
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
            $supplier_code = $request->supplier_code;
            // $supplier_code = $this->generateSupplierNumber($request->company_code, $request->supplier_name);
            if(Supplier::where('supplier_code',$request->supplier_code)->count()<1){
                Supplier::create([
                    'supplier_code'=>$supplier_code,
                    'supplier_name'=>$request->supplier_name,
                    'address'=>$request->address,
                    'warehouse_address'=>$request->warehouse_address,
                    'phone_number'=>$request->phone_number,
                    'pkp'=>$request->pkp,
                    'include'=>$request->include,
                    'currency_code'=>$request->currency_code,
                    'npwp'=>$request->npwp,
                    'department_code'=>'DP01',
                    'account_payable'=>$request->account_payable,
                    'account_dp'=>$request->account_dp,
                    'account_payable_grpo'=>$request->account_payable_grpo,
                    'account_add_tax'=>$request->account_add_tax,
                    'company_code'=>$request->company_code,
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
                ]);



                DB::commit();
                return redirect()->back()->with('success', 'Supplier added successfully!');
            }else{
                return redirect()->back()->with('error', 'Supplier code  must not be same');

            }
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
        $supplier_code = $request->supplier_code;
        DB::beginTransaction();  // Begin the transaction
        try {
            // dd($request->pkp);
            Supplier::where('supplier_code',$id)->update([
                'supplier_code'=>$supplier_code,
                'supplier_name'=>$request->supplier_name,
                'address'=>$request->address,
                'warehouse_address'=>$request->warehouse_address,
                'phone_number'=>$request->phone_number,
                'pkp'=>$request->pkp,
                'include'=>$request->include,
                'npwp'=>$request->npwp,
                'department_code'=>'DP01',
                'currency_code'=>$request->currency_code,
                'account_payable'=>$request->account_payable,
                'account_dp'=>$request->account_dp,
                'account_payable_grpo'=>$request->account_payable_grpo,
                'account_add_tax'=>$request->account_add_tax,
                'company_code'=>$request->company_code,
                'created_by'=>Auth::user()->username,
                'updated_by'=>Auth::user()->username,
            ]);



            DB::commit();

            return redirect()->back()->with('success', 'Supplier updated successfully!');
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

            $supp = Supplier::where('supplier_code',$id);
            $supp->delete();
            DB::commit();

            return redirect()->back()->with('success', 'Supplier deleted successfully!');
        }
        catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }
    public function import(Request $request)
    {
        if ($request->hasFile('importFile')) {
            $file = $request->file('importFile');
            $companyCode = Company::first()->company_code;  // Secara otomatis mendapatkan kode perusahaan
            $account_payable = $request->account_payable;
            $account_dp = $request->account_dp;
            $account_payable_grpo = $request->account_payable_grpo;
            $account_add_tax = $request->account_add_tax;

            DB::beginTransaction();
            try {
                (new FastExcel)->import($file, function ($row) use ($companyCode,$account_payable,$account_dp,$account_payable_grpo,$account_add_tax) {
                    $supplier_code = $row['supplier_code'];
                    // $supplier_code = $this->generateSupplierNumber($companyCode, $row['supplier_name']);
                    Supplier::create([
                        'supplier_code' => $supplier_code,
                        'supplier_name' => $row['supplier_name'],
                        'address' => '',
                        'warehouse_address' => '',
                        'phone_number' => '',
                        'pkp' => $pkp,
                        'include' => $include,
                        'currency_code' => '',
                        'company_code' => $companyCode,
                        'account_payable' => $account_payable,
                        'account_dp' => $account_dp,
                        'account_payable_grpo' => $account_payable_grpo,
                        'account_add_tax' => $account_add_tax,
                        'created_by' => Auth::user()->username,
                        'updated_by' => Auth::user()->username,
                    ]);

                });
                DB::commit();

                return redirect()->back()->with('success', 'Data supplier berhasil diimpor!');
            } catch (\Exception $e) {
                DB::rollBack();
                // dd($e);
                Log::error($e->getMessage());
                return redirect()->back()->with('error', 'Impor gagal: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('error', 'Tidak ada file yang diunggah.');
    }

    // Ekspor data supplier sebagai XLSX dengan streaming
    public function export()
    {
        // Siapkan header untuk template kosong
        $headers = [
            [
                'supplier_code' => '',
                'supplier_name' => '',
            ]
        ];

        // Gunakan FastExcel untuk mengekspor template kosong dengan hanya header
        return (new FastExcel(collect($headers)))->download('supplier_template.xlsx');
    }
}
