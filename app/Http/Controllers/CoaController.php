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

class CoaController extends Controller
{
    // Display COA data and companies
    public function index()
    {
        $coas = Coa::orderBy('account_number', 'asc')->get();
        $coass = CoaType::orderBy('id', 'asc')->get();
        $companies = Company::orderBy('company_code', 'asc')->get();
        $user = Users::where('username',Auth::user()->username)->with('roles')->first();
        $privileges = $user->roles->privileges['master_coa'];
        return view('master.coa', compact('coas','coass','companies','privileges'));
    }

    // Insert a new COA
    public function insert(Request $request)
    {
        DB::beginTransaction();
        try {
            Coa::create([
                'account_number' => $request->account_number,
                'account_name' => $request->account_name,
                'account_type' => $request->account_type,
                'normal_balance' => $request->normal_balance,
                'company_code' => Company::first()->company_code, // Automatically assign company_code
                'created_by' => Auth::user()->username,
                'updated_by' => Auth::user()->username,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Coa added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save: ' . $e->getMessage());
        }
    }

    // Update an existing COA
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            Coa::where('account_number', $id)->update([
                'account_number' => $request->account_number,
                'account_name' => $request->account_name,
                'account_type' => $request->account_type,
                'normal_balance' => $request->normal_balance,
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
            $coa = Coa::where('account_number', $id)->first();
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

    // Import COA data from an uploaded file
    public function import(Request $request)
    {
        if ($request->hasFile('importFile')) {
            $file = $request->file('importFile');
            $companyCode = Company::first()->company_code;  // Automatically get the company code

            DB::beginTransaction();
            try {
                (new FastExcel)->import($file, function ($row) use ($companyCode) {
                    Coa::create([
                        'account_number' => $row['Kode Akun'],
                        'account_name' => $row['Nama Akun'],
                        'account_type' => $row['type'],
                        'normal_balance' => $row['balance'],
                        'company_code' => $companyCode,
                        'created_by' => Auth::user()->username,
                        'updated_by' => Auth::user()->username,
                    ]);
                });
                DB::commit();

                return redirect()->back()->with('success', 'Data imported successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error($e->getMessage());
                return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('error', 'No file uploaded.');
    }

    // Export COA data as XLSX with streaming
    public function export()
    {
        // Prepare headers for the empty template
        $headers = [
            [
                'account_number' => '',
                'account_name' => '',
                'account_type' => '',
                'normal_balance' => ''
            ]
        ];

        // Use FastExcel to export an empty template with just headers
        return (new FastExcel(collect($headers)))->download('coa_template.xlsx');
    }
}
