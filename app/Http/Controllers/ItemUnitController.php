<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ItemUnit;
use App\Models\Company;
use App\Models\Users;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ItemUnitController extends Controller
{
    /**
     * Display a listing of item units.
     */
    public function index()
    {
        // Fetch all item units ordered by 'unit'
        $itemUnits = ItemUnit::orderBy('unit', 'asc')->get();
        $companies = Company::orderBy('company_code', 'asc')->get();
        $user = Users::where('username',Auth::user()->username)->with('roles')->first();
        $privileges = $user->roles->privileges['master_item'];
        return view('master.item-unit', [
            'itemUnits' => $itemUnits,
            'companies' => $companies,
            'privileges'=>$privileges,
        ]);
    }

    /**
     * Insert a new item unit.
     */
    public function insert(Request $request)
    {
        DB::beginTransaction();
        try {
            if (ItemUnit::where('unit', $request->unit)->count() < 1) {
                ItemUnit::create([
                    'unit' => $request->unit,
                    'unit_name' => $request->unit_name,
                    'company_code' => $request->company_code,
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
                ]);

                DB::commit();
                return redirect()->back()->with('success', 'Item Unit added successfully!');
            } else {
                return redirect()->back()->with('error', 'Unit code must not be the same.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }
    }

    /**
     * Update an existing item unit.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            ItemUnit::where('unit', $id)->update([
                'unit' => $request->unit,
                'unit_name' => $request->unit_name,
                'company_code' => $request->company_code,
                'updated_by'=>Auth::user()->username,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Item Unit updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }
    }

    /**
     * Delete an item unit.
     */
    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $itemUnit = ItemUnit::where('unit',$id);
            $itemUnit->delete();
            DB::commit();
            return redirect()->back()->with('success', 'Item Unit deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete');
        }
    }
}
