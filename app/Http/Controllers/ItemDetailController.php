<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ItemDetail;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ItemDetailController extends Controller
{
    public function index()
    {
        $itemDetails = ItemDetail::where('department_code','DP01')->where('conversion', '<>', 1)->orderBy('id', 'asc')->get();
        $items = Item::orderBy('item_code', 'asc')->get();
        $itemUnits = ItemUnit::orderBy('unit', 'asc')->get();
        $companies = Company::orderBy('company_code', 'asc')->get();
        $uuid = Str::uuid()->toString();

        return view('master.item_detail', [
            'itemDetails' => $itemDetails,
            'items' => $items,
            'itemUnits' => $itemUnits,
            'companies' => $companies,
            'created_by' => Auth::user()->username,
            'updated_by' => Auth::user()->username,
        ]);
    }

    public function insert(Request $request)
    {
        DB::beginTransaction();  // Begin the transaction
        try {
            // Check if the item exists
            $item = Item::where('item_code', $request->item_code)->first();
            $uuid = Str::uuid()->toString();
            if (!$item) {
                return redirect()->back()->with('not_found', 'The selected item could not be found.');
            }

            ItemDetail::create([
                'item_code' => $request->item_code,
                'base_unit' => $request->base_unit,
                'conversion' => $request->conversion,
                'unit_conversion' => $request->unit_conversion,
                'barcode' => $uuid,
                'department_code'=>'DP01',
                'company_code' => $request->company_code,
                'created_by' => Auth::user()->username,
                'updated_by' => Auth::user()->username,
            ]);
            DB::commit();  // Commit the transaction
            return redirect()->back()->with('success', 'Item detail added successfully!');
        } catch (\Exception $e) {

            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();  // Begin the transaction
        try {
            ItemDetail::where('id', $id)->update([
                'item_code' => $request->item_code,
                'base_unit' => $request->base_unit,
                'conversion' => $request->conversion,
                'unit_conversion' => $request->unit_conversion,
                'company_code' => $request->company_code,
                'updated_by' => Auth::user()->username,
            ]);
            DB::commit();  // Commit the transaction
            return redirect()->back()->with('success', 'Item detail updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();  // Begin the transaction
        try {
            $itemDetail = ItemDetail::findOrFail($id);
            $itemDetail->delete();
            DB::commit();  // Commit the transaction
            return redirect()->back()->with('success', 'Item detail deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }
    }

    public function gridItem()
    {
        $itemDetails = ItemDetail::where('department_code','DP01')->orderBy('item_code', 'asc')->get();
        $items = Item::orderBy('item_code', 'asc')->get();
        $itemUnits = ItemUnit::orderBy('unit', 'asc')->get();
        $companies = Company::orderBy('company_code', 'asc')->get();

        return view('grid.item', [
            'itemDetails' => $itemDetails,
            'items' => $items,
            'itemUnits' => $itemUnits,
            'companies' => $companies,
        ]);
    }
}
