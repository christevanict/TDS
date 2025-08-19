<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\GroupCustomer;
use App\Models\CategoryCustomer;
use App\Models\Coa;
use App\Models\Currency;
use App\Models\Company;
use App\Models\Department;
use App\Models\InventoryDetail;
use App\Models\Pbr;
use App\Models\Receivable;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\SalesReturn;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Rap2hpoutre\FastExcel\FastExcel;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::orderBy('customer_code','asc')->get();
        $coas = Coa::whereRelation('coasss','account_sub_type','!=','PM')->orderBy('account_number', 'asc')->get();
        $companies = Company::orderBy('company_code','asc')->get();
        $currencies = Currency::orderBy('currency_code','asc')->get();
        $groups = GroupCustomer::orderBy('code_group','asc')->get();
        $user = Users::where('username',Auth::user()->username)->with('roles')->first();
        $department_code = 'DP01';
        $department_name = Department::where('department_code',$department_code)->first()->department_name;
        $privileges = $user->roles->privileges['master_customer'];
        return view('master.customer',compact('customers','coas','companies','currencies','groups','privileges','department_code','department_name'));
    }

    private function generateCustomerNumber() {
        $lastCustomer = Customer::orderBy('id', 'desc')
            ->first();
        if ($lastCustomer) {
            $lastNumber = (int)substr($lastCustomer->customer_code, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        return 'CS-TDS'. $newNumber;
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
            if($request->department == 'DP01') {
                $customer_code = $request->customer_code;
            }
            else {
                $customer_code = $request->department.'.'.$request->customer_code;
            }

            if(Customer::where('customer_code',$request->customer_code)->count()<1){
                Customer::create([
                    'customer_code'=>$customer_code,
                    'customer_name'=>$request->customer_name,
                    'address'=>$request->address??'',
                    'warehouse_address'=>$request->warehouse_address,
                    'phone_number'=>$request->phone_number,
                    'pkp'=>$request->pkp,
                    'include'=>$request->include,
                    'bonded_zone'=>$request->bonded_zone,
                    'currency_code'=>$request->currency_code,
                    'email'=>$request->email??null,
                    'zone'=>$request->zone??null,
                    'city'=>$request->city??null,
                    'sales'=>$request->sales??null,
                    'npwp'=>$request->npwp??null,
                    'nik'=>$request->nik??null,
                    'group_customer'=>$request->group_customer??'DEFAULT',
                    'account_receivable'=>$request->account_receivable,
                    'account_dp'=>$request->account_dp,
                    'account_add_tax'=>$request->account_add_tax,
                    'account_add_tax_bonded_zone'=>$request->account_add_tax_bonded_zone,
                    'company_code'=>$request->company_code,
                    'department_code'=>'DP01',
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
                ]);

                DB::commit();
                return redirect()->back()->with('success', 'Customer added successfully!');
            }else{
                return redirect()->back()->with('error', 'Customer code  must not be same');

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
        DB::beginTransaction();  // Begin the transaction
        try {

            $customer_code = $request->customer_code;
            $customer = Customer::where('customer_code',$id)->update([
                    'customer_code'=>$customer_code,
                    'customer_name'=>$request->customer_name,
                    'address'=>$request->address??'',
                    'warehouse_address'=>$request->warehouse_address,
                    'phone_number'=>$request->phone_number,
                    'pkp'=>$request->pkp,
                    'include'=>$request->include,
                    'bonded_zone'=>$request->bonded_zone,
                    'currency_code'=>$request->currency_code,
                    'group_customer'=>$request->group_customer??'DEFAULT',
                    'email'=>$request->email??null,
                    'zone'=>$request->zone??null,
                    'city'=>$request->city??null,
                    'sales'=>$request->sales??null,
                    'npwp'=>$request->npwp??null,
                    'nik'=>$request->nik??null,
                    // 'category_customer'=>$request->category_customer,
                    'account_receivable'=>$request->account_receivable,
                    'account_dp'=>$request->account_dp,
                    'account_add_tax'=>$request->account_add_tax,
                    'account_add_tax_bonded_zone'=>$request->account_add_tax_bonded_zone,
                    'company_code'=>$request->company_code,
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
            ]);


            DB::commit();
            return redirect()->back()->with('success', 'Customer updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }

    public function delete($id) {
        DB::beginTransaction();  // Begin the transaction
        try {
            $customer = Customer::where('customer_code',$id);
            $relatedModels = [
                'SalesInvoice' => SalesInvoice::where('customer_code', $id)->exists(),
            ];
            // Check if customer is used in any related model
            foreach ($relatedModels as $modelName => $exists) {
                if ($exists) {
                    return redirect()->back()->with('error', "Tidak bisa hapus pelanggan, pelanggan sudah digunakan");
                }
            }
            $customer->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Customer deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }
    }

    public function import(Request $request)
    {
        // dd($request);
        if ($request->hasFile('importFile')) {
            $file = $request->file('importFile');
            $companyCode = Company::first()->company_code;  // Automatically get the company code
            $department = $request->department;
            $account_receivable = $request->account_receivable;
            $account_dp = $request->account_dp;
            $account_add_tax = $request->account_add_tax;
            $account_add_tax_bonded_zone = $request->account_add_tax_bonded_zone;

            // dd($request);
            DB::beginTransaction();
            try {
                (new FastExcel)->import($file, function ($row) use ($companyCode,$account_receivable,$account_dp,$account_add_tax,$account_add_tax_bonded_zone, $department) {
                    // $customer_code = $this->generateCustomerNumber($companyCode, $row['customer_name']);
                    if($department == 'DP01') {
                        $customer_code = $row['No. Pelanggan.'];
                        $pkp = true;
                        $include = true;
                    } else {
                        $customer_code = $department.'.'.$row['No. Pelanggan.'];
                        $pkp = false;
                        $include = false;
                    }
                    $groupCustomer = GroupCustomer::where('code_group',$row['Kode Group'])->first();
                    if(!$groupCustomer) {
                        GroupCustomer::create([
                            'code_group' => $row['Kode Group'],
                            'name_group' => $row['Nama Pelanggan'],
                            'detail_customer_name' => $row['Nama Pelanggan'],
                            'company_code' => $companyCode,
                            'created_by'=>Auth::user()->username,
                            'updated_by'=>Auth::user()->username,
                        ]);

                    }
                    // dd(Coa::where('account_number',$account_receivable)->get());
                    Customer::create([
                        'customer_code' => $customer_code,
                        'customer_name' => $row['Nama Pelanggan'],
                        'address' => '',
                        'warehouse_address' => '',
                        'phone_number' => '',
                        'pkp' => $pkp,
                        'include' => $include,
                        'currency_code' => '',
                        'category_customer' => '',
                        'group_customer' => $row['Kode Group'],
                        'city' => $row['Kota'],
                        'zone' => $row['DAERAH'],
                        'sales' => $row['SALES'],
                        'nik' => $row['NIK'],
                        'npwp' => $row['Nomor Pajak'],
                        'account_receivable' => $account_receivable,
                        'account_dp' => $account_dp,
                        'account_add_tax' => $account_add_tax,
                        'account_add_tax_bonded_zone' => $account_add_tax_bonded_zone,
                        'company_code' => $companyCode,
                        'created_by' => Auth::user()->username,
                        'updated_by' => Auth::user()->username,
                    ]);

                });
                DB::commit();

                return redirect()->back()->with('success', 'Customer data imported successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                // dd($e);
                Log::error($e->getMessage());
                return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('error', 'No file uploaded.');
    }
    public function importExtra(Request $request)
    {
        // dd($request);
        if ($request->hasFile('importFile')) {
            $file = $request->file('importFile');

            DB::beginTransaction();
            try {
                (new FastExcel)->import($file, function ($row) {
                    $customer = Customer::where('customer_code','like',$row['No'].'%')->where('customer_name',strval($row['Nama Pelanggan']))->first();
                    if($customer){
                        $customer->sales = $row['SALES']??null;
                        $customer->zone = $row['DAERAH']??null;
                        $customer->nik = $row['NIK']??null;
                        $customer->city = $row['Kota']??null;
                        $customer->npwp = $row['Nomor Pajak']??null;
                        $customer->save();
                    }
                });
                DB::commit();

                return redirect()->back()->with('success', 'Customer data imported successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                // dd($e);
                Log::error($e->getMessage());
                return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('error', 'No file uploaded.');
    }

    public function importMb2(Request $request)
    {
        // dd($request);
        if ($request->hasFile('importFile')) {
            $file = $request->file('importFile');

            DB::beginTransaction();
            try {
                $account_receivable = '1100.01';
                $account_dp = '2100.02';
                $account_add_tax = '2410.01';
                $account_add_tax_bonded_zone = '2410.01';
                (new FastExcel)->import($file, function ($row) use ($account_receivable,$account_dp,$account_add_tax,$account_add_tax_bonded_zone){
                    $customer = Customer::where('customer_code','DP02.'.$row['customer_code'])->where('customer_name',strval($row['customer_name']))->where('department_code','DP02')->first();
                    if(!$customer){
                        Customer::create([
                            'customer_code' => 'DP02.'.$row['customer_code'],
                            'customer_name' => $row['customer_name'],
                            'address' => $row['address'],
                            'warehouse_address' => $row['warehouse_address'],
                            'phone_number' => $row['phone_number'],
                            'pkp' => false,
                            'include' => false,
                            'currency_code' => '',
                            'category_customer' => '',
                            'group_customer' => 'DP02.'.$row['group_customer'],
                            'city' => $row['city'],
                            'zone' => $row['zone'],
                            'sales' => $row['sales'],
                            'nik' => $row['nik'],
                            'npwp' => $row['npwp'],
                            'account_receivable' => $account_receivable,
                            'account_dp' => $account_dp,
                            'account_add_tax' => $account_add_tax,
                            'account_add_tax_bonded_zone' => $account_add_tax_bonded_zone,
                            'company_code' => $row['company_code'],
                            'department_code' => 'DP02',
                            'created_by' => Auth::user()->username,
                            'updated_by' => Auth::user()->username,
                        ]);
                    }
                });
                DB::commit();

                return redirect()->back()->with('success', 'Customer data imported successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                dd($e);
                Log::error($e->getMessage());
                return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('error', 'No file uploaded.');
    }

    public function export()
    {
        // Prepare headers for the customer template
        $headers = [
            [
                'customer_code' => '',
                'customer_name' => '',
            ]
        ];

        // Use FastExcel to export an empty template with headers
        return (new FastExcel(collect($headers)))->download('customer_template.xlsx');
    }

    public function updateCustomerMb34()
    {
        $customers = Customer::where('department_code','DP01')->whereNotNull('sales')->get();
        foreach ($customers as $cust) {
            Customer::where('department_code','!=','DP01')->whereNull('sales')->where('customer_code','like',"%{$cust->customer_code}%")->where('customer_name',$cust->customer_name)->update(['sales'=>$cust->sales]);
        }
        return redirect()->back()->with('success', 'Customer Updated');
    }

    public function setGroupCustomerMb3()
    {
        DB::beginTransaction();
        try {
            $customers = Customer::where('department_code','DP03')->get();
            foreach ($customers as $value) {
                $exist = GroupCustomer::where('code_group',$value->group_customer);
                if(!$exist){
                    $head = Customer::where('department_code','DP03')->where('customer_code',$value->group_customer)->first();
                    $name = $head? $head->customer_name:$value->customer_name;
                    GroupCustomer::create([
                        'code_group'=>$value->group_customer,
                        'name_group'=>$name,
                        'detail_customer_name'=>$name,
                        'company_code'=>'TDS',
                        'created_by'=>'superadminICT',
                        'updated_by'=>'superadminICT',
                        'department_code'=>'DP03',
                    ]);
                }
            }
            DB::commit();
            return redirect()->route('transaction.sales_invoice')->with('success', 'Sales Invoice recalculate successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($e);
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to create Purchase Invoice: ' . $e->getMessage())->withInput();
        }
    }

    public function resetCustomerMb34()
    {
        DB::beginTransaction();
        try {
            $customers = Customer::where('department_code','!=','DP01')->get();
            foreach ($customers as $value) {
                $custMb2 = Customer::where('department_code','DP01')->where('customer_name',$value->customer_name)->where('group_customer',$value->group_customer)->first();
                if($custMb2&&str_contains($value->customer_code,$custMb2->customer_code)){
                    SalesOrder::where('customer_code',$value->customer_code)->update(['customer_code'=>$custMb2->customer_code]);
                    SalesInvoice::where('customer_code',$value->customer_code)->update(['customer_code'=>$custMb2->customer_code]);
                    SalesReturn::where('customer_code',$value->customer_code)->update(['customer_code'=>$custMb2->customer_code]);
                    Receivable::where('customer_code',$value->customer_code)->update(['customer_code'=>$custMb2->customer_code]);
                    InventoryDetail::where('customer_code',$value->customer_code)->update(['customer_code'=>$custMb2->customer_code]);
                    Pbr::where('customer_code',$value->customer_code)->update(['customer_code'=>$custMb2->customer_code]);
                }else{
                    $soExist = SalesOrder::where('customer_code',$value->customer_code)->first();
                    $siExist = SalesInvoice::where('customer_code',$value->customer_code)->first();
                    $srExist = SalesReturn::where('customer_code',$value->customer_code)->first();
                    $reExist = Receivable::where('customer_code',$value->customer_code)->first();
                    $idExist = InventoryDetail::where('customer_code',$value->customer_code)->first();
                    $pbrExist = Pbr::where('customer_code',$value->customer_code)->first();
                    if($soExist||$siExist||$srExist||$reExist||$idExist||$pbrExist){
                        Log::info('Customer: '.$value->customer_code);
                    }
                }
            }

            DB::commit();
            return redirect()->route('transaction.sales_invoice')->with('success', 'Sales Invoice recalculate successfully.');
        } catch (\Exception $e) {
            //throw $th;
            DB::rollBack();
            dd($e);
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to create Purchase Invoice: ' . $e->getMessage())->withInput();
        }

    }
}
