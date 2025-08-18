<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Coa;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\CategoryCustomer;
use App\Models\Item;
use App\Models\Journal;
use App\Models\DeliveryOrder;
use App\Models\ItemUnit;
use App\Models\InventoryDetail;
use App\Models\DeliveryOrderDetail;
use App\Models\ItemDetail;
use App\Models\DeleteLog;
use App\Models\ItemCategory;
use App\Models\Department;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceDetail;
use App\Models\ItemSalesPrice;
use App\Models\SalesOrder;
use App\Models\ReceivablePaymentDetail;
use App\Models\SalesDebtCreditNote;
use App\Models\TaxMaster;
use App\Models\Receivable;
use App\Models\SalesOrderDetail;
use App\Models\Warehouse;
use App\Http\Controllers\Module;
use App\Models\PayablePaymentDetail;
use App\Models\ReceivableListDetail;
use App\Models\ReceivableListSalesmanDetail;
use App\Models\Pbr;
use App\Models\Periode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use TCPDF;

class SalesInvoiceController extends Controller
{
    public function index() {
        $salesInvoices = SalesInvoice::where('department_code', 'DP01')->orderBy('document_date','desc')->orderBy('id','desc')->get();
        $privileges = Auth::user()->roles->privileges['sales_invoice'];

        return view('transaction.sales-invoice.sales_invoice_list', compact('salesInvoices','privileges'));
    }

    public function create() {
        $companies = Company::all();
        $customers = Customer::get();
        $items = ItemSalesPrice::where('department_code','DP01')->whereHas('items')->orderBy('item_code')->distinct('item_code')->with('items', 'unitn','itemDetails','items.warehouses')->get();
        $itemDetails = ItemDetail::where('department_code','DP01')->where('status',true)->get();
        $itemUnits = ItemUnit::all();
        $taxs = TaxMaster::where('tax_code','!=','PPN')->get();
        $department_TDS = 'DP01';
        $department_TDSn = Department::where('department_code', $department_TDS)->first();

        $salesOrder = SalesOrder::where('status','!=','Cancelled')->with([
            'department',
            'details' => function ($query) {
                $query->where('qty_left', '>', 0)
                    ->with(['items','items.warehouses', 'units']);
            }
        ])
        ->orderBy('id', 'asc')
        ->where('status', '!=', 'Closed')
        ->where('department_code', 'DP01')
        ->get();
        $privileges = Auth::user()->roles->privileges['sales_invoice'];

        $token = str()->random(16);
        return view('transaction.sales-invoice.sales_invoice_input', compact('companies', 'items', 'customers', 'customers', 'taxs','department_TDS', 'department_TDSn','salesOrder','privileges','items','itemDetails','itemUnits','token'));
    }

    public function store(Request $request) {
        // dd($request->all());

        $exist = SalesInvoice::where('token',$request->token)->where('department_code','DP01')->whereDate('created_at',Carbon::today())->first();
        if($exist){
            $id = SalesInvoice::where('created_by',Auth::user()->username)->orderBy('id','desc')->select('id')->first()->id;
            return redirect()->route('transaction.sales_invoice.create')->with('success', 'Sales Invoice created successfully.')->with('id',$id);
        }

        DB::beginTransaction(); // Begin transaction to ensure atomicity
        try {
            $sales_invoice_number = $this->generateSalesInvoiceNumber($request->document_date);

            if(SalesInvoice::where('sales_invoice_number', $request->sales_invoice_number)->count() < 1) {

            $general = new SalesInvoice();
            $general->sales_invoice_number = $sales_invoice_number;
            $general->contract_number = $request->contract_number;
            $general->document_date = $request->document_date;
            $general->due_date = $request->due_date;
            $general->customer_code = $request->customer_code;
            $general->token = $request->token;
            $general->tax = 'PPN';
            $general->status = 'Open';
            $general->disc_nominal = str_replace(',', '', $request->disc_nominal??0);
            // $general->con_invoice = $request->con_invoice;
            // $general->status = 'Open';


            $general->notes = $request->notes;
            $general->company_code = $request->company_code;
            $general->department_code = $request->department_code;
            $general->created_by = Auth::user()->username;
            $general->updated_by = Auth::user()->username;

            // Save the main cash in entry
            $tax_revenue_tariff=0;
            if($request->tax_revenue!=0){
                $tax_revenue_tariffs = TaxMaster::where('tax_code',$request->tax_revenue)->first();
                $tax_revenue_tariff = $tax_revenue_tariffs->tariff;
            }
            $general->tax_revenue_tariff = $request->tax_revenue;

            // Save the details
            $this->saveSalesInvoiceDetails($request->details, $sales_invoice_number, $request->company_code, $request->department_code,$request->customer_code,$general, $tax_revenue_tariff,'store');

            $id = SalesInvoice::where('sales_invoice_number',$sales_invoice_number)->select('id')->first()?SalesInvoice::where('sales_invoice_number',$sales_invoice_number)->select('id')->first()->id:84;

            DB::commit(); // Commit transaction
            return redirect()->route('transaction.sales_invoice.create')->with('success', 'Sales Invoice created successfully.')->with('id',$id);

            } else {
                return redirect()->back()->with('error', 'Sales Invoice Number must not be the same');
            };


        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to create Sales Invoice: ' . $e->getMessage());
        }
    }

    public function edit($id) {
        try {
            $companies = Company::all();
            $departments = Department::where('department_code', 'DP01')->first();
            $coas = Coa::all();
            $items = ItemSalesPrice::where('department_code','DP01')->whereHas('items')->orderBy('item_code')->distinct('item_code')->with('items', 'unitn','itemDetails','items.warehouses')->get();
            $itemUnits = ItemUnit::all();
            $itemDetails = ItemDetail::where('department_code','DP01')->where('status',true)->get();
            $customers = Supplier::all();
            $customers = Customer::get();
            $taxs = TaxMaster::where('tax_code','!=','PPN')->get();
            $warehouses = Warehouse::all();

            $salesInvoice = SalesInvoice::with('details')->findOrFail($id);

            // dd($SalesInvoice);
            $department_TDS = 'DP01';
            $department_TDSn = Department::where('department_code', $department_TDS)->first();
            $salesInvoiceDetails = SalesInvoiceDetail::where('sales_invoice_number', $salesInvoice->sales_invoice_number)->with(['items','units'])->orderBy('id','asc')->get();


            // Format dates for display
            $salesInvoice->document_date = Carbon::parse($salesInvoice->document_date)->format('Y-m-d');

            $salesInvoice->due_date = Carbon::parse($salesInvoice->due_date)->format('Y-m-d');

            $editable = true;
            $payable = ReceivablePaymentDetail::where('document_number',$salesInvoice->sales_invoice_number)->get();
            // $returns = SalesReturn::where('sales_invoice_number',$salesInvoice->sales_invoice_number)->get();
            $notes = SalesDebtCreditNote::where('invoice_number',$salesInvoice->sales_invoice_number)->get();
            $editable = (count($payable) > 0 || count($notes) > 0) ? false : true;
            $note='';
            $periodeClosed = Periode::where('periode_active', 'closed')
            ->where('periode_start', '<=', $salesInvoice->document_date)
            ->where('periode_end', '>=', $salesInvoice->document_date)
            ->first();
            if(count($payable) > 0 ){
                $note.='Sudah dibayar <br>';
            }
            if($periodeClosed){
                $note.='Sudah di Closing <br>';
                $editable = false;
            }
            $privileges = Auth::user()->roles->privileges['sales_invoice'];

            return view('transaction.sales-invoice.sales_invoice_edit', compact('salesInvoice', 'salesInvoiceDetails', 'companies', 'departments', 'coas', 'items', 'itemUnits', 'itemDetails', 'customers', 'customers', 'taxs', 'department_TDS', 'department_TDSn', 'editable','warehouses','privileges','note'));
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with('error', 'Failed to load edit form: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        // dd($request->all());
        DB::beginTransaction(); // Start the transaction
        try {
            // Retrieve the SalesInvoice record by sales_invoice_number

            $general = SalesInvoice::where('id', $id)->firstOrFail();
            $sales_invoice_number = $general->sales_invoice_number;
            $general->contract_number = $request->contract_number;
            $general->tax_revenue_tariff = $request->tax_revenue;
            $general->document_date = $request->document_date;
            $general->due_date = $request->due_date;
            $general->reason = $request->edit_reason??'';
            $general->tax = 'PPN';

            $general->disc_nominal = str_replace(',', '', $request->disc_nominal??0);
            // $general->con_invoice = $request->con_invoice;
            // $general->status = 'Open';

            $general->notes = $request->notes;
            $general->created_by = Auth::user()->username;
            $general->updated_by = Auth::user()->username;

            // Save the main cash in entry
            $tax_revenue_tariff=0;
            if($request->tax_revenue!=0){
                $tax_revenue_tariffs = TaxMaster::where('tax_code',$request->tax_revenue)->first();
                $tax_revenue_tariff = $tax_revenue_tariffs->tariff;
            }

            $oldDetail  = SalesInvoiceDetail::where('sales_invoice_number', $sales_invoice_number)->get();
            InventoryDetail::where('document_number', $general->sales_invoice_number)->delete();
            Receivable::where('document_number', $sales_invoice_number)->delete();
            Journal::where('document_number', $general->sales_invoice_number)->delete();
            SalesInvoiceDetail::where('sales_invoice_number', $general->sales_invoice_number)->delete();

            // Save the details
            $this->saveSalesInvoiceDetails($request->details, $sales_invoice_number, $request->company_code, $request->department_code,$request->customer_code,$general, $tax_revenue_tariff,'update');
            // Parse and assign date fields
            DB::commit(); // Commit the transaction
            return redirect()->route('transaction.sales_invoice')->with('success', 'Sales Invoice updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            // dd($e);
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to update Sales Invoice: ' . $e->getMessage());
        }
    }



    public function destroy(Request $request, $id) {
        DB::beginTransaction();
        try {
            $general = SalesInvoice::findOrFail($id);
            SalesInvoice::where('sales_invoice_number', $general->sales_invoice_number)->delete();
            SalesInvoiceDetail::where('sales_invoice_number', $general->sales_invoice_number)->delete();
            $general->delete();
            InventoryDetail::where('document_number', $general->sales_invoice_number)->delete();

            Journal::where('document_number',$general->sales_invoice_number)->delete();
            Receivable::where('document_number',$general->sales_invoice_number)->delete();


            $reason = $request->input('reason');

            DeleteLog::create([
                'document_number' => $general->sales_invoice_number,
                'document_date' => $general->document_date,
                'delete_notes' => $reason,
                'type' => 'SI',
                'company_code' => $general->company_code,
                'department_code' => $general->department_code,
                'deleted_by' => Auth::user()->username,
            ]);

            DB::commit(); // Commit transaction
            return redirect()->route('transaction.sales_invoice')->with('success', 'Sales Invoice deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            Log::error($e);
            return redirect()->route('transaction.sales_invoice')->with('error', 'Error deleting: ' . $e->getMessage());
        }
    }

    public function printSalesInvoicePDF($sales_invoice_number)
    {
        $salesInvoice = SalesInvoice::with([
            'company',
            'customers',
            'details' => function ($query) {
                $query->orderBy('id', 'asc');
            },
        ])->findOrFail($sales_invoice_number);

        $discTotal = 0;
        foreach($salesInvoice->details as $detail){
            $discTotal += ($detail->disc_nominal + ($detail->disc_percent / 100) * ($detail->qty * $detail->price));
        }


        $imagePath = storage_path('app/images/logo.jpg');
        $imageData = file_get_contents($imagePath);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('transaction.sales-invoice.sales_invoice_pdf', compact('salesInvoice', 'imageData', 'discTotal'));
        $nameFile = Str::replace("/", "", $salesInvoice->sales_invoice_number);
        return $pdf->stream("Sales_Invoice_{$nameFile}.pdf");
    }

    public function summary(Request $request)
    {
        // Initialize query for fetching sales invoices
        $query = SalesInvoice::query();

        // Apply date filtering if 'from_date' and 'to_date' are present in the request
        if ($request->filled('from_date') && $request->filled('to_date')) {
            // Convert dates to Carbon instances for proper formatting and range filtering
            $fromDate = Carbon::parse($request->input('from_date'))->startOfDay();
            $toDate = Carbon::parse($request->input('to_date'))->endOfDay();

            // Apply the date range filter on the 'document_date' column
            $query->whereBetween('document_date', [$fromDate, $toDate]);
        }else{
            $query->whereDate('document_date',Carbon::today())->get();
        }

        $query2 = Pbr::query();

        // Apply date filtering if 'from_date' and 'to_date' are present in the request
        if ($request->filled('from_date') && $request->filled('to_date')) {
            // Convert dates to Carbon instances for proper formatting and range filtering
            $fromDate = Carbon::parse($request->input('from_date'))->startOfDay();
            $toDate = Carbon::parse($request->input('to_date'))->endOfDay();

            // Apply the date range filter on the 'document_date' column
            $query2->whereBetween('document_date', [$fromDate, $toDate]);
        }else{
            $query2->whereDate('document_date',Carbon::today())->get();
        }

        // Retrieve filtered sales invoices
        $salesInvoices = $query->where('department_code','DP01')->orderBy('id','asc')->get();
        $pbr = $query2->where('department_code','DP01')->orderBy('id','asc')->get();
        $salesInvoices = $salesInvoices->concat($pbr);

        $customers = Customer::where('department_code','DP01')->get();

        // Calculate the total amount from all filtered sales invoices
        $totalAmount = $salesInvoices->sum('total');
        $privileges = Auth::user()->roles->privileges['sales_invoice'];

        // Return the view with the sales invoices data and total amount
        return view('transaction.sales-invoice.sales_invoice_summary', compact('salesInvoices', 'totalAmount','privileges','customers'))
        ->with([
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date')
        ]);
    }

    public function summaryDetail(Request $request)
    {
        // Initialize query for fetching sales invoices
        $query = SalesInvoice::query();

        // Apply date filtering if 'from_date' and 'to_date' are present in the request
        if ($request->filled('from_date') && $request->filled('to_date')) {
            // Convert dates to Carbon instances for proper formatting and range filtering
            $fromDate = Carbon::parse($request->input('from_date'))->startOfDay();
            $toDate = Carbon::parse($request->input('to_date'))->endOfDay();

            // Apply the date range filter on the 'document_date' column
            $query->whereBetween('document_date', [$fromDate, $toDate]);
        }else{
            $query->whereDate('document_date',Carbon::today())->get();
        }

        $query2 = Pbr::query();
        // Apply date filtering if 'from_date' and 'to_date' are present in the request
        if ($request->filled('from_date') && $request->filled('to_date')) {
            // Convert dates to Carbon instances for proper formatting and range filtering
            $fromDate = Carbon::parse($request->input('from_date'))->startOfDay();
            $toDate = Carbon::parse($request->input('to_date'))->endOfDay();

            // Apply the date range filter on the 'document_date' column
            $query2->whereBetween('document_date', [$fromDate, $toDate]);
        }else{
            $query2->whereDate('document_date',Carbon::today())->get();
        }

        // Retrieve filtered sales invoices
        $salesInvoices = $query->with('details')->where('department_code','DP01')->orderBy('id','asc')->get();
        $pbr = $query2->with('details')->where('department_code','DP01')->orderBy('id','asc')->get();
        $salesInvoices = $salesInvoices->concat($pbr);

        // Calculate the total amount from all filtered sales invoices
        $totalAmount = $salesInvoices->sum('subtotal');
        $privileges = Auth::user()->roles->privileges['sales_invoice'];

        // Return the view with the sales invoices data and total amount
        return view('transaction.sales-invoice.sales_invoice_summary_detail', compact('salesInvoices', 'totalAmount','privileges'))
        ->with([
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date')
        ]);
    }


    private function generateSalesInvoiceNumber($date) {
        $today = Carbon::parse($date);
        $month = $today->format('n'); // Numeric representation of a month (1-12)
        $year = $today->format('y');
        // Convert month to Roman numeral
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        $romanMonth = $romanMonths[$month];
        $prefix = "TDS/INV/{$romanMonth}/{$year}-"; //

        $lastSalesInvoice = SalesInvoice::whereRaw('SUBSTRING(sales_invoice_number, 1, ?) = ?', [strlen($prefix), $prefix])
            ->orderBy('sales_invoice_number', 'desc')
            ->first();

        if ($lastSalesInvoice) {
            $lastNumber = (int)substr($lastSalesInvoice->sales_invoice_number, -5);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }

        return "$prefix$newNumber";
    }

    private function saveSalesInvoiceDetails(array $SalesInvoiceDetails, $sales_invoice_number, $company_code, $department_code,$customer_code,$general, $tax_revenue_tariff,$type) {
        $nominal = 0;
        $revenueTax = 0;
        $addTax = 0;
        $services = 0;
        $taxed = 0;
        $totalAllAfterDiscountBeforeTax = 0;
        $totalHPP = 0;
        $itemNameConcat = "";
        foreach ($SalesInvoiceDetails as $detail) {
            $detail['price'] = str_replace(',', '', $detail['price']);
            $detail['disc_percent'] = str_replace(',', '', $detail['disc_percent']??0);
            $detail['disc_nominal'] = str_replace(',', '', $detail['disc_nominal']??0);
            $detail['nominal'] = str_replace(',', '', $detail['nominal']??0);
            $customer = Customer::where('customer_code', $customer_code)->first();
            $itemTax = Item::where('department_code','DP01')->where('item_code', $detail['item_id'])->first();
            $taxs = TaxMaster::where('tax_code', $general->tax)->first();

            if($customer->pkp == 1 && strtolower($itemTax->category->item_category_name) != 'service') {
                if($customer->include == 1) {
                    if ($itemTax->additional_tax == 1 ) {
                        $totalAllAfterDiscountBeforeTax += (($detail['qty']*$detail['price']*$detail['base_qty']) / (1 + $taxs->tax_base* $taxs->tariff / 100)) - ($detail['disc_percent']/100*(($detail['qty']*$detail['price']) / (1 + $taxs->tax_base * $taxs->tariff / 100)))-$detail['disc_nominal'];
                    } else {
                        $totalAllAfterDiscountBeforeTax += $detail['qty']*$detail['price'] - ($detail['disc_percent']/100*(($detail['qty']*$detail['price'])))-$detail['disc_nominal'];
                    }
                } else {
                    $totalAllAfterDiscountBeforeTax += $detail['qty']*$detail['price'] - ($detail['disc_percent']/100*(($detail['qty']*$detail['price'])))-$detail['disc_nominal'];
                }
            }else {
                $totalAllAfterDiscountBeforeTax += $detail['qty']*$detail['price'] - ($detail['disc_percent']/100*(($detail['qty']*$detail['price'])))-$detail['disc_nominal'];
            }

        }

        $totalAllItemBeforeTax = 0;
        $totalAllDiscountDetail = 0;
        foreach ($SalesInvoiceDetails as $index => $detail) {
            $detail['price'] = str_replace(',', '', $detail['price']);
            $detail['disc_percent'] = str_replace(',', '', $detail['disc_percent']??0);
            $detail['disc_nominal'] = str_replace(',', '', $detail['disc_nominal']??0);
            $detail['nominal'] = str_replace(',', '', $detail['nominal']??0);
            $detail['description'] = trim($detail['description']??'');

            // Ensure index is the same as the row number from the form input
            $detail['sales_invoice_number'] = $sales_invoice_number;
            $detail['number_row'] = $index + 1; // Correctly assign row number
            $detail['company_code'] = $company_code;
            $detail['department_code'] = $department_code;
            $nominal += $detail['qty']*$detail['price']-($detail['disc_percent']/100*$detail['qty']*$detail['price'])-$detail['disc_nominal'];
            $detail['created_by'] = Auth::user()->username;
            $detail['updated_by'] = Auth::user()->username;

            $item = ItemDetail::where('department_code','DP01')->where('status',true)->where([
                ['unit_conversion', $detail['unit']],
                ['item_code',$detail['item_id']]
                ])->first();
            $detail['base_qty'] = $item->conversion;
            $detail['base_qty_left'] = $detail['base_qty']*$detail['qty'];
            $detail['base_unit'] = $item->base_unit;
            $detail['status'] = 'Not';
            $detail['qty_left'] = $detail['qty'];


            $customer = Customer::where('customer_code', $customer_code)->first();
            $itemTax = Item::where('department_code','DP01')->where('item_code', $detail['item_id'])->first();
            $taxs = TaxMaster::where('tax_code', $general->tax)->first();
            if ($itemTax->additional_tax == 1) {
                $detail['add_tax_detail'] = $detail['nominal'] * $taxs->tax_base * $taxs->tariff/100;
            }else{
                $detail['add_tax_detail'] = 0;
            }

            $totalPriceBeforeTaxBeforeDiscount = 0;
            $totalPriceBeforeTaxAfterDiscount = 0;
            $totalDiscountPerDetail = 0;
            $discPerDetail = 0;

            if (strtolower($itemTax->category->item_category_name) == 'service') {
                $services += $detail['nominal'];

            }
            if ($customer->pkp == 1) {


                if($customer->include == 1) {
                    if ($itemTax->additional_tax == 1) {
                        $totalPriceBeforeTaxBeforeDiscount = ($detail['qty']*$detail['price'])/(1 + $taxs->tax_base* $taxs->tariff / 100)*$detail['base_qty'];
                        $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                        $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $general->disc_nominal;
                        $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                        $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                        $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];
                        $taxed += $totalPriceBeforeTaxAfterDiscount;
                    }else{
                        $totalPriceBeforeTaxBeforeDiscount = $detail['qty']*$detail['price']*$detail['base_qty'];
                        $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                        $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $general->disc_nominal;
                        $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                        $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                        $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];
                    }
                } else {
                    if ($itemTax->additional_tax == 1) {
                        $totalPriceBeforeTaxBeforeDiscount = $detail['qty']*$detail['price']*$detail['base_qty'];
                        $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                        $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $general->disc_nominal;
                        $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                        $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                        $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];
                        $taxed += $totalPriceBeforeTaxAfterDiscount;
                    }else{
                        $totalPriceBeforeTaxBeforeDiscount = $detail['qty']*$detail['price']*$detail['base_qty'];
                        $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                        $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $general->disc_nominal;
                        $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                        $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                        $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];
                    }
                }
            } else {
                $totalPriceBeforeTaxBeforeDiscount = $detail['qty']*$detail['price']*$detail['base_qty'];
                $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $general->disc_nominal;
                $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];

            }
            $totalAllItemBeforeTax+=$totalPriceBeforeTaxAfterDiscount;
            $priceUpdate = $detail['price'];
            $detail['disc_header'] = $discPerDetail;
            SalesInvoiceDetail::create($detail);
            // dd($detail);


            $itemUnit = ItemSalesPrice::where('department_code','DP01')->where('item_code', $detail['item_id'])->first();

            $customers = Customer::where('customer_code', $customer_code)->first();
            $itemTax = Item::where('department_code','DP01')->where('item_code', $detail['item_id'])->first();
            $categories;
            if($itemTax){
                $categories = ItemCategory::where('item_category_code', $itemTax->item_category)->first();
            }

            //PI Detail Value - Pendapatan Jasa
            $SIJournalD = new Journal();
            $SIJournalD->document_number = $detail['sales_invoice_number'];
            $SIJournalD->document_date = $general->document_date;
            $SIJournalD->account_number = $categories->acc_number_sales??'1001';
            $SIJournalD->debet_nominal = 0;
            $SIJournalD->credit_nominal = $totalPriceBeforeTaxAfterDiscount+$totalDiscountPerDetail;
            $SIJournalD->notes = 'Sales '.$itemTax->item_name.' : '.$detail['qty'].' '.$itemUnit->units->unit_name ;
            $SIJournalD->company_code = $general->company_code;
            $SIJournalD->department_code = $general->department_code;
            $SIJournalD->created_by = Auth::user()->username;
            $SIJournalD->updated_by = Auth::user()->username;
            $SIJournalD -> save();


            //PI Discount Total(Discount per detail + Discount Allocation from header) - Diskon
            $SIJournalDd = new Journal();
            $SIJournalDd->document_number = $detail['sales_invoice_number'];
            $SIJournalDd->document_date = $general->document_date;
            $SIJournalDd->account_number = $categories->acc_number_sales_discount??'1001';
            $SIJournalDd->debet_nominal = $totalDiscountPerDetail;
            $SIJournalDd->credit_nominal = 0;
            $SIJournalDd->notes = 'Discount on sales '.$itemTax->item_name.' : '.$detail['qty'].' '.$itemUnit->units->unit_name ;
            $SIJournalDd->company_code = $general->company_code;
            $SIJournalDd->department_code = $general->department_code;
            $SIJournalDd->created_by = Auth::user()->username;
            $SIJournalDd->updated_by = Auth::user()->username;
            if($totalDiscountPerDetail>0){
                $SIJournalDd -> save();
            }

            $totalHPP+=$totalPriceBeforeTaxAfterDiscount;
            $itemNameConcat.=$itemTax->item_name." | ";

            InventoryDetail::insert([
                'document_number'=>$detail['sales_invoice_number'],
                'document_date'=>$general->document_date,
                'transaction_type'=>'Sales',
                'from_to'=>$general->customer_code,
                'item_id'=>$detail['item_id'],
                'quantity'=>$detail['qty'],
                'unit'=>$detail['unit'],
                'base_quantity'=>$detail['base_qty'],
                'unit_base'=>$detail['base_unit'],
                'company_code'=>$general->company_code,
                'department_code'=>$general->department_code,
                'created_by' => $general->created_by,
                'updated_by' => Auth::user()->username,
                'total' => $totalPriceBeforeTaxAfterDiscount * -1,
                'cogs' => $totalPriceBeforeTaxAfterDiscount * -1,
                'qty_actual' => $detail['qty'] * -1
            ]);
        }

        //SI HPP
        $SIJournalDd = new Journal();
        $SIJournalDd->document_number = $general->sales_invoice_number;
        $SIJournalDd->document_date = $general->document_date;
        $SIJournalDd->account_number = $categories->acc_cogs??'1001';
        $SIJournalDd->debet_nominal = $totalHPP;
        $SIJournalDd->credit_nominal = 0;
        $SIJournalDd->notes = 'HPP '.$itemNameConcat;
        $SIJournalDd->company_code = $general->company_code;
        $SIJournalDd->department_code = $general->department_code;
        $SIJournalDd->created_by = Auth::user()->username;
        $SIJournalDd->updated_by = Auth::user()->username;
        // $SIJournalDd -> save();


        //SI Persediaan
        $SIJournalDd = new Journal();
        $SIJournalDd->document_number = $general->sales_invoice_number;
        $SIJournalDd->document_date = $general->document_date;
        $SIJournalDd->account_number = $categories->account_inventory??'1001';
        $SIJournalDd->debet_nominal = 0;
        $SIJournalDd->credit_nominal = $totalHPP;
        $SIJournalDd->notes = 'Inventory '.$itemNameConcat ;
        $SIJournalDd->company_code = $general->company_code;
        $SIJournalDd->department_code = $general->department_code;
        $SIJournalDd->created_by = Auth::user()->username;
        $SIJournalDd->updated_by = Auth::user()->username;
        // $SIJournalDd -> save();

        $customer = Customer::where('customer_code', $customer_code)->first();
        // dd($totalAllItemBeforeTax);
        if ($customer->pkp == 1) {
            $addTax = $taxed* $taxs->tax_base * $taxs->tariff/100;
            $revenueTax = $services * $tax_revenue_tariff/100; //nanti diubah dengan pilihan dari header
        } else {
            $revenueTax = 0;
            $addTax = 0;
        }

        $general->add_tax = $addTax;
        $general->tax_revenue = $revenueTax;

        $general->subtotal = $totalAllItemBeforeTax+$general->disc_nominal;
        $general->total = $general->subtotal-$general->disc_nominal + $general->add_tax - $general->tax_revenue;

        $general->save();


        $customers = Customer::where('customer_code', $general->customer_code)->first();
        $taxes = TaxMaster::where('tax_code', $general->tax_revenue_tariff)->first();

        //PI Header Total Purchase - Piutang Dagang
        $PIJournal = new Journal();
        $PIJournal->document_number = $general->sales_invoice_number;
        $PIJournal->document_date = $general->document_date;
        $PIJournal->account_number = $customers->account_receivable??'1001';
        $PIJournal->debet_nominal = $general->total;
        $PIJournal->credit_nominal = 0;
        $PIJournal->notes = 'Sales from '.$customers->customer_name.' ('.$general->sales_invoice_number.')';
        $PIJournal->company_code = $general->company_code;
        $PIJournal->department_code = $general->department_code;
        $PIJournal->created_by = Auth::user()->username;
        $PIJournal->updated_by = Auth::user()->username;
        $PIJournal -> save();

        //PI Header Discount
        if($general->disc_nominal>0){
            $itemTax = Item::where('department_code','DP01')->where('item_code', $detail['item_id'])->first();
            $categories;
            if($itemTax){
                $categories = ItemCategory::where('item_category_code', $itemTax->item_category)->first();
            }
            //
            $PIJournald = new Journal();
            $PIJournald->document_number = $general->sales_invoice_number;
            $PIJournald->document_date = $general->document_date;
            $PIJournald->account_number = $categories->acc_number_sales_discount??'1001';
            $PIJournald->debet_nominal = $general->disc_nominal;
            $PIJournald->credit_nominal = 0;
            $PIJournald->notes = 'Discount on sales for '.$customers->customer_name.' ('.$general->sales_invoice_number.')';
            $PIJournald->company_code = $general->company_code;
            $PIJournald->department_code = $general->department_code;
            $PIJournald->created_by = Auth::user()->username;
            $PIJournald->updated_by = Auth::user()->username;
            // $PIJournald -> save();
        }

        //PI Header Add Tax - PPN
        $PIJournala = new Journal();
        $PIJournala->document_number = $general->sales_invoice_number;
        $PIJournala->document_date = $general->document_date;
        $PIJournala->account_number = $customers->account_add_tax??'1001';
        $PIJournala->debet_nominal = 0;
        $PIJournala->credit_nominal = $general->add_tax;
        $PIJournala->notes = 'Add. Tax on sales for '.$customers->customer_name.' ('.$general->sales_invoice_number.')';
        $PIJournala->company_code = $general->company_code;
        $PIJournala->department_code = $general->department_code;
        $PIJournala->created_by = Auth::user()->username;
        $PIJournala->updated_by = Auth::user()->username;
        if($general->add_tax>0){
            $PIJournala -> save();
        }
        //PI Header Total Discount
        $PIJournala = new Journal();
        $PIJournala->document_number = $general->sales_invoice_number;
        $PIJournala->document_date = $general->document_date;
        $PIJournala->account_number = $customers->account_add_tax??'1001';
        $PIJournala->debet_nominal = $totalAllDiscountDetail;
        $PIJournala->credit_nominal = 0;
        $PIJournala->notes = 'Discount Items on sales for '.$customers->customer_name.' ('.$general->sales_invoice_number.')';
        $PIJournala->company_code = $general->company_code;
        $PIJournala->department_code = $general->department_code;
        $PIJournala->created_by = Auth::user()->username;
        $PIJournala->updated_by = Auth::user()->username;
        // $PIJournala -> save();

        //PI Header Revenue Tax
        if($taxes){
            $PIJournala = new Journal();
            $PIJournala->document_number = $general->sales_invoice_number;
            $PIJournala->document_date = $general->document_date;
            $PIJournala->account_number = $taxes->account_number??'1001';
            $PIJournala->debet_nominal = $general->tax_revenue;
            $PIJournala->credit_nominal = 0;
            $PIJournala->notes = 'Revenue Tax on sales for '.$customers->customer_name.' ('.$general->sales_invoice_number.')';
            $PIJournala->company_code = $general->company_code;
            $PIJournala->department_code = $general->department_code;
            $PIJournala->created_by = Auth::user()->username;
            $PIJournala->updated_by = Auth::user()->username;
            if($general->tax_revenue>0){
                $PIJournala -> save();
            }
        }

            Receivable::create([
                'document_number'=>$general->sales_invoice_number,
                'document_date'=>$general->document_date,
                'due_date'=>$general->due_date,
                'total_debt'=>$general->total,
                'debt_balance'=>$general->total,
                'customer_code'=>$general->customer_code,
                'due_date'=>$general->due_date,
                'company_code'=>$general->company_code,
                'department_code'=>$general->department_code,
                'created_by' => Auth::user()->username,
                'updated_by' => Auth::user()->username,
            ]);


    }

    public function showDeliveryPage()
    {
        $privileges = Auth::user()->roles->privileges['sales_invoice'];
        $salesInvoices = SalesInvoice::where('department_code','DP01')->where('status','Open')->orderBy('id','desc')->get();
        $pbrInvoices = Pbr::where('department_code','DP01')->whereNull('delivery_date')->orWhere('status','Open')->orderBy('id','desc')->get();
        $combinedInvoices = $salesInvoices->concat($pbrInvoices);
        return view('transaction.sales-invoice.sales_invoice_delivery',compact('combinedInvoices','privileges'));
    }

    public function updateStatus(Request $request)
    {
        DB::beginTransaction(); // Begin transaction to ensure atomicity
        try {
            foreach($request->details as $detail)
            {
                if($detail['delivery_date']){
                    if($detail['type'] === 'sales_invoice') {
                        SalesInvoice::where('sales_invoice_number', $detail['document_number'])
                            ->update([
                                'status' => 'Delivered',
                                'delivery_date' => $detail['delivery_date']
                            ]);
                    } else { // pbr
                        Pbr::where('pbr_number', $detail['document_number'])
                            ->update([
                                'status' => 'Delivered',
                                'delivery_date' => $detail['delivery_date']
                            ]);
                    }
                }
            }
            DB::commit(); // Commit transaction
            return redirect()->route('transaction.sales_invoice')->with('success', 'Documents updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            // dd($e);
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to update Sales Invoice: ' . $e->getMessage());
        }

    }

    public function showCancelDeliveryPage()
    {
        $privileges = Auth::user()->roles->privileges['sales_invoice'];
        $salesInvoices = SalesInvoice::where('department_code','DP01')->where('status','Delivered')->orderBy('id','desc')->get();
        $salesInvoices = $salesInvoices->filter(function($si){
            $existReceivablePayment = PayablePaymentDetail::where('document_number',$si->sales_invoice_number)->exists();
            $existReceivableList = ReceivableListDetail::where('document_number',$si->sales_invoice_number)->exists();
            $existReceivableListSales = ReceivableListSalesmanDetail::where('document_number',$si->sales_invoice_number)->exists();

            return !$existReceivablePayment&&!$existReceivableList&&!$existReceivableListSales;
        });
        return view('transaction.sales-invoice.sales_invoice_delivery_cancel',compact('salesInvoices','privileges'));
    }

    public function updateStatusCancel(Request $request)
    {
        DB::beginTransaction(); // Begin transaction to ensure atomicity
        try {
            // dd($request->all());
            foreach($request->details as $si)
            {
                if(array_key_exists('check',$si)){
                    SalesInvoice::where('sales_invoice_number',$si['sales_invoice_number'])->update([
                        'status'=>'Open',
                        'delivery_date'=>null
                    ]);
                }
            }
            DB::commit(); // Commit transaction
            return redirect()->route('transaction.sales_invoice')->with('success', 'Sales Invoice updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            // dd($e);
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to update Sales Invoice: ' . $e->getMessage());
        }

    }

    public function summaryDelivery(Request $request)
    {
        $fromDate = $request->from;
        $toDate = $request->to;
        if(!$fromDate&&!$toDate){
            $fromDate = date('Y-m-d');
            $toDate = date('Y-m-d');
        }

        $privileges = Auth::user()->roles->privileges['sales_invoice'];
        $salesInvoices = SalesInvoice::where('department_code','DP01')->whereBetween('delivery_date',[$fromDate,$toDate])->where('status','Delivered')->orderBy('id','desc')->get();
        $pbrInvoices = Pbr::where('department_code','DP01')->whereBetween('delivery_date',[$fromDate,$toDate])->where('status','Delivered')->orWhereNotNull('delivery_date')->orderBy('id','desc')->get();
        $combinedInvoices = $salesInvoices->concat($pbrInvoices);
        $customers = Customer::where('department_code','DP01')->get();

        return view('transaction.sales-invoice.sales_invoice_delivery_report',compact('combinedInvoices','privileges','customers'));
    }

    public function recalcJournal()
    {
        DB::beginTransaction();
        try {
                $salesInvoices = SalesInvoice::all();
                foreach ($salesInvoices as $general) {
                    Journal::where('document_number',$general->sales_invoice_number)->delete();
                    $details = SalesInvoiceDetail::where('sales_invoice_number',$general->sales_invoice_number)->get();
                    $categories = ItemCategory::first();
                    $totalHPP=0;
                    $itemNameConcate="";
                    $customers = Customer::where('customer_code', $general->customer_code)->first();

                    foreach ($details as $detail) {
                        $itemTax = Item::where('department_code',$general->department_code)->where('item_code', $detail->item_id)->first();
                        $totalHPP+=$detail->nominal;
                        $itemNameConcate.=$itemTax->item_name.'|';

                        //SI Detail Value
                        $SIJournalD = new Journal();
                        $SIJournalD->document_number = $detail->sales_invoice_number;
                        $SIJournalD->document_date = $general->document_date;
                        $SIJournalD->account_number = $categories->acc_number_sales??'1001';

                        $SIJournalD->debet_nominal = 0;
                        $SIJournalD->credit_nominal = $detail->nominal+(($detail->disc_percent/100*$detail->qty*$detail->price*$detail->base_qty)+$detail->disc_nominal);
                        $SIJournalD->notes = 'Sales '.$itemTax->item_name.' : '.$detail->qty.' '.$detail->unit ;
                        $SIJournalD->company_code = $general->company_code;
                        $SIJournalD->department_code = $general->department_code;
                        $SIJournalD->created_by = Auth::user()->username;
                        $SIJournalD->updated_by = Auth::user()->username;
                        $SIJournalD -> save();

                        //SI Discount Total(Discount per detail + Discount Allocation from header)
                        $SIJournalDd = new Journal();
                        $SIJournalDd->document_number = $detail['sales_invoice_number'];
                        $SIJournalDd->document_date = $general->document_date;
                        $SIJournalDd->account_number = $categories->acc_number_sales_discount??'1001';
                        $SIJournalDd->debet_nominal = (($detail->disc_percent/100*$detail->qty*$detail->price*$detail->base_qty)+$detail->disc_nominal);
                        $SIJournalDd->credit_nominal = 0;
                        $SIJournalDd->notes = 'Discount on sales '.$itemTax->item_name.' : '.$detail->qty.' '.$detail->unit;
                        $SIJournalDd->company_code = $general->company_code;
                        $SIJournalDd->department_code = $general->department_code;
                        $SIJournalDd->created_by = Auth::user()->username;
                        $SIJournalDd->updated_by = Auth::user()->username;
                        $SIJournalDd -> save();
                    }

                    //SI HPP
                    $SIJournalDd = new Journal();
                    $SIJournalDd->document_number = $general->sales_invoice_number;
                    $SIJournalDd->document_date = $general->document_date;
                    $SIJournalDd->account_number = $categories->acc_cogs??'1001';
                    $SIJournalDd->debet_nominal = $totalHPP;
                    $SIJournalDd->credit_nominal = 0;
                    $SIJournalDd->notes = 'HPP '.$itemNameConcate;
                    $SIJournalDd->company_code = $general->company_code;
                    $SIJournalDd->department_code = $general->department_code;
                    $SIJournalDd->created_by = Auth::user()->username;
                    $SIJournalDd->updated_by = Auth::user()->username;
                    $SIJournalDd -> save();

                    //SI Persediaan
                    $SIJournalDd = new Journal();
                    $SIJournalDd->document_number = $general->sales_invoice_number;
                    $SIJournalDd->document_date = $general->document_date;
                    $SIJournalDd->account_number = $categories->account_inventory??'1001';
                    $SIJournalDd->debet_nominal = 0;
                    $SIJournalDd->credit_nominal = $totalHPP;
                    $SIJournalDd->notes = 'Inventory '.$itemNameConcate ;
                    $SIJournalDd->company_code = $general->company_code;
                    $SIJournalDd->department_code = $general->department_code;
                    $SIJournalDd->created_by = Auth::user()->username;
                    $SIJournalDd->updated_by = Auth::user()->username;
                    $SIJournalDd -> save();

                    //SI Header Total Purchase
                    $PIJournal = new Journal();
                    $PIJournal->document_number = $general->sales_invoice_number;
                    $PIJournal->document_date = $general->document_date;
                    $PIJournal->account_number = $customers->account_receivable??'1001';
                    $PIJournal->debet_nominal = $general->total;
                    $PIJournal->credit_nominal = 0;
                    $PIJournal->notes = 'Sales from '.$customers->customer_name.' ('.$general->sales_invoice_number.')';
                    $PIJournal->company_code = $general->company_code;
                    $PIJournal->department_code = $general->department_code;
                    $PIJournal->created_by = Auth::user()->username;
                    $PIJournal->updated_by = Auth::user()->username;
                    $PIJournal -> save();


                    //SI Header Add Tax
                    $PIJournala = new Journal();
                    $PIJournala->document_number = $general->sales_invoice_number;
                    $PIJournala->document_date = $general->document_date;
                    $PIJournala->account_number = $customers->account_add_tax??'1001';
                    $PIJournala->debet_nominal = 0;
                    $PIJournala->credit_nominal = $general->add_tax;
                    $PIJournala->notes = 'Add. Tax on sales for '.$customers->customer_name.' ('.$general->sales_invoice_number.')';
                    $PIJournala->company_code = $general->company_code;
                    $PIJournala->department_code = $general->department_code;
                    $PIJournala->created_by = Auth::user()->username;
                    $PIJournala->updated_by = Auth::user()->username;
                    if($general->department_code=='DP01'){
                        $PIJournala -> save();
                    }
                }
                DB::commit();
                return redirect()->route('transaction.sales_invoice')->with('success', 'Sales Invoice recalculate successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to create Purchase Invoice: ' . $e->getMessage())->withInput();
        }
    }
}
