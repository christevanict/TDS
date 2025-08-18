<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ItemSalesPrice;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\Company;
use App\Models\CategoryCustomer;
use App\Models\ItemDetail;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ItemSalesPriceController extends Controller
{
    /**
     * Display a listing of the item sales prices.
     */
    public function index()
    {
        $itemSalesPrices = ItemSalesPrice::where('department_code','DP01')->orderBy('id', 'asc')->get();
        $items = Item::where('department_code','DP01')->with('itemDetails')->orderBy('id', 'asc')->get();
        $itemUnits = ItemUnit::orderBy('unit', 'asc')->get();
        $companies = Company::orderBy('company_code', 'asc')->get();
        // $customerCategories = CategoryCustomer::orderBy('category_code', 'asc')->get();
        $user = Users::where('username',Auth::user()->username)->with('roles')->first();
        $privileges = $user->roles->privileges['master_item_sales'];
        return view('master.item_sales_price', [
            'itemSalesPrices' => $itemSalesPrices,
            'items' => $items,
            'itemUnits' => $itemUnits,
            'companies' => $companies,
            // 'customerCategories' => $customerCategories,
            'created_by' => Auth::user()->username,
            'updated_by' => Auth::user()->username,
            'privileges'=>$privileges,
        ]);
    }

    /**
     * Insert a new item sales price.
     */
    public function insert(Request $request)
    {
        DB::beginTransaction();  // Begin the transaction
        try {
            // Ensure that unit is not null
            $item = ItemDetail::where('department_code','DP01')->where('item_code',$request->item_code)->first();
            $unit = $item->unit_conversion;
            $item_code = $item->item_code;


            ItemSalesPrice::create([
                'barcode' => $item->barcode,
                'item_code' => $item_code,
                'sales_price' => $request->sales_price,
                'unit' => $unit,
                'category_customer' => $request->category_customer,
                'department_code' => 'DP01',
                'company_code' => $request->company_code,
                'created_by' => Auth::user()->username,
                'updated_by' => Auth::user()->username,
            ]);


            DB::commit();  // Commit the transaction
            return redirect()->back()->with('success', 'Item sales price added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }
    }

    /**
     * Update an existing item sales price.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();  // Begin the transaction
        try {
            // Ensure that unit is not null
            $item = ItemDetail::where('department_code','DP01')->where('barcode',$request->barcode)->first();
            $unit = $item->unit_conversion;
            $item_code = $item->item_code;

            ItemSalesPrice::where('id', $id)->update([
                'barcode' => $request->barcode,
                'item_code' => $item_code,
                'sales_price' => $request->sales_price,
                'unit' => $unit,
                'category_customer' => $request->category_customer,
                'company_code' => $request->company_code,
                'updated_by' => Auth::user()->username,
            ]);


            DB::commit();  // Commit the transaction
            return redirect()->back()->with('success', 'Item sales price updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }
    }

    /**
     * Delete an item sales price.
     */
    public function delete($id)
    {
        DB::beginTransaction();  // Begin the transaction
        try {
            $itemSalesPrice = ItemSalesPrice::findOrFail($id);
            $itemSalesPrice->delete();

            DB::commit();  // Commit the transaction
            return redirect()->back()->with('success', 'Item sales price deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete');
        }
    }

    /**
     * Display a grid of item sales prices.
     */
    // public function gridItemSalesPrice()
    // {
    //     $itemSalesPrices = ItemSalesPrice::orderBy('item_code', 'asc')->get();
    //     $items = Item::orderBy('item_code', 'asc')->get();
    //     $itemUnits = ItemUnit::orderBy('unit', 'asc')->get();
    //     $companies = Company::orderBy('company_code', 'asc')->get();
    //     $customerCategories = CategoryCustomer::orderBy('category_code', 'asc')->get();

    //     return view('grid.item_sales_price', [
    //         'itemSalesPrices' => $itemSalesPrices,
    //         'items' => $items,
    //         'itemUnits' => $itemUnits,
    //         'companies' => $companies,
    //         'customerCategories' => $customerCategories,
    //     ]);
    // }
}
