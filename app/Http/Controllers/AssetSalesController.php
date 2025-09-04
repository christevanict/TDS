<?php

namespace App\Http\Controllers;

use App\Models\AssetSales;
use App\Models\AssetDetail;
use App\Models\AssetPurchase;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Depreciation;
use App\Models\Journal;
use App\Models\Periode;
use App\Models\Receivable;
use App\Models\ReceivablePaymentDetail;
use App\Models\TaxMaster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssetSalesController extends Controller
{
    public function index()
    {
        $assetSales = AssetSales::with(['assetDetail', 'depreciation'])->get();
        $assetDetails = AssetDetail::all();
        $depreciations = Depreciation::all();
        $privileges = Auth::user()->roles->privileges['asset_sales'];

        return view('transaction.asset-sales.asset_sales_list', compact('assetSales', 'assetDetails', 'depreciations', 'privileges'));
    }

    public function create()
    {
        $assetDetails = AssetDetail::where('is_sold',false)->with('asset')->get();
        $customers = Customer::all();
        $tax = TaxMaster::where('tax_code','PPN')->first();
        $privileges = Auth::user()->roles->privileges['asset_sales'];

        return view('transaction.asset-sales.asset_sales_input', compact('assetDetails', 'customers', 'privileges', 'tax'));
    }

    public function store(Request $request)
    {
        $periodeClosed = Periode::where('periode_active', 'closed')
            ->where('periode_start', '<=', $request->document_date)
            ->where('periode_end', '>=', $request->document_date)
            ->first();
        if($periodeClosed){
            return redirect()->back()->with('error','Tanggal dokumen tidak valid, Periode sudah diclosing');
        }
        DB::beginTransaction();
        try {
            $assetSales = new AssetSales();
            $assetSales->asset_sales_number = $this->generateAssetSalesNumber($request->document_date);
            $assetSales->asset_number = $request->asset_number;
            $assetSales->document_date = $request->document_date;
            $assetSales->due_date = $request->due_date;
            $assetSales->customer_code = $request->customer_code;
            $assetSales->subtotal = str_replace(',', '', $request->subtotal??0);
            $assetSales->add_tax = str_replace(',', '', $request->add_tax??0);
            $assetSales->nominal = str_replace(',', '', $request->nominal??0);
            $assetSales->created_by = Auth::user()->username;
            $assetSales->updated_by = Auth::user()->username;
            // $assetSales->save();

            $company = Company::first();

            Receivable::create([
                'document_number'=>$assetSales->asset_sales_number,
                'document_date'=>$assetSales->document_date,
                'due_date'=>$assetSales->due_date,
                'customer_code'=>$assetSales->customer_code,
                'total_debt'=>$assetSales->nominal,
                'debt_balance'=>$assetSales->nominal,
                'company_code'=>$company->company_code,
                'department_code'=>'DP01',
                'created_by'=>Auth::user()->username,
                'updated_by'=>Auth::user()->username,
            ]);

            $assetDetail = AssetDetail::where('asset_number',$assetSales->asset_number)->first();
            $assetDetail->is_sold = true;
            $assetDetail->save();

            $assetPurchase = AssetPurchase::where('asset_number',$assetSales->asset_number)->first();

            $customer = Customer::where('customer_code',$assetSales->customer_code)->first();
            $tax = TaxMaster::where('tax_code','PPN')->first();

            //Piutang
            Journal::create([
                'document_number'=>$assetSales->asset_sales_number,
                'document_date'=>$assetSales->document_date,
                'account_number'=>$customer->account_receivable,
                'debet_nominal'=>$assetSales->nominal,
                'credit_nominal'=>0,
                'notes'=>$customer->customer_name.' Penjualan Asset '.$assetDetail->asset->asset_name,
                'company_code'=>$company->company_code,
                'department_code'=>'DP01',
                'created_by'=>Auth::user()->username,
                'updated_by'=>Auth::user()->username,
            ]);

            //Pajak
            Journal::create([
                'document_number'=>$assetSales->asset_sales_number,
                'document_date'=>$assetSales->document_date,
                'account_number'=>$tax->account_number,
                'debet_nominal'=>0,
                'credit_nominal'=>$assetSales->add_tax,
                'notes'=>$customer->customer_name.' Pajak Penjualan Asset '.$assetDetail->asset->asset_name,
                'company_code'=>$company->company_code,
                'department_code'=>'DP01',
                'created_by'=>Auth::user()->username,
                'updated_by'=>Auth::user()->username,
            ]);

            $journalAccum = null;
            if($assetPurchase){
                 $journalAccum = Journal::
                where('document_number',$assetPurchase->asset_purchase_number)
                ->where('account_number',$assetDetail->asset->assetType->acc_number_akum_depreciation)
                ->whereDate('document_date','<',$assetSales->document_date)->first();
            }

            $accumValue = 0;
            if($journalAccum){
                $accumValue = $journalAccum->credit_nominal;
            }
            $assetSales->accum_value = $accumValue;
            $assetSales->save();

            $totalAkum = $assetDetail->nominal;
            if($assetPurchase){
                $totalAkum = Journal::
                where('document_number',$assetPurchase->asset_purchase_number)
                ->where('account_number',$assetDetail->asset->assetType->acc_number_akum_depreciation)
                ->whereDate('document_date','<',$assetSales->document_date)
                // ->get();
                ->sum('credit_nominal');
            }

            //Akum Penyusutan
            Journal::create([
                'document_number'=>$assetSales->asset_sales_number,
                'document_date'=>$assetSales->document_date,
                'account_number'=>$assetDetail->asset->assetType->acc_number_akum_depreciation,
                'debet_nominal'=>$totalAkum,
                'credit_nominal'=>0,
                'notes'=>'Penjualan Asset '.$assetDetail->asset->asset_name,
                'company_code'=>$company->company_code,
                'department_code'=>'DP01',
                'created_by'=>Auth::user()->username,
                'updated_by'=>Auth::user()->username,
            ]);

            //Inventaris
            Journal::create([
                'document_number'=>$assetSales->asset_sales_number,
                'document_date'=>$assetSales->document_date,
                'account_number'=>$assetDetail->asset->assetType->acc_number_asset,
                'debet_nominal'=>0,
                'credit_nominal'=>$assetDetail->nominal,
                'notes'=>'Penjualan Asset '.$assetDetail->asset->asset_name,
                'company_code'=>$company->company_code,
                'department_code'=>'DP01',
                'created_by'=>Auth::user()->username,
                'updated_by'=>Auth::user()->username,
            ]);

            $selisih = ($assetDetail->nominal + $assetSales->add_tax) - ($assetSales->nominal +$totalAkum);
            $debetNominal = 0;
            $creditNominal = 0;
            if($selisih>0){
                $debetNominal = $selisih;
            }else if($selisih<0){
                $creditNominal = abs($selisih);
            }
            //Pendapatan lain-lain
            if($debetNominal!=0 || $creditNominal!=0){
                Journal::create([
                    'document_number'=>$assetSales->asset_sales_number,
                    'document_date'=>$assetSales->document_date,
                    'account_number'=>'7100099',
                    'debet_nominal'=>$debetNominal,
                    'credit_nominal'=>$creditNominal,
                    'notes'=>'Penjualan Asset '.$assetDetail->asset->asset_name,
                    'company_code'=>$company->company_code,
                    'department_code'=>'DP01',
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
                ]);

            }

            //Delete journal accum penyusutan kedepan
            if($assetPurchase){
                Journal::where('document_number',$assetPurchase->asset_purchase_number)->whereDate('document_date','>',$assetSales->document_date)->delete();
            }

            DB::commit();
            return redirect()->route('asset-sales.index')->with('success', 'Asset Sale created successfully.')->with('id', AssetSales::latest()->first()->id);
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to create asset sale: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $assetSale = AssetSales::findOrFail($id);
        $is_tax = Customer::where('customer_code',$assetSale->customer_code)->first()->pkp;
        $assetDetails = AssetDetail::all();
        $depreciations = Depreciation::all();
        $tax = TaxMaster::where('tax_code','PPN')->first();
        $privileges = Auth::user()->roles->privileges['asset_sales'];
        $editable = true; // Add logic to determine editability
        $payable = ReceivablePaymentDetail::where('document_number',$assetSale->asset_sales_number)->exists();
        $periodeClosed = Periode::where('periode_active', 'closed')
            ->where('periode_start', '<=', $assetSale->document_date)
            ->where('periode_end', '>=', $assetSale->document_date)
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

        return view('transaction.asset-sales.asset_sales_edit', compact('assetSale', 'assetDetails', 'depreciations', 'privileges', 'editable','tax','note','is_tax'));
    }

    public function update(Request $request, $id)
    {
        $periodeClosed = Periode::where('periode_active', 'closed')
            ->where('periode_start', '<=', $request->document_date)
            ->where('periode_end', '>=', $request->document_date)
            ->first();
        if($periodeClosed){
            return redirect()->back()->with('error','Tanggal dokumen tidak valid, Periode sudah diclosing');
        }
        DB::beginTransaction();
        DB::connection()->beginTransaction();
        try {
            $assetSales = AssetSales::findOrFail($id);
            $oldAssetSales =$assetSales;
            $assetSales->update([
                'asset_number' => $request->asset_number,
                'document_date' => $request->document_date,
                'due_date' => $request->due_date,
                'depreciation_code' => $request->depreciation_code,
                'subtotal' => str_replace(',', '', $request->subtotal??0),
                'add_tax' => str_replace(',', '', $request->add_tax??0),
                'nominal' => str_replace(',', '', $request->nominal??0),
                'updated_by' => Auth::user()->username,
            ]);
            $assetSales->save();

            Receivable::where('document_number',$assetSales->asset_sales_number)->update([
                'total_debt'=>$assetSales->nominal,
                'debt_balance'=>$assetSales->nominal,
                'document_date'=>$assetSales->document_date,
                'due_date'=>$assetSales->due_date,
            ]);

            $assetDetail = AssetDetail::where('asset_number',$oldAssetSales->asset_number)->first();

            if($oldAssetSales->asset_number!=$assetSales->asset_number){
                $assetDetail->is_sold = false;
                $assetDetail->save();
            }
            $company = Company::first();

            $start = Carbon::parse($oldAssetSales->document_date);
            $end = Carbon::parse($assetDetail->end_economic_life);

            //Retrieve journal accum
            $assetPurchase = AssetPurchase::where('asset_number',$oldAssetSales->asset_number)->first();
            $monthDiff = $start->diffInMonths($end);

            $currentMonth = Carbon::parse($oldAssetSales->document_date)->startOfMonth();
            for ($month = 0; $month < $monthDiff; $month++) {
                // Calculate the last day of the current month
                $documentDate = $currentMonth->copy()
                    ->addMonthsNoOverflow($month)
                    ->endOfMonth();

                $bulan = $this->getBulanString($documentDate);
                //Akumulasi Penyusutan
                Journal::create([
                    'document_number'=>$assetPurchase->asset_purchase_number,
                    'document_date'=>$documentDate,
                    'account_number'=>$assetDetail->asset->assetType->acc_number_akum_depreciation,
                    'debet_nominal'=>0,
                    'credit_nominal'=>$assetSales->accum_value,
                    'notes'=>'Penyusutan bulan '.$bulan.' Aktiva tetap'.$assetDetail->asset->asset_code,
                    'company_code'=>$company->company_code,
                    'department_code'=>'DP01',
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
                ]);

                //Akumulasi Penyusutan
                Journal::create([
                    'document_number'=>$assetPurchase->asset_purchase_number,
                    'document_date'=>$documentDate,
                    'account_number'=>$assetDetail->asset->assetType->acc_number_depreciation,
                    'debet_nominal'=>$assetSales->accum_value,
                    'credit_nominal'=>0,
                    'notes'=>'Penyusutan bulan '.$bulan.' Aktiva tetap'.$assetDetail->asset->asset_code,
                    'company_code'=>$company->company_code,
                    'department_code'=>'DP01',
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
                ]);

            }

            Journal::where('document_number',$assetSales->asset_sales_number)->delete();

            $assetPurchase = AssetPurchase::where('asset_number',$assetSales->asset_number)->first();
            $customer = Customer::where('customer_code',$assetSales->customer_code)->first();
            $tax = TaxMaster::where('tax_code','PPN')->first();

            //Piutang
            Journal::create([
                'document_number'=>$assetSales->asset_sales_number,
                'document_date'=>$assetSales->document_date,
                'account_number'=>$customer->account_receivable,
                'debet_nominal'=>$assetSales->nominal,
                'credit_nominal'=>0,
                'notes'=>$customer->customer_name.' Penjualan Asset '.$assetDetail->asset->asset_name,
                'company_code'=>$company->company_code,
                'department_code'=>'DP01',
                'created_by'=>Auth::user()->username,
                'updated_by'=>Auth::user()->username,
            ]);

            //Pajak
            Journal::create([
                'document_number'=>$assetSales->asset_sales_number,
                'document_date'=>$assetSales->document_date,
                'account_number'=>$tax->account_number,
                'debet_nominal'=>0,
                'credit_nominal'=>$assetSales->add_tax,
                'notes'=>$customer->customer_name.' Pajak Penjualan Asset '.$assetDetail->asset->asset_name,
                'company_code'=>$company->company_code,
                'department_code'=>'DP01',
                'created_by'=>Auth::user()->username,
                'updated_by'=>Auth::user()->username,
            ]);

            $journalAccum = Journal::
            where('document_number',$assetPurchase->asset_purchase_number)
            ->where('account_number',$assetDetail->asset->assetType->acc_number_akum_depreciation)
            ->whereDate('document_date','<',$assetSales->document_date)->first();
            $accumValue = 0;
            if($journalAccum){
                $accumValue = $journalAccum->credit_nominal;
            }
            $assetSales->accum_value = $accumValue;
            $assetSales->save();

            $totalAkum = Journal::
            where('document_number',$assetPurchase->asset_purchase_number)
            ->where('account_number',$assetDetail->asset->assetType->acc_number_akum_depreciation)
            ->whereDate('document_date','<',$assetSales->document_date)
            // ->get();
            ->sum('credit_nominal');

            //Akum Penyusutan
            Journal::create([
                'document_number'=>$assetSales->asset_sales_number,
                'document_date'=>$assetSales->document_date,
                'account_number'=>$assetDetail->asset->assetType->acc_number_akum_depreciation,
                'debet_nominal'=>$totalAkum,
                'credit_nominal'=>0,
                'notes'=>'Penjualan Asset '.$assetDetail->asset->asset_name,
                'company_code'=>$company->company_code,
                'department_code'=>'DP01',
                'created_by'=>Auth::user()->username,
                'updated_by'=>Auth::user()->username,
            ]);


            //Inventaris
            Journal::create([
                'document_number'=>$assetSales->asset_sales_number,
                'document_date'=>$assetSales->document_date,
                'account_number'=>$assetDetail->asset->assetType->acc_number_asset,
                'debet_nominal'=>0,
                'credit_nominal'=>$assetPurchase->nominal,
                'notes'=>'Penjualan Asset '.$assetDetail->asset->asset_name,
                'company_code'=>$company->company_code,
                'department_code'=>'DP01',
                'created_by'=>Auth::user()->username,
                'updated_by'=>Auth::user()->username,
            ]);

            $selisih = ($assetPurchase->nominal + $assetSales->add_tax) - ($assetSales->nominal +$totalAkum);
            $debetNominal = 0;
            $creditNominal = 0;
            if($selisih>0){
                $debetNominal = $selisih;
            }else if($selisih<0){
                $creditNominal = abs($selisih);
            }
            //Pendapatan lain-lain
            if($debetNominal!=0 || $creditNominal!=0){
                Journal::create([
                    'document_number'=>$assetSales->asset_sales_number,
                    'document_date'=>$assetSales->document_date,
                    'account_number'=>'7100099',
                    'debet_nominal'=>$debetNominal,
                    'credit_nominal'=>$creditNominal,
                    'notes'=>'Penjualan Asset '.$assetDetail->asset->asset_name,
                    'company_code'=>$company->company_code,
                    'department_code'=>'DP01',
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
                ]);

            }
            //Delete journal accum penyusutan kedepan
            Journal::where('document_number',$assetPurchase->asset_purchase_number)->whereDate('document_date','>',$assetSales->document_date)->delete();

            DB::commit();
            return redirect()->route('asset-sales.index')->with('success', 'Asset Sale updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            // dd($e);
            return redirect()->back()->with('error', 'Failed to update asset sale: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        DB::connection()->beginTransaction();
        try {
            $assetSales = AssetSales::findOrFail($id);

            $assetDetail = AssetDetail::where('asset_number',$assetSales->asset_number)->first();
            $assetDetail->is_sold = false;
            $assetDetail->save();


            $company = Company::first();

            $start = Carbon::parse($assetSales->document_date);
            $end = Carbon::parse($assetDetail->end_economic_life);

            //Retrieve journal accum
            $assetPurchase = AssetPurchase::where('asset_number',$assetSales->asset_number)->first();
            $monthDiff = $start->diffInMonths($end);

            $currentMonth = Carbon::parse($assetSales->document_date)->startOfMonth();
            for ($month = 0; $month < $monthDiff; $month++) {
                // Calculate the last day of the current month
                $documentDate = $currentMonth->copy()
                    ->addMonthsNoOverflow($month)
                    ->endOfMonth();

                $bulan = $this->getBulanString($documentDate);
                //Akumulasi Penyusutan
                Journal::create([
                    'document_number'=>$assetPurchase->asset_purchase_number,
                    'document_date'=>$documentDate,
                    'account_number'=>$assetDetail->asset->assetType->acc_number_akum_depreciation,
                    'debet_nominal'=>0,
                    'credit_nominal'=>$assetSales->accum_value,
                    'notes'=>'Penyusutan bulan '.$bulan.' Aktiva tetap'.$assetDetail->asset->asset_code,
                    'company_code'=>$company->company_code,
                    'department_code'=>'DP01',
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
                ]);

                //Akumulasi Penyusutan
                Journal::create([
                    'document_number'=>$assetPurchase->asset_purchase_number,
                    'document_date'=>$documentDate,
                    'account_number'=>$assetDetail->asset->assetType->acc_number_depreciation,
                    'debet_nominal'=>$assetSales->accum_value,
                    'credit_nominal'=>0,
                    'notes'=>'Penyusutan bulan '.$bulan.' Aktiva tetap'.$assetDetail->asset->asset_code,
                    'company_code'=>$company->company_code,
                    'department_code'=>'DP01',
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
                ]);

            }
            Journal::where('document_number',$assetSales->asset_sales_number)->delete();
            Receivable::where('document_number',$assetSales->asset_sales_number)->delete();

            $assetSales->delete();
            DB::commit();
            return redirect()->route('asset-sales.index')->with('success', 'Asset Sale deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete asset sale: ' . $e->getMessage());
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

    public function print($id)
    {
        $assetSale = AssetSales::findOrFail($id);
        // Implement print logic (e.g., generate PDF)
        return view('transaction.asset-sales.print', compact('assetSale'));
    }

    private function generateAssetSalesNumber($date) {
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
        $prefix = "TDS/SIA/{$romanMonth}/{$year}-";

        // Fetch the last sales invoice created
        $lastSalesInvoice = AssetSales::whereRaw('SUBSTRING(asset_sales_number, 1, ?) = ?', [strlen($prefix), $prefix])
            ->orderBy('asset_sales_number', 'desc')
            ->first();
            // dd($lastSalesInvoice);

        // Determine the new invoice number
        if ($lastSalesInvoice) {
            // Extract the last number from the last invoice number
            $lastNumber = (int)substr($lastSalesInvoice->asset_sales_number, -5);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            // Reset counter to 00001 if no invoices found for the current month
            $newNumber = '00001';
        }

        // Return the new invoice number in the desired format
        return "$prefix$newNumber";
    }
}
