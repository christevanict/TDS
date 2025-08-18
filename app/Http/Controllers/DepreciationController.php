<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Depreciation;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;

class DepreciationMethodController extends Controller
{
    public function index()
    {
        $depreciations = Depreciation::orderBy('depreciation_code', 'asc')->get();
        $companies = Company::orderBy('id', 'asc')->get();
        $departments = Department::orderBy('id', 'asc')->get();
        return view('master.depreciation', [
            'depreciations' => $depreciations,
            'companies' => $companies,
            'departments' => $departments,
        ]);
    }

    public function insert(Request $request)
    {
        // Uncomment the validation if needed
        $request->validate([
            'depreciation_code' => 'required',
            'depreciation_name' => 'required',
            'company_code' => 'required',
            'department_code' => 'required',
        ]);

        if (Depreciation::where('depreciation_code', $request->depreciation_code)->count() < 1) {
            Depreciation::create([
                'depreciation_code' => $request->depreciation_code,
                'depreciation_name' => $request->depreciation_name,
                'company_code' => $request->company_code,
                'department_code' => $request->department_code,
                'created_by' => Auth::user()->username,
                'updated_by' => Auth::user()->username,
            ]);
            return redirect()->back()->with('success', 'Depreciation Method added successfully!');
        } else {
            return redirect()->back()->with('error', 'Depreciation Method code must not be the same');
        }
    }

    public function update(Request $request, $id)
    {
        // Uncomment the validation if needed
        $request->validate([
            'depreciation_code' => 'required',
            'depreciation_name' => 'required',
            'company_code' => 'required',
            'department_code' => 'required',
        ]);

        $depreciation = Depreciation::where('depreciation_code', $id)->update([
            'depreciation_name' => $request->depreciation_code,
            'depreciation_name' => $request->depreciation_name,
            'company_code' => $request->company_code,
            'department_code' => $request->department_code,
            'created_by' => Auth::user()->username,
            'updated_by' => Auth::user()->username,
        ]);

        return redirect()->back()->with('success', 'Depreciation Method updated successfully!');
    }

    public function delete($id)
    {
        $depreciation = Depreciation::where('depreciation_code',$id);
        $depreciation->delete();
        return redirect()->back()->with('success', 'Depreciation Method deleted successfully!');
    }
}