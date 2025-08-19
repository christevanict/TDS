<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use App\Models\AssetType;
use App\Models\Company;
use App\Models\Department;
use App\Models\Coa;
use App\Models\Depreciation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssetTypeController extends Controller
{
    public function index()
    {
        $assetTypes = AssetType::orderBy('asset_type_code', 'asc')->get();
        $coas = Coa::whereRelation('coasss','account_sub_type','!=','PM')->orderBy('account_number', 'asc')->get();
        $depreciations = Depreciation::orderBy('id', 'asc')->get();
        return view('master.asset-type', [
            'coas' => $coas,
            'assetTypes' => $assetTypes,
            'depreciations' => $depreciations,
        ]);
    }

    public function insert(Request $request)
    {
        DB::beginTransaction();
        try {
            // Uncomment the validation if needed
            $request->validate([
                'asset_type_code' => 'required',
                'asset_type_name' => 'required',
                'depreciation_code' => 'required',
                'economic_life' => 'required',
                'tariff_depreciation'=> 'required',
                'acc_number_asset'=> 'required',
                'acc_number_akum_depreciation'=> 'required',
                'acc_number_depreciation'=> 'required',
            ]);

            $company = Company::first();

            if (AssetType::where('asset_type_code', $request->asset_type_code)->count() < 1) {
                AssetType::create([
                    'asset_type_code' => $request->asset_type_code,
                    'asset_type_name' => $request->asset_type_name,
                    'depreciation_code' => $request->depreciation_code,
                    'economic_life' => $request->economic_life,
                    'tariff_depreciation' => $request->tariff_depreciation,
                    'acc_number_asset' => $request->acc_number_asset,
                    'acc_number_akum_depreciation' => $request->acc_number_akum_depreciation,
                    'acc_number_depreciation' => $request->acc_number_depreciation,
                    'company_code' => $company->company_code,
                    'department_code' => 'DP01',
                    'created_by' => Auth::user()->username,
                    'updated_by' => Auth::user()->username,
                ]);
                DB::commit();
                return redirect()->back()->with('success', 'Asset Type added successfully!');
            } else {
                return redirect()->back()->with('error', 'Asset Type code must not be the same');
            }
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try{
            $company = Company::first();
            $assetType = AssetType::where('asset_type_code', $id)->update([
                'asset_type_code' => $request->asset_type_code,
                'asset_type_name' => $request->asset_type_name,
                'economic_life' => $request->economic_life,
                'depreciation_code' => $request->depreciation_code,
                'tariff_depreciation' => $request->tariff_depreciation,
                'acc_number_asset' => $request->acc_number_asset,
                'acc_number_akum_depreciation' => $request->acc_number_akum_depreciation,
                'acc_number_depreciation' => $request->acc_number_depreciation,
                'updated_by' => Auth::user()->username,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Asset Type added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }
    }

    public function delete($id)
    {
        $asset = Asset::where('asset_type',$id)->exists();
        if($asset){
            return redirect()->back()->with('error', 'Sudah digunakan pada asset!');
        }
        $assetType = AssetType::where('asset_type_code',$id);
        $assetType->delete();
        return redirect()->back()->with('success', 'Asset Type deleted successfully!');
    }
}
