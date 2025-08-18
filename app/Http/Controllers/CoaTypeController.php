<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coa;
use App\Models\CoaType;
use App\Models\Company;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rap2hpoutre\FastExcel\FastExcel;

class CoaTypeController extends Controller
{
    // Display COA data and companies
    public function index()
    {
        $coass = CoaType::orderBy('id', 'asc')->get();
        $companies = Company::orderBy('company_code', 'asc')->get();
        $user = Users::where('username',Auth::user()->username)->with('roles')->first();
        $privileges = $user->roles->privileges['master_coa'];
        return view('master.coa-type', compact('coass','companies','privileges'));
    }

    // Insert a new COA
    public function insert(Request $request)
    {
        DB::beginTransaction();
        try {
            CoaType::create([
                'account_sub_type' => $request->account_sub_type,
                'account_type' => $request->account_type,
                'company_code' => Company::first()->company_code, // Automatically assign company_code
                'created_by' => Auth::user()->username,
                'updated_by' => Auth::user()->username,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Coa added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save: ' . $e->getMessage());
        }
    }

    // Update an existing COA
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            CoaType::where('id', $id)->update([
                'account_sub_type' => $request->account_sub_type,
                'account_type' => $request->account_type,
                'company_code' => Company::first()->company_code, // Automatically assign company_code
                'updated_by' => Auth::user()->username,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Coa updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to update: ' . $e->getMessage());
        }
    }

    // Delete a COA entry
    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $coa = CoaType::where('id', $id)->first();
            if ($coa) {
                $coa->delete();
                DB::commit();
                return redirect()->back()->with('success', 'Coa deleted successfully!');
            } else {
                return redirect()->back()->with('error', 'Coa not found.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete: ' . $e->getMessage());
        }
    }

}
