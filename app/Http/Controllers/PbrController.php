<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\DeleteLog;
use App\Models\Department;
use App\Models\InventoryDetail;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemDetail;
use App\Models\ItemSalesPrice;
use App\Models\ItemUnit;
use App\Models\Journal;
use App\Models\Pbr;
use App\Models\PbrDetail;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use App\Models\TaxMaster;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PbrController extends Controller
{
    //
    public function index()
    {
        $pbrs = Pbr::where('department_code','DP01')->orderBy('id','desc')->get();
        $customers = Customer::where('department_code','DP01')->get();
        $privileges = Auth::user()->roles->privileges['pbr'];
        return view('transaction.pbr.pbr_list',compact('pbrs','privileges','customers'));
    }

    public function create()
    {
        $customers = Customer::where('department_code','DP01')->orderBy('customer_code','asc')->get();
        $items = ItemSalesPrice::where('department_code','DP01')->whereHas('items')->orderBy('item_code')->distinct('item_code')->with('items', 'unitn','itemDetails','items.warehouses')->get();
        $itemDetails = ItemDetail::where('department_code','DP01')->where('status',true)->get();
        $itemUnits = ItemUnit::all();
        $dp_code = 'DP01';
        $dpName = Department::where('department_code','DP01')->first()->department_name;
        $salesOrder = SalesOrder::
        where('department_code', 'DP01')
        ->where('is_pbr', true)
        ->where('status','!=','Closed')
        ->orderBy('id', 'asc')
        ->with([
            'department',
            'details' => function ($query) {
                $query->where('qty_left', '>', 0)
                    ->with(['items','items.warehouses', 'units']);
            }
        ])
        ->get();
        $privileges = Auth::user()->roles->privileges['pbr'];
        $token = str()->random(16);
        return view('transaction.pbr.pbr_input',compact('customers','items','itemDetails','itemUnits','dp_code','dpName','token','salesOrder','privileges'));
    }

    public function store(Request $request)
    {
        $exist = Pbr::where('token',$request->token)->where('department_code','DP01')->whereDate('created_at',Carbon::today())->first();
        if($exist){
            $id = Pbr::where('created_by',Auth::user()->username)->orderBy('id','desc')->select('id')->first()->id;
            return redirect()->route('transaction.pbr.create')->with('success', 'Sales PBR created successfully.')->with('id',$id);
        }

        DB::beginTransaction(); // Begin transaction to ensure atomicity
        try {
            $pbr_number = $this->generatePbrNumber();
            $general = new Pbr();
            $general->pbr_number = $pbr_number;
            $general->document_date = $request->document_date;
            $general->customer_code = $request->customer_code;
            $general->token = $request->token;
            $general->tax = 'PPN';
            $general->status = 'Open';
            $general->disc_nominal = 0;
            $general->notes = $request->notes;
            $company_code = Company::first()->company_code;
            $general->company_code = $company_code;
            $general->department_code = $request->department_code;
            $general->created_by = Auth::user()->username;
            $general->updated_by = Auth::user()->username;

            // Save the main cash in entry
            $tax_revenue_tariff=0;
            $general->tax_revenue_tariff = 0;

            // Save the details
            $this->savePbrDetails($request->details, $pbr_number, $company_code, $request->department_code,$request->customer_code,$general, $tax_revenue_tariff,'store');

            $id = Pbr::where('pbr_number',$pbr_number)->select('id')->first()->id;

            DB::commit(); // Commit transaction
            return redirect()->route('transaction.pbr.create')->with('success', 'Sales PBR created successfully.')->with('id',$id);




        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            // dd($e);
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to create Sales PBR: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $companies = Company::all();
            $departments = Department::where('department_code', 'DP01')->first();
            $items = ItemSalesPrice::where('department_code','DP01')->whereHas('items')->orderBy('item_code')->distinct('item_code')->with('items', 'unitn','itemDetails','items.warehouses')->get();
            $itemUnits = ItemUnit::all();
            $itemDetails = ItemDetail::where('department_code','DP01')->where('status',true)->get();
            $customers = Customer::orderBy('customer_code','asc')->get();
            $taxs = TaxMaster::all();
            $warehouses = Warehouse::all();

            $pbr = Pbr::with([
            'details' => function ($query) {
                $query->orderBy('id', 'asc');
            },
            'details.items',
            'details.units',
            'details.so'
            ])->findOrFail($id);

            // dd($pbr);
            $department_TDS = 'DP01';
            $department_TDSn = Department::where('department_code', $department_TDS)->first();

            //jika warehouse_code kosong ambil dari master item
            foreach($pbr->details as $sd){
                if(is_null($sd->warehouse_code)){
                    $gd = $items->where("item_code",$sd->item_id)->first();
                    $sd->warehouse_code = $gd->warehouse_code??null;
                }
            }

            // Format dates for display
            $pbr->document_date = Carbon::parse($pbr->document_date)->format('Y-m-d');
            $pbr->delivery_date = Carbon::parse($pbr->delivery_date)->format('Y-m-d');
            $pbr->due_date = Carbon::parse($pbr->due_date)->format('Y-m-d');

            $editable = true;
            $privileges = Auth::user()->roles->privileges['pbr'];
        return view('transaction.pbr.pbr_edit',compact('pbr', 'companies', 'departments', 'items', 'itemUnits', 'itemDetails', 'customers', 'customers', 'taxs', 'department_TDS', 'department_TDSn', 'editable','warehouses','privileges'));
    }

    public function update(Request $request,$id)
    {
        // dd($request->all());
        DB::beginTransaction(); // Start the transaction
        try {
            // Retrieve the Pbr record by pbr_number

            $general = Pbr::where('id', $id)->firstOrFail();
            $pbr_number = $general->pbr_number;
            $general->document_date = $request->document_date;
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
            $general->tax_revenue_tariff = 0;

            $oldDetail  = PbrDetail::where('pbr_number', $pbr_number)->get();

            foreach ($oldDetail as $key => $detail) {
                if($detail['so_id']){
                    $sod = SalesOrderDetail::find($detail['so_id']);
                    $sod->qty_left = $sod->qty_left+$detail['qty'];
                    $sod->save();
                    $so = SalesOrder::where('sales_order_number',$sod->sales_order_number)->first();
                    $so->status = 'Partial';
                    $so->save();
                }
            }
            InventoryDetail::where('document_number', $general->pbr_number)->delete();
            Journal::where('document_number', $general->pbr_number)->delete();
            PbrDetail::where('pbr_number', $general->pbr_number)->delete();
            // Save the details
            $this->savePbrDetails($request->details, $pbr_number, $request->company_code, $request->department_code,$request->customer_code,$general, $tax_revenue_tariff,'update');
            // Parse and assign date fields
            DB::commit(); // Commit the transaction
            return redirect()->route('transaction.pbr')->with('success', 'Sales PBR updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            // dd($e);
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to update Sales PBR: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request,$id)
    {
        DB::beginTransaction();
        try {
            $general = Pbr::findOrFail($id);
            Pbr::where('pbr_number', $general->pbr_number)->delete();
            $siDetails = PbrDetail::where('pbr_number', $general->pbr_number)->get();
            $soNumber = '';
            foreach ($siDetails as $key => $value) {
                if($value->so_id){
                    $poDetail = SalesOrderDetail::where('id',$value->so_id)->first();
                    $poDetail->qty_left = $poDetail->qty_left+$value->qty;
                    $poDetail->save();
                    if(!str_contains($soNumber,$value->sales_order_number)){
                        $soNumber = $soNumber.' '.$value->sales_order_number;
                    }
                    $totalAll = SalesOrderDetail::where('sales_order_number', $poDetail->sales_order_number)->count();

                    $checkLeft = SalesOrderDetail::where('sales_order_number', $poDetail->sales_order_number)->whereColumn('qty_left', 'qty')->count();
                    $so = SalesOrder::where('sales_order_number',$poDetail->sales_order_number)->first();
                    if($so->status!='Cancelled'){
                        if($checkLeft==$totalAll) {
                            $so->update(['status'=> 'Open']);
                        } else {
                            $so->update(['status'=> 'Partial']);
                        }
                    }
                    $so->save();
                }
            }
            PbrDetail::where('pbr_number', $general->pbr_number)->delete();
            $general->delete();
            InventoryDetail::where('document_number', $general->pbr_number)->delete();
            Journal::where('document_number',$general->pbr_number)->delete();
            $reason = $request->input('reason');



            DeleteLog::create([
                'document_number' => $general->pbr_number,
                'document_date' => $general->document_date,
                'delete_notes' => $reason,
                'type' => 'SI',
                'company_code' => $general->company_code,
                'department_code' => $general->department_code,
                'deleted_by' => Auth::user()->username,
            ]);

            DB::commit(); // Commit transaction
            return redirect()->route('transaction.pbr')->with('success', 'Sales PBR deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e);
            return redirect()->route('transaction.pbr')->with('error', 'Error deleting: ' . $e->getMessage());
        }
    }

    public function printPdf($pbr_number)
    {
        $pbr = Pbr::with([
            'customers',
            'details' => function ($query) {
                $query->orderBy('id', 'asc');
            },
            'details.items.itemDetails.unitConversion', // Nested relationships still load
        ])->findOrFail($pbr_number);
        $totalDiscount= 0;
        foreach ($pbr->details as $key => $value) {
            $subtotal = $value->qty*$value->base_qty*$value->price;
            $totalDiscount+= ($subtotal *$value->disc_percent/100) +$value->disc_nominal;
        }

        $totalHuruf = ucfirst($this->numberToWords($pbr->total)).' rupiah';
        $tax = TaxMaster::where('tax_code','PPN')->first();
        return view('transaction.pbr.pbr_pdf',compact('pbr','totalDiscount','totalHuruf','tax'));
    }

    public function summary(Request $request)
    {
        // Initialize query for fetching sales invoices
        $query = Pbr::query();

        // Apply date filtering if 'from_date' and 'to_date' are present in the request
        if ($request->filled('from_date') && $request->filled('to_date')) {
            // Convert dates to Carbon instances for proper formatting and range filtering
            $fromDate = Carbon::parse($request->input('from_date'))->startOfDay();
            $toDate = Carbon::parse($request->input('to_date'))->endOfDay();

            // Apply the date range filter on the 'document_date' column
            $query->whereBetween('document_date', [$fromDate, $toDate]);
        }

        // Retrieve filtered sales invoices
        $pbrs = $query->where('department_code','DP01')->orderBy('id','asc')->get();
        $customers = Customer::where('department_code','DP01')->get();
        // Calculate the total amount from all filtered sales invoices
        $totalAmount = $pbrs->sum('total');
        return view('transaction.pbr.pbr_summary',
        compact('pbrs', 'totalAmount','customers'))
        ->with([
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date')
        ]);
    }

    public function summaryDetail(Request $request)
    {
        // Initialize query for fetching sales invoices
        $query = Pbr::query();

        // Apply date filtering if 'from_date' and 'to_date' are present in the request
        if ($request->filled('from_date') && $request->filled('to_date')) {
            // Convert dates to Carbon instances for proper formatting and range filtering
            $fromDate = Carbon::parse($request->input('from_date'))->startOfDay();
            $toDate = Carbon::parse($request->input('to_date'))->endOfDay();

            // Apply the date range filter on the 'document_date' column
            $query->whereBetween('document_date', [$fromDate, $toDate]);
        }

        // Retrieve filtered sales invoices
        $pbrs = $query->with('details')->where('department_code','DP01')->orderBy('id','asc')->get();
        $customers = Customer::where('department_code','DP01')->get();
        // Calculate the total amount from all filtered sales invoices
        $totalAmount = $pbrs->sum('total');
        return view('transaction.pbr.pbr_summary_detail',compact('pbrs', 'totalAmount','customers'))
        ->with([
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date')
        ]);

    }

    private function generatePbrNumber() {
        // Get today's date components
        $today = now();
        $month = $today->format('n'); // Numeric representation of a month (1-12)
        $year = $today->format('y'); // Last two digits of the year

        // Convert month to Roman numeral
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        $romanMonth = $romanMonths[$month];

        // Fetch the last Sales PBR created
        $lastPbr = Pbr::whereYear('created_at', $today->year)
            ->whereMonth('created_at', $month)
            ->where('department_code','DP01')
            ->orderBy('id', 'desc')
            ->first();

        // Determine the new invoice number
        if ($lastPbr) {
            // Extract the last number from the last invoice number
            $lastNumber = (int)substr($lastPbr->pbr_number, strrpos($lastPbr->pbr_number, '-') + 1);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            // Reset counter to 00001 if no invoices found for the current month
            $newNumber = '00001';
        }

        // Return the new invoice number in the desired format
        return "PBR/{$romanMonth}/{$year}-{$newNumber}";
    }

    function numberToWords($number) {
        $number = floor($number);
        $words = [
            '', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'
        ];

        if ($number < 12) {
            return $words[$number];
        } else if ($number < 20) {
            return $words[$number - 10] . ' belas';
        } else if ($number < 100) {
            $result = $words[floor($number / 10)] . ' puluh ' . $words[$number % 10];
        } else if ($number < 200) {
            $result = 'seratus ' . $this->numberToWords($number - 100);
        } else if ($number < 1000) {
            $result = $words[floor($number / 100)] . ' ratus ' . $this->numberToWords($number % 100);
        } else if ($number < 2000) {
            $result = 'seribu ' . $this->numberToWords($number - 1000);
        } else if ($number < 1000000) {
            $result = $this->numberToWords(floor($number / 1000)) . ' ribu ' . $this->numberToWords($number % 1000);
        } else if ($number < 1000000000) {
            $result = $this->numberToWords(floor($number / 1000000)) . ' juta ' . $this->numberToWords($number % 1000000);
        } else if ($number < 1000000000000) {
            $result = $this->numberToWords(floor($number / 1000000000)) . ' milyar ' . $this->numberToWords($number % 1000000000);
        } else if ($number < 1000000000000000) {
            $result = $this->numberToWords(floor($number / 1000000000000)) . ' triliun ' . $this->numberToWords($number % 1000000000000);
        } else {
            return 'Jumlah terlalu besar';
        }

        // Remove double spaces and trim
        return trim(preg_replace('/\s+/', ' ', $result));
    }


    private function savePbrDetails(array $pbrDetails, $pbr_number, $company_code, $department_code,$customer_code,$general, $tax_revenue_tariff,$type) {
        $nominal = 0;
        $revenueTax = 0;
        $addTax = 0;
        $services = 0;
        $taxed = 0;
        $totalAllAfterDiscountBeforeTax = 0;
        foreach ($pbrDetails as $detail) {



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
                        $totalAllAfterDiscountBeforeTax += (($detail['qty']*$detail['price']) / (1 + $taxs->tax_base* $taxs->tariff / 100)) - ($detail['disc_percent']/100*(($detail['qty']*$detail['price']) / (1 + $taxs->tax_base * $taxs->tariff / 100)))-$detail['disc_nominal'];
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
        foreach ($pbrDetails as $index => $detail) {
            $detail['price'] = str_replace(',', '', $detail['price']);
            $detail['disc_percent'] = str_replace(',', '', $detail['disc_percent']??0);
            $detail['disc_nominal'] = str_replace(',', '', $detail['disc_nominal']??0);
            $detail['nominal'] = str_replace(',', '', $detail['nominal']??0);

            // Ensure index is the same as the row number from the form input
            $detail['pbr_number'] = $pbr_number;
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

            if ($customer->pkp == 1) {
                if (strtolower($itemTax->category->item_category_name) == 'service') {
                    $services += $detail['nominal'];
                }

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
            PbrDetail::create($detail);
            // dd($detail);


            $itemTax = Item::where('department_code','DP01')->where('item_code', $detail['item_id'])->first();

            $firstQ = InventoryDetail::where('item_id', $detail['item_id'])->orderBy('id','desc')->first();

            $firstQty = $firstQ->last_qty??0;


            $crAt = Carbon::now('Asia/Jakarta');
            if(!is_null($general->id)){
                $crAt = $general->created_at;
            }

            $gd = Warehouse::where("warehouse_code",$detail["warehouse_code"])->first();
            $cogs = Module::getCogs($general->document_date,$detail['item_id'],$general->company_code,$general->department_code,$gd->id);

            InventoryDetail::insert([
                'document_number'=>$detail['pbr_number'],
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
                'first_qty'=>$firstQty,
                'last_qty'=>$firstQty-($detail['qty']*$detail['base_qty']),
                'created_at' => $crAt,
                'updated_at' => Carbon::now('Asia/Jakarta'),
                'created_by' => $general->created_by,
                'updated_by' => Auth::user()->username,
                'warehouse_id' => $gd->id,
                'total' => $totalPriceBeforeTaxAfterDiscount * -1,
                'cogs' => ($cogs * ($detail['qty'] * $detail['base_qty'])) * -1,
                'qty_actual' => $detail['qty'] * -1
            ]);


            $itemQtyL = SalesOrderDetail::where('id', $detail['so_id'])->first();
            if(!is_null($itemQtyL)){
                $itemQtyLeft = $itemQtyL->qty_left-$detail['qty'];

                if($itemQtyLeft<0){
                    $itemQtyLeft = 0;
                }
                // dd($itemQtyLeft);
                $itemQtyL->qty_left = $itemQtyLeft;
                $itemQtyL->save();

                $checkLeft = SalesOrderDetail::where('sales_order_number', $detail['sales_order_number'])->where('qty_left', '!=', 0)->count();

                if($checkLeft<=0) {
                    SalesOrder::where('sales_order_number', $detail['sales_order_number'])->update(['status'=> 'Closed']);
                } else {
                    SalesOrder::where('sales_order_number', $detail['sales_order_number'])->update(['status'=> 'Partial']);
                }
            }else{
                if($type=='store'){
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Harap melakukan pembuatan ulang karena ada perubahan pada data permintaan penjualan');
                }
            }
        }

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
        $general->total = $general->subtotal-$general->disc_nominal + $general->add_tax + $general->tax_revenue;

        $general->save();
// dd($general);


       $customers = Customer::where('customer_code', $general->customer_code)->first();
            $taxes = TaxMaster::where('tax_code', $general->tax_revenue_tariff)->first();
            $itemCategory = ItemCategory::first();

            //BIAYA BARANG RUSAK
            $PIJournal = new Journal();
            $PIJournal->document_number = $general->pbr_number;
            $PIJournal->document_date = $general->document_date;
            $PIJournal->account_number = $itemCategory->acc_barang_rusak??'1001';
            $PIJournal->debet_nominal = $general->total;
            $PIJournal->credit_nominal = 0;
            $PIJournal->notes = 'Biaya barang rusak';
            $PIJournal->company_code = $general->company_code;
            $PIJournal->department_code = $general->department_code;
            $PIJournal->created_by = Auth::user()->username;
            $PIJournal->updated_by = Auth::user()->username;
            $PIJournal -> save();

            // PERSEDIAAN
            $PIJournala = new Journal();
            $PIJournala->document_number = $general->pbr_number;
            $PIJournala->document_date = $general->document_date;
            $PIJournala->account_number = $itemCategory->account_inventory??'1001';
            $PIJournala->debet_nominal = 0;
            $PIJournala->credit_nominal = $general->total;
            $PIJournala->notes = 'Persediaan';
            $PIJournala->company_code = $general->company_code;
            $PIJournala->department_code = $general->department_code;
            $PIJournala->created_by = Auth::user()->username;
            $PIJournala->updated_by = Auth::user()->username;
            $PIJournala -> save();

    }
}
