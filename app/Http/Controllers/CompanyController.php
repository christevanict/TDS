<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\TypeCompany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::orderBy('company_code', 'asc')->get();
        $typeCompanies = TypeCompany::orderBy('id', 'asc')->get();
        return view('master.company', [
            'companies' => $companies,
            'typeCompanies' => $typeCompanies,
        ]);
    }

    public function insert(Request $request)
    {
        // Uncomment the validation if needed
        // $request->validate([
        //     'company_code' => 'required',
        //     'company_name' => 'required',
        //     'address' => 'required',
        //     'phone_number' => 'required',
        //     'npwp' => 'required',
        //     'pkp' => 'required', // Ensure pkp is required
        //     'final_tax' => 'required|numeric', // Validate final_tax as required and numeric
        //     'type_company' => 'required',
        // ]);
        DB::beginTransaction();  // Begin the transaction
        try {
            if (Company::where('company_code', $request->company_code)->count() < 1) {
                Company::create([
                    'company_code' => $request->company_code,
                    'company_name' => $request->company_name,
                    'address' => $request->address,
                    'phone_number' => $request->phone_number,
                    'npwp' => $request->npwp,
                    'pkp' => $request->pkp, // Include pkp when creating a company
                    'final_tax' => $request->final_tax, // Include final_tax when creating a company
                    'type_company' => $request->type_company,
                    'created_by' => Auth::user()->username,
                    'updated_by' => Auth::user()->username,
                ]);

                DB::commit();  // Commit the transaction

                return redirect()->back()->with('success', 'Company added successfully!');
            } else {
                return redirect()->back()->with('error', 'Company code must not be the same');
            }



        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }


    }

    public function update(Request $request, $id)
    {
        // Uncomment the validation if needed
        // $request->validate([
        //     'company_code' => 'required',
        //     'company_name' => 'required',
        //     'address' => 'required',
        //     'phone_number' => 'required',
        //     'npwp' => 'required',
        //     'pkp' => 'required', // Ensure pkp is required
        //     'final_tax' => 'required|numeric', // Validate final_tax as required and numeric
        //     'type_company' => 'required',
        // ]);
        DB::beginTransaction();  // Begin the transaction
        try {
            $company = Company::where('company_code', $id)->update([
                'company_code' => $request->company_code,
                'company_name' => $request->company_name,
                'address' => $request->address,
                'phone_number' => $request->phone_number,
                'npwp' => $request->npwp,
                'pkp' => $request->pkp, // Include pkp when updating a company
                'final_tax' => $request->final_tax, // Include final_tax when updating a company
                'type_company' => $request->type_company,
                'updated_by' => Auth::user()->username, // Update the user who made the change
            ]);



                DB::commit();  // Commit the transaction

                return redirect()->back()->with('success', 'Company updated successfully!');
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
            $company = Company::where('company_code',$id);
            $company->delete();

            DB::commit();  // Commit the transaction
            return redirect()->back()->with('success', 'Company deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }
}
