<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ItemPurchase;
use App\Models\Company;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\Supplier;
use App\Models\ItemDetail;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ItemPurchaseController extends Controller
{
    public function index()
    {
        $itemPurchases = ItemPurchase::where('department_code','DP01')->orderBy('barcode', 'asc')->get();
        $companies = Company::orderBy('id', 'asc')->get();
        $items = Item::where('department_code','DP01')->with('itemDetails')->orderBy('id', 'asc')->get();
        $units = ItemUnit::orderBy('id', 'asc')->get();
        $suppliers = Supplier::orderBy('id', 'asc')->get();
        $user = Users::where('username',Auth::user()->username)->with('roles')->first();
        $privileges = $user->roles->privileges['master_item'];
        // dd($details);
        return view('master.item-purchase', [
            'itemPurchases' => $itemPurchases,
            'companies' => $companies,
            'items' => $items,
            'units' => $units,
            'suppliers' => $suppliers,
            'privileges'=>$privileges,
        ]);

    }

    public function insert(Request $request)
    {
        DB::beginTransaction();  // Begin the transaction
        try {
            // Ensure that unit is not null
            $item = ItemDetail::where('department_code','DP01')->where('item_code',$request->item_code)->first();
            $unit = $item->unit_conversion;
            $item_code = $item->item_code;

            ItemPurchase::create([
                'barcode' => $item->barcode,
                'item_code' => $item_code,
                'purchase_price' => $request->purchase_price,
                'unit' => $unit,
                'supplier' => $request->supplier,
                'department_code' => 'DP01',
                'company_code' => $request->company_code,
                'created_by' => Auth::user()->username,
                'updated_by' => Auth::user()->username,
            ]);




            DB::commit();  // Commit the transaction
            return redirect()->back()->with('success', 'Item Purchase added successfully!');

        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }


    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // Uncomment the validation if needed
            $item = ItemDetail::where('department_code','DP01')->where('barcode',$request->barcode)->first();
            $unit = $item->unit_conversion;
            $item_code = $item->item_code;
            $itemPurchase = ItemPurchase::where([
                ['id', $id],
                ])->update([
                'barcode' => $request->barcode,
                'item_code' => $item_code,
                'purchase_price' => $request->purchase_price,
                'unit' => $unit,
                'supplier' => $request->supplier,
                'department_code' => 'DP01',
                'company_code' => $request->company_code,
                'created_by' => Auth::user()->username,
                'updated_by' => Auth::user()->username,
            ]);



            DB::commit();
            return redirect()->back()->with('success', 'Item Purchase updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $itemPurchase = ItemPurchase::findOrFail($id);

            $itemPurchase->delete();
            DB::commit();
            return redirect()->back()->with('success', 'Item Purchase deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }
}
