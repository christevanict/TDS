<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetPurchase;
use App\Models\AssetDetail;
use App\Models\Company;
use App\Models\Debt;
use App\Models\Depreciation;
use App\Models\Journal;
use App\Models\PayablePaymentDetail;
use App\Models\Periode;
use App\Models\Supplier;
use App\Models\TaxMaster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssetPurchaseController extends Controller
{
    public function index()
    {
        $assetPurchases = AssetPurchase::with(['assetDetail', 'depreciation'])->get();
        $assetDetails = AssetDetail::all();
        $depreciations = Depreciation::all();
        $privileges = Auth::user()->roles->privileges['asset_purchase']; // Adjust based on your auth setup

        return view('transaction.asset-purchase.asset_purchase_list', compact('assetPurchases', 'assetDetails', 'depreciations', 'privileges'));
    }

    public function create()
    {
        $assetDetails = AssetDetail::all();
        $asset = Asset::all();
        $suppliers = Supplier::all();
        $tax = TaxMaster::where('tax_code','PPN')->first();
        $depreciations = Depreciation::all();
        $privileges = Auth::user()->roles->privileges['asset_purchase'];

        return view('transaction.asset-purchase.asset_purchase_input', compact('assetDetails','asset','tax', 'depreciations', 'privileges','suppliers'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $assetPurchase = new AssetPurchase();
            $assetPurchase->asset_purchase_number = $this->generateAssetPurchaseNumber($request->document_date);

            $lastAsset = AssetDetail::where('asset_code',$request->asset_code)->orderBy('id','desc')->first();
            $number = $lastAsset?substr($lastAsset->asset_number,-2) : 0;

            $assetPurchase->supplier_code = $request->supplier_code;
            $assetPurchase->asset_number = $request->asset_code.'-'. str_pad($number +1,2,'0',STR_PAD_LEFT);
            $assetPurchase->document_date = $request->document_date;
            $assetPurchase->due_date = $request->due_date;
            $assetPurchase->subtotal = str_replace(',', '', $request->subtotal??0);
            $assetPurchase->add_tax = str_replace(',', '', $request->add_tax??0);
            $assetPurchase->nominal = str_replace(',', '', $request->nominal??0);
            $assetPurchase->created_by = Auth::user()->username;
            $assetPurchase->updated_by = Auth::user()->username;
            $assetPurchase->save();

            $company = Company::first();

            Debt::create([
                'document_number'=>$assetPurchase->asset_purchase_number,
                'document_date'=>$assetPurchase->document_date,
                'due_date'=>$assetPurchase->due_date,
                'supplier_code'=>$assetPurchase->supplier_code,
                'total_debt'=>$assetPurchase->nominal,
                'debt_balance'=>$assetPurchase->nominal,
                'company_code'=>$company->company_code,
                'department_code'=>'DP01',
                'created_by'=>Auth::user()->username,
                'updated_by'=>Auth::user()->username,
            ]);

            $asset = Asset::where('asset_code',$request->asset_code)->with('assetType')->first();

            $totalMonthCount = 0;
            if($asset->assetType->depreciation_code=='YEAR'){
                $totalMonthCount = ($asset->assetType->economic_life) *12;
            }else{
                $totalMonthCount = (100/$asset->assetType->economic_life) *12;
            }

            $lastDate = Carbon::today()
                ->startOfMonth() // Start from the beginning of the current month
                ->addMonthsNoOverflow($totalMonthCount - 1) // Add 19 months (since current month is counted)
                ->endOfMonth();

            AssetDetail::create([
                'asset_code'=>$asset->asset_code,
                'asset_name'=>$asset->asset_name,
                'asset_number'=>$assetPurchase->asset_number,
                'purchase_date'=>$request->document_date,
                'end_economic_life'=>$lastDate,
                'nominal'=>$assetPurchase->nominal,
                'is_sold'=>false,
                'company_code'=>$company->company_code,
                'department_code'=>'DP01',
                'created_by'=>Auth::user()->username,
                'updated_by'=>Auth::user()->username,
            ]);

            //Inventaris
            $supplier = Supplier::where('supplier_code',$assetPurchase->supplier_code)->first();
            $tax = TaxMaster::where('tax_code','PPN')->first();
            Journal::create([
                'document_number'=>$assetPurchase->asset_purchase_number,
                'document_date'=>$assetPurchase->document_date,
                'account_number'=>$asset->assetType->acc_number_asset,
                'debet_nominal'=>$assetPurchase->subtotal,
                'credit_nominal'=>0,
                'notes'=>$supplier->supplier_name.' Pembelian Asset '.$asset->asset_name,
                'company_code'=>$company->company_code,
                'department_code'=>'DP01',
                'created_by'=>Auth::user()->username,
                'updated_by'=>Auth::user()->username,
            ]);


            //Pajak
            Journal::create([
                'document_number'=>$assetPurchase->asset_purchase_number,
                'document_date'=>$assetPurchase->document_date,
                'account_number'=>$tax->account_number,
                'debet_nominal'=>$assetPurchase->add_tax,
                'credit_nominal'=>0,
                'notes'=>$supplier->supplier_name.' Pajak Pembelian Asset '.$asset->asset_name,
                'company_code'=>$company->company_code,
                'department_code'=>'DP01',
                'created_by'=>Auth::user()->username,
                'updated_by'=>Auth::user()->username,
            ]);

            //HUTANG
            Journal::create([
                'document_number'=>$assetPurchase->asset_purchase_number,
                'document_date'=>$assetPurchase->document_date,
                'account_number'=>$supplier->account_payable,
                'debet_nominal'=>0,
                'credit_nominal'=>$assetPurchase->nominal,
                'notes'=>$supplier->supplier_name.' Hutang Pembelian Asset '.$asset->asset_name,
                'company_code'=>$company->company_code,
                'department_code'=>'DP01',
                'created_by'=>Auth::user()->username,
                'updated_by'=>Auth::user()->username,
            ]);


            $currentMonth = Carbon::parse($assetPurchase->document_date)->startOfMonth();
            for ($month = 0; $month < $totalMonthCount; $month++) {
                // Calculate the last day of the current month
                $documentDate = $currentMonth->copy()
                    ->addMonthsNoOverflow($month)
                    ->endOfMonth();

                $bulan = $this->getBulanString($documentDate);
                //Akumulasi Penyusutan
                Journal::create([
                    'document_number'=>$assetPurchase->asset_purchase_number,
                    'document_date'=>$documentDate,
                    'account_number'=>$asset->assetType->acc_number_akum_depreciation,
                    'debet_nominal'=>0,
                    'credit_nominal'=>$assetPurchase->nominal/(int)$totalMonthCount,
                    'notes'=>'Penyusutan bulan '.$bulan.' Aktiva tetap'.$asset->asset_code,
                    'company_code'=>$company->company_code,
                    'department_code'=>'DP01',
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
                ]);

                //Akumulasi Penyusutan
                Journal::create([
                    'document_number'=>$assetPurchase->asset_purchase_number,
                    'document_date'=>$documentDate,
                    'account_number'=>$asset->assetType->acc_number_depreciation,
                    'debet_nominal'=>$assetPurchase->nominal/(int)$totalMonthCount,
                    'credit_nominal'=>0,
                    'notes'=>'Penyusutan bulan '.$bulan.' Aktiva tetap'.$asset->asset_code,
                    'company_code'=>$company->company_code,
                    'department_code'=>'DP01',
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
                ]);

            }

            DB::commit();
            return redirect()->route('asset-purchase.index')->with('success', 'Asset Purchase created successfully.')->with('id', AssetPurchase::latest()->first()->id);
        } catch (\Exception $e) {
            DB::rollback();
            // dd($e);
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to create asset purchase: ' . $e->getMessage());
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

    public function edit($id)
    {
        $assetPurchase = AssetPurchase::findOrFail($id);
        $assetDetails = Asset::all();
        $tax = TaxMaster::where('tax_code','PPN')->first();
        $privileges = Auth::user()->roles->privileges['asset_purchase'];
        $editable = true;
        $payable = PayablePaymentDetail::where('document_number',$assetPurchase->asset_purchase_number)->exists();
        $periodeClosed = Periode::where('periode_active', 'closed')
            ->where('periode_start', '<=', $assetPurchase->document_date)
            ->where('periode_end', '>=', $assetPurchase->document_date)
            ->first();
        $note='';
        if($payable){
            $note.='Sudah dibayar <br>';
            $editable = false;
        }
        if($periodeClosed){
            $note.='Sudah di Closing <br>';
            $editable = false;
        }
         // Add logic to determine editability (e.g., not closed)

        return view('transaction.asset-purchase.asset_purchase_edit', compact('assetPurchase', 'assetDetails', 'privileges', 'editable','tax','note'));
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $assetPurchase = AssetPurchase::findOrFail($id);
            $assetPurchase->update([
                'document_date' => $request->document_date,
                'due_date' => $request->due_date,
                'subtotal' => str_replace(',', '', $request->subtotal??0),
                'add_tax' => str_replace(',', '', $request->add_tax??0),
                'nominal' => str_replace(',', '', $request->nominal??0),
                'updated_by' => Auth::user()->username,
            ]);

            $oldAssetNumber = $assetPurchase->asset_number;
            if(substr($assetPurchase->asset_number,0,-3)!=$request->asset_code){
                $lastAsset = AssetDetail::where('asset_code',$request->asset_code)->orderBy('id','desc')->first();
                $number = $lastAsset?substr($lastAsset->asset_number,-2) : 0;
                $assetPurchase->asset_number = $request->asset_code.'-'. str_pad($number +1,2,'0',STR_PAD_LEFT);
                $assetPurchase->save();
            }
            Debt::where('document_number',$assetPurchase->asset_purchase_number)->delete();
            Journal::where('document_number',$assetPurchase->asset_purchase_number)->delete();
            AssetDetail::where('asset_number',$oldAssetNumber)->delete();

            $company = Company::first();

            Debt::create([
                'document_number'=>$assetPurchase->asset_purchase_number,
                'document_date'=>$assetPurchase->document_date,
                'due_date'=>$assetPurchase->due_date,
                'supplier_code'=>$assetPurchase->supplier_code,
                'total_debt'=>$assetPurchase->nominal,
                'debt_balance'=>$assetPurchase->nominal,
                'company_code'=>$company->company_code,
                'department_code'=>'DP01',
                'created_by'=>Auth::user()->username,
                'updated_by'=>Auth::user()->username,
            ]);

            $asset = Asset::where('asset_code',$request->asset_code)->with('assetType')->first();

            $totalMonthCount = 0;
            if($asset->assetType->depreciation_code=='YEAR'){
                $totalMonthCount = ($asset->assetType->economic_life) *12;
            }else{
                $totalMonthCount = (100/$asset->assetType->economic_life) *12;
            }

            $lastDate = Carbon::today()
                ->startOfMonth() // Start from the beginning of the current month
                ->addMonthsNoOverflow($totalMonthCount - 1) // Add 19 months (since current month is counted)
                ->endOfMonth();

            AssetDetail::create([
                'asset_code'=>$asset->asset_code,
                'asset_name'=>$asset->asset_name,
                'asset_number'=>$assetPurchase->asset_number,
                'purchase_date'=>$request->document_date,
                'end_economic_life'=>$lastDate,
                'nominal'=>$assetPurchase->nominal,
                'is_sold'=>false,
                'company_code'=>$company->company_code,
                'department_code'=>'DP01',
                'created_by'=>Auth::user()->username,
                'updated_by'=>Auth::user()->username,
            ]);

            $supplier = Supplier::where('supplier_code',$assetPurchase->supplier_code)->first();
            $tax = TaxMaster::where('tax_code','PPN')->first();

            //Inventaris
            Journal::create([
                'document_number'=>$assetPurchase->asset_purchase_number,
                'document_date'=>$assetPurchase->document_date,
                'account_number'=>$asset->assetType->acc_number_asset,
                'debet_nominal'=>$assetPurchase->subtotal,
                'credit_nominal'=>0,
                'notes'=>$supplier->supplier_name.' Pembelian Asset '.$asset->asset_name,
                'company_code'=>$company->company_code,
                'department_code'=>'DP01',
                'created_by'=>Auth::user()->username,
                'updated_by'=>Auth::user()->username,
            ]);

            //Pajak
            Journal::create([
                'document_number'=>$assetPurchase->asset_purchase_number,
                'document_date'=>$assetPurchase->document_date,
                'account_number'=>$tax->account_number,
                'debet_nominal'=>$assetPurchase->add_tax,
                'credit_nominal'=>0,
                'notes'=>$supplier->supplier_name.' Pajak Pembelian Asset '.$asset->asset_name,
                'company_code'=>$company->company_code,
                'department_code'=>'DP01',
                'created_by'=>Auth::user()->username,
                'updated_by'=>Auth::user()->username,
            ]);

            //HUTANG
            Journal::create([
                'document_number'=>$assetPurchase->asset_purchase_number,
                'document_date'=>$assetPurchase->document_date,
                'account_number'=>$supplier->account_payable,
                'debet_nominal'=>0,
                'credit_nominal'=>$assetPurchase->nominal,
                'notes'=>$supplier->supplier_name.' Hutang Pembelian Asset '.$asset->asset_name,
                'company_code'=>$company->company_code,
                'department_code'=>'DP01',
                'created_by'=>Auth::user()->username,
                'updated_by'=>Auth::user()->username,
            ]);

            $currentMonth = Carbon::parse($assetPurchase->document_date)->startOfMonth();
            for ($month = 0; $month < $totalMonthCount; $month++) {
                // Calculate the last day of the current month
                $documentDate = $currentMonth->copy()
                    ->addMonthsNoOverflow($month)
                    ->endOfMonth();

                $bulan = $this->getBulanString($documentDate);
                //Akumulasi Penyusutan
                Journal::create([
                    'document_number'=>$assetPurchase->asset_purchase_number,
                    'document_date'=>$documentDate,
                    'account_number'=>$asset->assetType->acc_number_akum_depreciation,
                    'debet_nominal'=>0,
                    'credit_nominal'=>$assetPurchase->nominal/(int)$totalMonthCount,
                    'notes'=>'Penyusutan bulan '.$bulan.' Aktiva tetap'.$asset->asset_code,
                    'company_code'=>$company->company_code,
                    'department_code'=>'DP01',
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
                ]);

                //Akumulasi Penyusutan
                Journal::create([
                    'document_number'=>$assetPurchase->asset_purchase_number,
                    'document_date'=>$documentDate,
                    'account_number'=>$asset->assetType->acc_number_depreciation,
                    'debet_nominal'=>$assetPurchase->nominal/(int)$totalMonthCount,
                    'credit_nominal'=>0,
                    'notes'=>'Penyusutan bulan '.$bulan.' Aktiva tetap'.$asset->asset_code,
                    'company_code'=>$company->company_code,
                    'department_code'=>'DP01',
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
                ]);
            }

            DB::commit();
            return redirect()->route('asset-purchase.index')->with('success', 'Asset Purchase updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to update asset purchase: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {

        DB::beginTransaction();
        try {
            $assetPurchase = AssetPurchase::findOrFail($id);
            $assetDetails =AssetDetail::where('asset_number',$assetPurchase->asset_number)->first();
            if($assetDetails->is_sold){
                return redirect()->back()->with('error','Tidak dapat menghapus asset yang sudah dijual');
            }
            Debt::where('document_number',$assetPurchase->asset_purchase_number)->delete();
            Journal::where('document_number',$assetPurchase->asset_purchase_number)->delete();
            AssetDetail::where('asset_number',$assetPurchase->asset_number)->delete();

            $assetPurchase->delete();
            DB::commit();
            return redirect()->route('asset-purchase.index')->with('success', 'Asset Purchase deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to delete asset purchase: ' . $e->getMessage());
        }
    }

    public function print($id)
    {
        $assetPurchase = AssetPurchase::findOrFail($id);
        // Implement print logic (e.g., generate PDF)
        return view('transaction.asset-purchase.print', compact('assetPurchase'));
    }

    private function generateAssetPurchaseNumber($date) {
        // Get today's date components
        $today = Carbon::parse($date);
        $month = $today->format('n'); // Numeric representation of a month (1-12)
        $year = $today->format('y'); // Last two digits of the year

        // Convert month to Roman numeral
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        $romanMonth = $romanMonths[$month];
        $prefix = "TDS/PIA/{$romanMonth}/{$year}-";

        // Fetch the last sales invoice created
        $lastSalesInvoice = AssetPurchase::whereRaw('SUBSTRING(asset_purchase_number, 1, ?) = ?', [strlen($prefix), $prefix])
            ->orderBy('asset_purchase_number', 'desc')
            ->first();
            // dd($lastSalesInvoice);

        // Determine the new invoice number
        if ($lastSalesInvoice) {
            // Extract the last number from the last invoice number
            $lastNumber = (int)substr($lastSalesInvoice->asset_purchase_number, -5);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            // Reset counter to 00001 if no invoices found for the current month
            $newNumber = '00001';
        }

        return "$prefix$newNumber";
    }
}
