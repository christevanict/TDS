<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetDetail;
use App\Models\Company;
use App\Models\AssetType;
use App\Models\Department;
use App\Models\Journal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Rap2hpoutre\FastExcel\FastExcel;

class AssetController extends Controller
{
    public function index()
    {
        $assets = Asset::with(['company', 'department', 'assetType'])->get();
        $assetTypes = AssetType::all();
        $privileges = Auth::user()->roles->privileges['master_asset'];

        return view('master.asset', compact('assets', 'assetTypes', 'privileges'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $company = Company::first();
            Asset::create([
                'asset_code' => $request->asset_code,
                'asset_name' => $request->asset_name,
                'asset_type' => $request->asset_type,
                'company_code' => $company->company_code,
                'department_code' => 'DP01',
                'created_by' => Auth::user()->username,
                'updated_by' => Auth::user()->username,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Asset Type added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $asset = Asset::findOrFail($id);
            $assetCode = $asset->asset_code;
            $asset->update([
                'asset_code' => $request->asset_code,
                'asset_name' => $request->asset_name,
                'asset_type' => $request->asset_type,
                'updated_by' => Auth::user()->username,
            ]);

            return redirect()->route('master.assets.index')->with('success', 'Asset updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update asset: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $asset = Asset::findOrFail($id);
            $assetDetail = AssetDetail::where('asset_code',$asset->asset_code)->exists();
            if($assetDetail){
                return redirect()->back()->with('error','Sudah ada pembelian atau penjualan asset');
            }
            $asset->delete();

            return redirect()->route('master.assets.index')->with('success', 'Asset deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete asset: ' . $e->getMessage());
        }
    }

    function getBulanString($documentDate)
    {
        // Parse the document_date
        $date = Carbon::parse($documentDate);

        // Set locale to Indonesian for month names
        Carbon::setLocale('id');

        // Format the date to get 'Februari 2025'
        $bulan = $date->translatedFormat('F Y');

        return $bulan;
    }

    public function importExcel(Request $request)
    {
        if ($request->hasFile('importFile')) {
            $file = $request->file('importFile');
            $companyCode = Company::first()->company_code;  // Automatically get the company code

            DB::beginTransaction();
            try {
                (new FastExcel)->import($file, function ($row) use ($companyCode) {
                    //Asset
                    Asset::create([
                        'asset_code' => $row['Kode Aktiva'],
                        'asset_name' => $row['Nama Aktiva'],
                        'asset_type' => $row['Nama Tipe Aktiva'],
                        'department_code' => 'DP01',
                        'company_code' => $companyCode,
                        'created_by' => Auth::user()->username,
                        'updated_by' => Auth::user()->username,
                    ]);

                    $totalMonthCount = $row['Estimasi Umur'] *12;
                    $endDate = Carbon::parse($row['Tanggal Beli'])
                        ->startOfMonth() // Start from the beginning of the current month
                    ->addMonthsNoOverflow($totalMonthCount - 1) // Add 19 months (since current month is counted)
                    ->endOfMonth();

                    //Asset Detail
                    AssetDetail::create([
                        'asset_code' => $row['Kode Aktiva'],
                        'asset_name' => $row['Nama Aktiva'],
                        'asset_number' => $row['Kode Aktiva'].'-01',
                        'purchase_date' => $row['Tanggal Beli'],
                        'end_economic_life' => $endDate,
                        'nominal' => $row['Harga Perolehan'],
                        'is_sold' => false,
                        'department_code' => 'DP01',
                        'company_code' => $companyCode,
                        'created_by' => Auth::user()->username,
                        'updated_by' => Auth::user()->username,
                    ]);
                    $startDate = Carbon::create(2025, 1, 1)->startOfMonth(); // Start from January 1, 2025
                    $currentMonth = $startDate;
                    $month = 0;
                    if($endDate>now()){
                        $currentMonth = $startDate;
                        while ($currentMonth <= $endDate) {
                            // Calculate the last day of the current month
                            $documentDate = $currentMonth->copy()->endOfMonth();
                            $currentMonth->addMonthNoOverflow();
                            $bulan = $this->getBulanString($documentDate);
                            //Akumulasi Penyusutan
                            Journal::create([
                                'document_number'=>'DEP/'.substr($row['Kode Aktiva'],2,2),
                                'document_date'=>$documentDate,
                                'account_number'=>$row['Kode Akun Penyusutan'],
                                'debet_nominal'=>0,
                                'credit_nominal'=>$row['Harga Perolehan']/(int)$totalMonthCount,
                                'notes'=>'Penyusutan bulan '.$bulan.' Aktiva tetap'.$row['Kode Aktiva'],
                                'company_code' => $companyCode,
                                'department_code'=>'DP01',
                                'created_by'=>Auth::user()->username,
                                'updated_by'=>Auth::user()->username,
                            ]);
                            //Akumulasi Penyusutan
                            Journal::create([
                                'document_number'=>'DEP/'.substr($row['Kode Aktiva'],2,2),
                                'document_date'=>$documentDate,
                                'account_number'=>$row['Biaya Penyusutan'],
                                'debet_nominal'=>$row['Harga Perolehan']/(int)$totalMonthCount,
                                'credit_nominal'=>0,
                                'notes'=>'Penyusutan bulan '.$bulan.' Aktiva tetap'.$row['Kode Aktiva'],
                                'company_code' => $companyCode,
                                'department_code'=>'DP01',
                                'created_by'=>Auth::user()->username,
                                'updated_by'=>Auth::user()->username,
                            ]);
                        }
                    }
                });
                DB::commit();

                return redirect()->back()->with('success', 'Data imported successfully!');
            } catch (\Exception $e) {
                DB::rollBack();
                // dd($e);
                Log::error($e->getMessage());
                return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('error', 'No file uploaded.');
    }
}
