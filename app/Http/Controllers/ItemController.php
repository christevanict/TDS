<?php

namespace App\Http\Controllers;

use App\Models\Coa;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Company;
use App\Models\GoodReceiptDetail;
use App\Models\ItemUnit;
use App\Models\ItemCategory;
use App\Models\ItemDetail;
use App\Models\InventoryDetail;
use App\Models\ItemPurchase;
use App\Models\ItemSalesPrice;
use App\Models\PurchaseInvoiceDetail;
use App\Models\PurchaseOrderDetail;
use App\Models\PurchaseReturnDetail;
use App\Models\SalesInvoiceDetail;
use App\Models\SalesOrderDetail;
use App\Models\SalesReturnDetail;
use App\Models\Users;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rap2hpoutre\FastExcel\FastExcel;

class ItemController extends Controller
{
    // Display the list of items
    public function index()
    {
        $items = Item::where('department_code','DP01')->orderBy('id', 'asc')->get();
        $companies = Company::orderBy('company_code', 'asc')->first();
        $itemUnits = ItemUnit::orderBy('unit', 'asc')->get();
        $itemCategories = ItemCategory::orderBy('id', 'asc')->get();
        $user = Users::where('username',Auth::user()->username)->with('roles')->first();
        $privileges = $user->roles->privileges['master_item'];
        return view('master.item', [
            'items' => $items,
            'itemCategories' => $itemCategories,
            'companies' => $companies,
            'itemUnits' => $itemUnits,
            'privileges'=>$privileges,
        ]);
    }

    private function generateItemNumber($name) {
        $today = date('Ymd');
        $lastItem = Item::where('item_category',$name)->orderBy('id', 'desc')
            ->first();
            // dd($lastItem);
        if ($lastItem) {
            $lastNumber = (int)substr($lastItem->item_code, -5);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }

        return $name .'_' . $newNumber;
    }

    // Display the input form for a new item
    public function inputForm()
    {
        $itemCategories = ItemCategory::all(); // Fetch item categories
        $itemUnits = ItemUnit::all(); // Fetch item units
        $companies = Company::first(); // Fetch companies
        $coas = Coa::all();

        return view('master.item.item_input', compact('itemCategories', 'itemUnits', 'companies','coas'));
    }

    // Handle the insertion of a new item
    public function insert(Request $request)
    {
        // $request->validate([
        //     'item_code' => 'required',
        //     'item_name' => 'required',
        //     'item_category' => 'required',
        //     'base_unit' => 'required',
        //     'sales_unit' => 'required',
        //     'purchase_unit' => 'required',
        //     'additional_tax' => 'nullable',
        //     'company_code' => 'required',
        // ], [
        //     // Custom error messages
        //     'item_code.required' => 'Item Code ',
        //     'item_name.required' => 'Item Name ',
        //     'item_category.required' => 'Item Category ',
        //     'base_unit.required' => 'Base Unit ',
        //     'sales_unit.required' => 'Sales Unit ',
        //     'purchase_unit.required' => 'Purchase unit ',
        //     'additional_tax.required' => 'Additionoal Tax ',
        //     'company_code.required' => 'Company Code ',
        // ]);

        // $item_code = $this->generateItemNumber($request->item_category);

        DB::beginTransaction();
        try {
            // Create the new item
            Item::create([
                'item_code' => $request->item_code,
                'item_name' => $request->item_name,
                'item_category' => $request->item_category,
                'base_unit' => $request->base_unit,
                'sales_unit' => $request->sales_unit,
                'purchase_unit' => $request->purchase_unit,
                'additional_tax' => $request->additional_tax,
                'include' => $request->include,
                // 'qty' => 0, // Initial quantity
                'status' => true,
                'department_code' => 'DP01',
                'company_code' => $request->company_code,
                'created_by' => Auth::user()->username,
                'updated_by' => Auth::user()->username,
            ]);

            ItemSalesPrice::create([
                'barcode' => '',
                'item_code' => $request->item_code,
                'sales_price' => 1,
                'unit' => $request->sales_unit,
                'status' => true,
                'unit' => $request->sales_unit,
                'category_customer' => 'DEFAULT',
                'department_code' => 'DP01',
                'company_code' => $request->company_code,
                'created_by' => Auth::user()->username,
                'updated_by' => Auth::user()->username,
            ]);

            if($request->item_details){
                $this->saveItemDetails($request->item_details, $request->item_code, $request->base_unit, $request->company_code, $request->additional_tax);
            }

            DB::commit();
            // Redirect to the item list with success message
            return redirect()->route('item.index')->with('success', 'Item added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to save: ' . $e->getMessage());
        }
    }

    // Display the edit form for an existing item
    public function edit($item_code)
    {
        $item = Item::where('id', $item_code)->firstOrFail();
        $itemCategories = ItemCategory::all();
        $itemUnits = ItemUnit::all();
        $companies = Company::first();
        $itemDetails = ItemDetail::where('department_code','DP01')->where('item_code', $item->item_code)->get();
        $editable = true;
        $si = SalesInvoiceDetail::where('item_id', $item->item_code)->where('department_code', 'DP01')->get();
        $so = SalesOrderDetail::where('item_id', $item->item_code)->where('department_code', 'DP01')->get();
        $pi = PurchaseInvoiceDetail::where('item_id', $item->item_code)->where('department_code', 'DP01')->get();
        $po = PurchaseOrderDetail::where('item_id', $item->item_code)->where('department_code', 'DP01')->get();

        $editable = (count($si) == 0 && count($so) == 0 && count($pi) == 0 && count($po) == 0);



        return view('master.item.item_edit', compact('item', 'itemCategories', 'itemUnits', 'companies', 'itemDetails','editable'));
    }

    // Handle the update of an existing item
    public function update(Request $request, $id)
    {
        $item_code = Item::find($id)->item_code;
        // $request->validate([
        //     'item_code' => 'required',
        //     'item_name' => 'required',
        //     'item_category' => 'required',
        //     'base_unit' => 'required',
        //     'sales_unit' => 'required',
        //     'purchase_unit' => 'required',
        //     'additional_tax' => 'nullable',
        //     'company_code' => 'required',
        // ], [
        //     // Custom error messages
        //     'item_code.required' => 'Item Code ',
        //     'item_name.required' => 'Item Name ',
        //     'item_category.required' => 'Item Category ',
        //     'base_unit.required' => 'Base Unit ',
        //     'sales_unit.required' => 'Sales Unit ',
        //     'purchase_unit.required' => 'Purchase unit ',
        //     'additional_tax.required' => 'Additionoal Tax ',
        //     'company_code.required' => 'Company Code ',
        // ]);


        DB::beginTransaction();
        try {
            $updatedFields = [
                'item_code' => $request->item_code,
                'item_name' => $request->item_name,
                'item_category' => $request->item_category,
                'base_unit' => $request->base_unit,
                'sales_unit' => $request->sales_unit,
                'purchase_unit' => $request->purchase_unit,
                'additional_tax' => $request->additional_tax,
                'include' => $request->include,
                'company_code' => $request->company_code,
            ];

            ItemDetail::where('department_code','DP01')->where('item_code',$item_code)->delete();

            $item = Item::find($id);

            Item::where('item_code', $item_code)->update([
                'item_code' => $item_code,
                'item_name' => $request->item_name,
                'item_category' => $request->item_category,
                'base_unit' => $request->base_unit,
                'sales_unit' => $request->sales_unit,
                'purchase_unit' => $request->purchase_unit,
                'additional_tax' => $request->additional_tax,
                'company_code' => $request->company_code,
                'updated_by' => Auth::user()->username,
            ]);


            if($request->item_details){
                $this->saveItemDetails($request->item_details, $item_code, $request->base_unit, $request->company_code, $request->additional_tax);
            }
            DB::commit();
            return redirect()->route('item.index')->with('success', 'Item updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e->getMessage());
            if (stripos($e->getMessage(), 'violates foreign key constraint') !== false) {
                return redirect()->back()->with('error', 'Failed to update: This item is linked to another record and cannot be modified.');
            }else{
                return redirect()->back()->with('error', 'Failed to update: ' . $e->getMessage());
            }
        }
    }

    // Handle the deletion of an item by item_code
    public function delete(Request $request, $id)
    {
        $item = Item::where('id', $id)->firstOrFail();
        $boleh = true;
        $exist1 = SalesOrderDetail::where('item_id',$item->item_code)->exists();
        $exist2 = GoodReceiptDetail::where('item_id',$item->item_code)->exists();
        $exist3 = PurchaseReturnDetail::where('item_id',$item->item_code)->exists();
        $exist4 = SalesReturnDetail::where('item_id',$item->item_code)->exists();
        $exist5 = PurchaseOrderDetail::where('item_id',$item->item_code)->exists();
        if($exist1||$exist2||$exist3||$exist4||$exist5) $boleh=false;

        if(!$boleh){
            return redirect()->back()->with('error', 'Item ini tidak dapat di hapus karena telah digunakan');
        }

        DB::beginTransaction();

        try {

            // Find the item by item_code
            $item = Item::where('id', $id)->firstOrFail();
            // Delete related item details if necessary
            ItemDetail::where('department_code','DP01')->where('item_code', $item->item_code)->delete();
            ItemSalesPrice::where('department_code','DP01')->where('item_code', $item->item_code)->delete();
            ItemPurchase::where('department_code','DP01')->where('item_code', $item->item_code)->delete();


            // Delete the item
            $item->delete();

            DB::commit();
            return redirect()->route('item.index')->with('success', 'Item deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            if (stripos($e->getMessage(), 'violates foreign key constraint') !== false) {
                return redirect()->back()->with('error', 'Failed to delete: This item is linked to another record and cannot be deleted.');
            }else{
                return redirect()->back()->with('error', 'Failed to delete: ' . $e->getMessage());
            }
        }
    }

    private function saveItemDetails(array $itemDetails, $item_code, $base_unit, $company_code, $tax) {
        foreach ($itemDetails as $index => $detail) {
            $detail['conversion'] = str_replace(',', '.', $detail['conversion']??0);
            $detail['item_code'] = $item_code;
            $detail['base_unit'] = $base_unit;
            $detail['barcode'] = $item_code.'_'.$detail['unit_conversion'];
            $detail['department_code'] = 'DP01';
            $detail['company_code'] = $company_code;
            $detail['created_by'] = Auth::user()->username;
            $detail['updated_by'] = Auth::user()->username;

            $detail['status'] = array_key_exists('stat',$detail)? true:false;


            ItemDetail::create($detail);
            $detail['created_at'] = now();
            $detail['updated_at'] = now();

        }
    }

    // Display the details of a specific item
    public function show($item_code)
    {
        $item = Item::where('id', $item_code)->firstOrFail();
        $itemDetails = ItemDetail::where('department_code','DP01')->where('item_code', $item->item_code)->get();
        $itemCategory = ItemCategory::where('item_category_code',$item->item_category)->first();
        // dd($itemCategory);
        // $company = Company::find($item->company_code);
        // $itemUnit = ItemUnit::find($item->base_unit);

        return view('master.item.item_show', compact('item', 'itemDetails', 'itemCategory'));
    }

    public function import(Request $request)
    {
        if ($request->hasFile('importFile')) {
            $file = $request->file('importFile');
            $companyCode = Company::first()->company_code;  // Automatically get the company code

            DB::beginTransaction();
            try {
                (new FastExcel)->import($file, function ($row) use ($companyCode) {
                    $itemCategory = ItemCategory::first();
                    Item::create([
                        'item_code' => $row['No. Barang'],
                        'item_category' => $itemCategory->item_category_code,
                        'item_name' => $row['Deskripsi Barang'],
                        'base_unit' => $row['Satut'],
                        'sales_unit' => $row['Satut'],
                        'purchase_unit' => $row['Satut'],
                        'additional_tax' => '1',
                        'include' => '1',
                        'department_code' => 'DP01',
                        'company_code' => $companyCode,
                        'created_by' => Auth::user()->username,
                        'updated_by' => Auth::user()->username,
                    ]);
                    ItemDetail::create([
                        'item_code' => $row['No. Barang'],
                        'base_unit' => $row['Satut'],
                        'conversion' => 1,
                        'unit_conversion' => $row['Satut'],
                        'barcode' => $row['No. Barang'].'_'.$row['Satut'],
                        'department_code' => 'DP01',
                        'company_code' => $companyCode,
                        'created_by' => Auth::user()->username,
                        'updated_by' => Auth::user()->username,
                    ]);
                    if($row['Konversi2']&&$row['Satuan2']!=''){
                        ItemDetail::create([
                            'item_code' => $row['No. Barang'],
                            'base_unit' => $row['Satut'],
                            'conversion' => (float) $row['Konversi2'],
                            'unit_conversion' => $row['Satuan2'],
                            'barcode' => $row['No. Barang'].'_'.$row['Satuan2'],
                            'company_code' => $companyCode,
                            'department_code' => 'DP01',
                            'created_by' => Auth::user()->username,
                            'updated_by' => Auth::user()->username,
                        ]);
                    }
                    if($row['Harga Jual']){
                        ItemSalesPrice::create([
                            'barcode' => $row['No. Barang'].'_'.$row['Satut'],
                            'item_code' => $row['No. Barang'],
                            'unit' => strtoupper($row['Satut']),
                            'sales_price'=>$row['Harga Jual'],
                            'category_customer' => 'DEFAULT',
                            'department_code' => 'DP01',
                            'company_code' => $companyCode,
                            'created_by' => Auth::user()->username,
                            'updated_by' => Auth::user()->username,
                        ]);
                    }
                    if($row['Harga Beli']){
                        ItemPurchase::create([
                            'barcode' => $row['No. Barang'].'_'.$row['Satut'],
                            'item_code' => $row['No. Barang'],
                            'unit' => strtoupper($row['Satut']),
                            'purchase_price'=>$row['Harga Beli'],
                            'category_customer' => 'DEFAULT',
                            'department_code' => 'DP01',
                            'company_code' => $companyCode,
                            'created_by' => Auth::user()->username,
                            'updated_by' => Auth::user()->username,
                        ]);
                    }
                });
                DB::commit();

                return redirect()->back()->with('success', 'Data imported successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                // dd($e);
                Log::error($e);
                return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('error', 'No file uploaded.');
    }
    public function importSaldo(Request $request)
    {
        if ($request->hasFile('importFile')) {
            $file = $request->file('importFile');
            $companyCode = Company::first()->company_code;  // Automatically get the company code

            DB::beginTransaction();
            try {
                (new FastExcel)->import($file, function ($row) use ($companyCode) {
                    $item = Item::where('item_code',$row['No. Barang'])->where('department_code','DP01')->first();
                    if($item){
                        $oldInventoryDetail = InventoryDetail::where('item_id',$row['No. Barang'])->where('department_code','DP01')->where('transaction_type','SYSTEM')->first();
                        if($oldInventoryDetail){
                            $oldInventoryDetail->quantity = $row['Kuantitas Baru'];
                            $oldInventoryDetail->last_qty = $row['Kuantitas Baru'];
                            $oldInventoryDetail->qty_actual = $row['Kuantitas Baru'];
                            $oldInventoryDetail->total = $row['Nilai Akhir Baru'];
                            $oldInventoryDetail->cogs = $row['Nilai Akhir Baru'];
                            $oldInventoryDetail->save();
                        }
                    }
                });
                DB::commit();

                return redirect()->back()->with('success', 'Data imported successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                // dd($e);
                Log::error($e);
                return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('error', 'No file uploaded.');
    }

    public function importItemWarehouse(Request $request)
    {
        if ($request->hasFile('importFile')) {
            $file = $request->file('importFile');  // Automatically get the company code

            DB::beginTransaction();
            try {
                (new FastExcel)->import($file, function ($row)  {
                    $item = Item::where('item_code',$row['Kode Item'])->where('department_code','DP01')->first();
                    $warehouse = Warehouse::where('warehouse_code',$row['Lokasi'])->first();
                    if($item&&$warehouse){
                        $item->warehouse_code = $row['Lokasi'];
                        $item->save();
                        InventoryDetail::where('item_id',$row['Kode Item'])->where('department_code','DP01')->update(['warehouse_id'=>$warehouse->id]);
                    }
                });

                DB::commit();

                return redirect()->back()->with('success', 'Data imported successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                // dd($e);
                Log::error($e);
                return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('error', 'No file uploaded.');
    }

    public function setItemPurchaseSales(){
        DB::beginTransaction();
        try {
            $items = Item::all();
            $companyCode = Company::first();
            foreach ($items as $key => $item) {
                $itemDetails = ItemDetail::where('item_code',$item->item_code)->where('conversion','!=',1)->first();
                $itemPurchaseExist = ItemPurchase::where('item_code',$item->item_code)->where('department_code',$item->department_code)->first();
                if(!$itemPurchaseExist&&$itemDetails){
                    ItemPurchase::create(
                        [
                            'barcode'=>$itemDetails->barcode,
                            'item_code'=>$item->item_code,
                            'purchase_price'=>1,
                            'unit'=>$itemDetails->unit_conversion,
                            'supplier'=>null,
                            'company_code'=>$companyCode->company_code,
                            'department_code'=>$item->department_code,
                            'created_by'=>'superadminICT',
                            'updated_by'=>'superadminICT',
                        ]
                    );
                }
                $itemSalesExist = ItemSalesPrice::where('item_code',$item->item_code)->where('department_code',$item->department_code)->first();
                if(!$itemSalesExist&&$itemDetails){
                    ItemSalesPrice::create(
                        [
                            'barcode'=>$itemDetails->barcode,
                            'item_code'=>$item->item_code,
                            'sales_price'=>1,
                            'unit'=>$itemDetails->unit_conversion,
                            'category_customer'=>'DEFAULT',
                            'company_code'=>$companyCode->company_code,
                            'department_code'=>$item->department_code,
                            'created_by'=>'superadminICT',
                            'updated_by'=>'superadminICT',
                        ]
                    );
                }
            }
            DB::commit();

            return redirect()->route('item.index')->with('success', 'Data imported successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->route('item.index')->with('error', 'Import failed: ' . $e->getMessage());
        }

    }
}
