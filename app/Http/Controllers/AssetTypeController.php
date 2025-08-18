<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetType;
use App\Models\Company;
use App\Models\Department;
use App\Models\COA;
use Illuminate\Support\Facades\Auth;

class AssetTypeController extends Controller
{
    public function index()
    {
        $assetTypes = AssetType::orderBy('asset_type_code', 'asc')->get();
        $coas = COA::orderBy('account_number', 'asc')->get();
        $companies = Company::orderBy('id', 'asc')->get();
        $departments = Department::orderBy('id', 'asc')->get();
        return view('master.asset-type', [
            'coas' => $coas,
            'assetTypes' => $assetTypes,
            'companies' => $companies,
            'departments' => $departments,
        ]);
    }

    public function insert(Request $request)
    {
        // Uncomment the validation if needed
        $request->validate([
            'asset_type_code' => 'required',
            'asset_type_name' => 'required',
            'economic_life' => 'required',
            'tariff_depreciation'=> 'required',
            'acc_number_asset'=> 'required',
            'acc_number_akum_depreciation'=> 'required',
            'acc_number_depreciation'=> 'required',
            'company_code' => 'required',
            'department_code' => 'required',
        ]);

        if (AssetType::where('asset_type_code', $request->asset_type_code)->count() < 1) {
            AssetType::create([
                'asset_type_code' => $request->asset_type_code,
                'asset_type_name' => $request->asset_type_name,
                'economic_life' => $request->economic_life,
                'tariff_depreciation' => $request->tariff_depreciation,
                'acc_number_asset' => $request->acc_number_asset,
                'acc_number_akum_depreciation' => $request->acc_number_akum_depreciation,
                'acc_number_depreciation' => $request->acc_number_depreciation,
                'company_code' => $request->company_code,
                'department_code' => $request->department_code,
                'created_by' => Auth::user()->username,
                'updated_by' => Auth::user()->username,
            ]);
            return redirect()->back()->with('success', 'Asset Type added successfully!');
        } else {
            return redirect()->back()->with('error', 'Asset Type code must not be the same');
        }
    }

    public function update(Request $request, $id)
    {
        // Uncomment the validation if needed
        $request->validate([
            'asset_type_code' => 'required',
            'asset_type_name' => 'required',
            'economic_life' => 'required',
            'tariff_depreciation'=> 'required',
            'acc_number_asset'=> 'required',
            'acc_number_akum_depreciation'=> 'required',
            'acc_number_depreciation'=> 'required',
            'company_code' => 'required',
            'department_code' => 'required',
        ]);


        $assetType = AssetType::where('asset_type_code', $id)->update([
            'asset_type_code' => $request->asset_type_code,
            'asset_type_name' => $request->asset_type_name,
            'economic_life' => $request->economic_life,
            'tariff_depreciation' => $request->tariff_depreciation,
            'acc_number_asset' => $request->acc_number_asset,
            'acc_number_akum_depreciation' => $request->acc_number_akum_depreciation,
            'acc_number_depreciation' => $request->acc_number_depreciation,
            'company_code' => $request->company_code,
            'department_code' => $request->department_code,
            'created_by' => Auth::user()->username,
            'updated_by' => Auth::user()->username,
        ]);

        return redirect()->back()->with('success', 'Asset Type updated successfully!');
    }

    public function delete($id)
    {
        $assetType = AssetType::where('asset_type_code',$id);
        $assetType->delete();
        return redirect()->back()->with('success', 'Asset Type deleted successfully!');
    }
}
