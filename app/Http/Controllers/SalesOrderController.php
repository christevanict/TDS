<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Coa;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Journal;
use App\Models\CategoryCustomer;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\DeleteLog;
use App\Models\DeliveryOrder;
use App\Models\ItemDetail;
use App\Models\ItemCategory;
use App\Models\Department;
use App\Models\SalesOrder;
use App\Models\PurchaseOrder;
use App\Models\SalesOrderDetail;
use App\Models\ItemSalesPrice;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceDetail;
use App\Models\DeliveryOrderDetail;
use App\Models\PbrDetail;
use App\Models\TaxMaster;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SalesOrderController extends Controller
{
    public function cancelDetail(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $detail = SalesOrderDetail::findOrFail($request->detail_id);
            SalesOrderDetail::where('sales_order_number',$detail->sales_order_number)->where('item_id',$detail->item_id)->update([
                'qty_left' => 0,
                'status' => 'Cancelled',
                'updated_by' => Auth::user()->username
            ]);

            // Check if all details are cancelled
            $allDetailsCancelled = SalesOrderDetail::where('sales_order_number', $detail->sales_order_number)
                ->where('qty_left', '>', 0)
                ->doesntExist();

            if ($allDetailsCancelled) {
                SalesOrder::where('sales_order_number', $detail->sales_order_number)
                    ->update([
                        'status' => 'Cancelled',
                        'updated_by' => Auth::user()->username
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Detail cancelled successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling detail: ' . $e->getMessage()
            ], 500);
        }
    }
    public function index() {
        $companies = Company::all();
        $departments = Department::where('department_code', 'DP01')->first();
        $salesOrders = SalesOrder::where('department_code', 'DP01')->orderBy('id','desc')->get();
        if(Auth::user()->role=='RO09')
        {
            $salesOrders = SalesOrder::where('department_code', 'DP01')->whereRelation('customers','sales','ANDRE')->orderBy('id','desc')->get();
        }
        $coas = COA::all();
        $suppliers = Supplier::all();
        $customers = Customer::whereNot(function ($query) {
            $query->where('customer_code', 'like', 'DP02%')
            ->orWhere('customer_code', 'like', 'DP03%');
        })->get();
        $prices = ItemSalesPrice::where('department_code','DP01')->get();
        $taxs = TaxMaster::all();
        $privileges = Auth::user()->roles->privileges['sales_order'];


        return view('transaction.sales-order.sales_order_list', compact('companies', 'departments', 'salesOrders', 'coas', 'suppliers', 'customers', 'prices', 'taxs','privileges'));
    }

    public function summary(Request $request) {
        $companies = Company::all();
        $departments = Department::where('department_code', 'DP01')->first();
        $query = SalesOrder::query();
        if ($request->filled('from_date') && $request->filled('to_date')) {
            // Convert dates to Carbon instances for proper formatting and range filtering
            $fromDate = Carbon::parse($request->input('from_date'))->startOfDay();
            $toDate = Carbon::parse($request->input('to_date'))->endOfDay();

            // Apply the date range filter on the 'document_date' column
            $query->whereBetween('document_date', [$fromDate, $toDate]);
        }else{
            $query->whereDate('document_date',Carbon::today())->get();
        }
        $salesOrders = $query->where('department_code','DP01')->orderBy('id','asc')->get();
        $coas = COA::all();
        $items = Item::where('department_code','DP01')->get();
        $itemUnits = ItemUnit::all();
        $itemDetails = ItemDetail::where('department_code','DP01')->get();
        $suppliers = Supplier::all();
        $customers = Customer::whereNot(function ($query) {
            $query->where('customer_code', 'like', 'DP02%')
                ->orWhere('customer_code', 'like', 'DP03%');
        })->get();
        $prices = ItemSalesPrice::where('department_code','DP01')->get();
        $taxs = TaxMaster::all();
        $privileges = Auth::user()->roles->privileges['sales_order'];


        return view('transaction.sales-order.sales_summary_list', compact('companies', 'departments', 'salesOrders', 'coas', 'items', 'itemUnits','itemDetails', 'suppliers', 'customers', 'prices', 'taxs','privileges'));
    }

    private function generateSalesOrderNumber() {
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

        // Fetch the last sales invoice created
        $lastSalesInvoice = SalesOrder::whereYear('created_at', $today->year)
            ->whereMonth('created_at', $month)
            ->where('department_code','DP01')
            ->orderBy('sales_order_number', 'desc')
            ->first();
            // dd($lastSalesInvoice);

        // Determine the new invoice number
        if ($lastSalesInvoice) {
            // Extract the last number from the last invoice number
            $lastNumber = (int)substr($lastSalesInvoice->sales_order_number, strrpos($lastSalesInvoice->sales_order_number, '-') + 1);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            // Reset counter to 00001 if no invoices found for the current month
            $newNumber = '00001';
        }

        // Return the new invoice number in the desired format
        return "TDS/SAO/{$romanMonth}/{$year}-{$newNumber}";
    }

    private function generateSalesInvoiceNumber() {
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

        // Fetch the last sales invoice created
        $lastSalesInvoice = SalesInvoice::whereYear('created_at', $today->year)
            ->whereMonth('created_at', $month)
            ->orderBy('sales_invoice_number', 'desc')
            ->first();

        // Determine the new invoice number
        if ($lastSalesInvoice) {
            // Extract the last number from the last invoice number
            $lastNumber = (int)substr($lastSalesInvoice->sales_invoice_number, strrpos($lastSalesInvoice->sales_invoice_number, '-') + 1);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            // Reset counter to 00001 if no invoices found for the current month
            $newNumber = '00001';
        }

        // Return the new invoice number in the desired format
        return "TDS/INV/{$romanMonth}/{$year}-{$newNumber}";
    }


    public function create() {
        $companies = Company::all();
        $departments = Department::where('department_code', 'DP01')->first();
        $coas = COA::all();
        $items = ItemSalesPrice::where('department_code','DP01')->whereHas('items')->orderBy('item_code')->distinct('item_code')->with('items', 'unitn','itemDetails')->get();
        $itemUnits = ItemUnit::all();
        $itemDetails = ItemDetail::where('department_code','DP01')->where('status',true)->get();
        $customers = Customer::where('department_code','DP01')->get();
        if(Auth::user()->role=='RO09')
        {
            $customers = Customer::where('sales','ANDRE')->where('department_code','DP01')->get();
        }
        $token = str()->random(16);
        $suppliers = Supplier::all();
        $taxs = TaxMaster::all();
        $department_TDS = 'DP01';
        // dd($department_TDS);
        $department_TDSn = Department::where('department_code', $department_TDS)->first();
        $privileges = Auth::user()->roles->privileges['sales_order'];

        return view('transaction.sales-order.sales_order_input', compact('companies', 'departments', 'coas', 'items', 'itemUnits','itemDetails', 'suppliers', 'customers', 'taxs','department_TDS', 'department_TDSn','privileges','token'));
    }



    public function store(Request $request) {
        $exist = SalesOrder::where('token',$request->token)->where('department_code','DP01')->whereDate('created_at',Carbon::today())->first();
        if($exist){
            $id = SalesOrder::where('created_by',Auth::user()->username)->orderBy('id','desc')->select('id')->first()->id;
            return redirect()->route('transaction.sales_order.create')->with('success', 'Sales Order created successfully.')->with('id',$id);
        }
        DB::beginTransaction(); // Begin transaction to ensure atomicity
        try {
            $sales_order_number = $this->generateSalesOrderNumber();

            if(SalesOrder::where('sales_order_number', $request->sales_order_number)->count() < 1) {

            $general = new SalesOrder();
            $general->sales_order_number = $sales_order_number;
            $general->manual_number = $request->manual_number??'';
            $general->document_date = $request->document_date;
            $general->delivery_date = $request->delivery_date;
            $general->due_date = $request->due_date;
            $general->customer_code = $request->customer_code;
            $general->token = $request->token;
            $general->is_nt = $request->is_nt;
            $general->tax = 'PPN';
            $general->disc_nominal = str_replace(',', '', $request->disc_nominal??0);
            // $general->con_invoice = $request->con_invoice;
            $general->status = 'Open';


            $general->notes = $request->notes;
            $general->company_code = $request->company_code;
            $general->department_code = $request->department_code;
            $general->created_by = Auth::user()->username;
            $general->updated_by = Auth::user()->username;

            // Save the main cash in entry


            // Save the details
            $this->saveSalesOrderDetails($request->details, $sales_order_number, $request->company_code, $request->department_code,$request->customer_code,$general,'in', []);

            $id = SalesOrder::where('sales_order_number',$sales_order_number)->select('id')->first()->id;

            DB::commit(); // Commit transaction
            return redirect()->route('transaction.sales_order.create')->with('success', 'Sales Order created successfully.')->with('id',$id);

            } else {
                return redirect()->back()->with('error', 'Sales Order Number must not be the same');
            };

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to create Sales Order: ' . $e->getMessage());
        }
    }

    public function cancel(Request $request, $id) {
        DB::beginTransaction();
        try {
            $general = SalesOrder::findOrFail($id);
            SalesOrderDetail::where('sales_order_number',$general->sales_order_number)->where('qty_left','>','0')->update(['status'=>'Cancelled','qty_left'=>0]);

            $general->update(['status'=>'Cancelled']);

            $reason = $request->input('reason');

            $general->update(['cancel_notes'=>$reason]);

            DB::commit(); // Commit transaction
            return redirect()->route('transaction.sales_order')->with('success', 'Sales Order cancelled successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->route('transaction.sales_order')->with('error', 'Error canceling: ' . $e->getMessage());
        }
    }

    public function edit($id) {
        try {
            $companies = Company::all();
            $departments = Department::where('department_code', 'DP01')->first();
            $coas = Coa::all();
            $items = ItemSalesPrice::where('department_code','DP01')->whereHas('items')->orderBy('item_code')->distinct('item_code')->with('items', 'unitn','itemDetails')->get();
            $itemUnits = ItemUnit::all();
            $itemDetails = ItemDetail::where('department_code','DP01')->where('status',true)->get();
            $suppliers = Supplier::all();
            $customers = Customer::where('department_code','DP01')->get();
            $prices = ItemSalesPrice::where('department_code','DP01')->get();
            $taxs = TaxMaster::all();
            $department_TDS = 'DP01';
            $department_TDSn = Department::where('department_code', $department_TDS)->first();

            $salesOrder = SalesOrder::with('details')->findOrFail($id);
            // dd($salesOrder);

            $po = SalesInvoiceDetail::where('sales_order_number','like','%' .$salesOrder->sales_order_number. '%')->get();

            $boleh = true;
            foreach ($salesOrder->details as $value) {
                $pbrDetails = PbrDetail::where('so_id',$value->id)->first();
                if($pbrDetails){
                    $boleh=false;
                }
            }


            $editable = count($po)>0 || !$boleh ? false:true;

            if($salesOrder->status=='Cancelled') {
                $editable = false;
            }
            $changeCustomer = $salesOrder->status=='Open'?true:false;

            // Format dates for display
            $salesOrder->document_date = Carbon::parse($salesOrder->document_date)->format('Y-m-d');
            // $salesOrder->eta_date = Carbon::parse($salesOrder->eta_date)->format('Y-m-d');
            $salesOrder->delivery_date = Carbon::parse($salesOrder->delivery_date)->format('Y-m-d');
            $salesOrder->due_date = Carbon::parse($salesOrder->due_date)->format('Y-m-d');

            $privileges = Auth::user()->roles->privileges['sales_order'];

            return view('transaction.sales-order.sales_order_edit', compact('salesOrder', 'companies', 'departments', 'coas', 'items', 'itemUnits', 'itemDetails', 'suppliers', 'customers', 'prices', 'taxs','editable', 'department_TDS', 'department_TDSn','privileges','changeCustomer'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load edit form: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction(); // Start the transaction
        try {
            // Retrieve the SalesOrder record by sales_order_number

            $general = SalesOrder::where('id', $id)->firstOrFail();

            // Parse and assign date fields
            $general->document_date = Carbon::createFromFormat('Y-m-d', $request->document_date);
            $general->delivery_date = Carbon::createFromFormat('Y-m-d', $request->delivery_date);
            $general->due_date = Carbon::createFromFormat('Y-m-d', $request->due_date);

            $general->is_nt = $request->is_nt;
                $general->sales_order_number = $request->sales_order_number;
                $general->manual_number = $request->manual_number;
                $general->document_date = $request->document_date;
                $general->delivery_date = $request->delivery_date;
                $general->due_date = $request->due_date;
                $general->customer_code = $request->customer_code;
                $general->disc_nominal = str_replace(',', '', $request->disc_nominal??0);
                // $general->con_invoice = $request->con_invoice;
                // $general->status = 'Open';

                $general->notes = $request->notes;
                $general->company_code = $request->company_code;
                $general->department_code = $request->department_code;
                $general->updated_by = Auth::user()->username;

                // Save the main cash in entry
                $oldSales = SalesOrderDetail::where('sales_order_number', $general->sales_order_number)->get();
                SalesOrderDetail::where('sales_order_number', $general->sales_order_number)->delete();

                // Save the details
                $this->saveSalesOrderDetails($request->details, $general->sales_order_number, $request->company_code, $request->department_code,$request->customer_code,$general,'up', $oldSales);

            DB::commit(); // Commit the transaction
            return redirect()->route('transaction.sales_order')->with('success', 'Sales Order updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to update Sales Order: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id) {
        DB::beginTransaction();
        try {
            $general = SalesOrder::findOrFail($id);
            SalesOrder::where('sales_order_number', $general->sales_order_number)->delete();
            SalesOrderDetail::where('sales_order_number', $general->sales_order_number)->delete();


            // $genera = SalesInvoice::where('sales_order_number',$general->sales_order_number)->first();
            // SalesInvoiceDetail::where('sales_invoice_number', $genera->sales_invoice_number)->delete();
            // SalesInvoice::where('sales_order_number',$general->sales_order_number)->delete();

            $reason = $request->input('reason');

            DeleteLog::create([
                'document_number' => $general->sales_order_number,
                'document_date' => $general->document_date,
                'delete_notes' => $reason,
                'type' => 'SO',
                'company_code' => $general->company_code,
                'department_code' => $general->department_code,
                'deleted_by' => Auth::user()->username,
            ]);
            $general->delete();
            DB::commit(); // Commit transaction
            return redirect()->route('transaction.sales_order')->with('success', 'Sales Order deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->route('transaction.sales_order')->with('error', 'Error deleting: ' . $e->getMessage());
        }
    }

    private function saveSalesOrderDetails(array $SalesOrderDetails, $sales_order_number, $company_code, $department_code,$customer_code,$general,$check, $oldSales) {
        $nominal = 0;
        $revenueTax = 0;
        $addTax = 0;
        $taxed=0;
        $services=0;
        $totalPriceBeforeTaxBeforeDiscount = 0;
        $totalPriceBeforeTaxAfterDiscount = 0;
        $totalAllAfterDiscountBeforeTax = 0;
        $totalAllItemBeforeTax = 0;
        $totalAllDiscountDetail = 0;

        // dd($oldSales);
        $customer = Customer::where('customer_code', $customer_code)->first();

        foreach ($SalesOrderDetails as $detail) {
            $customer = Customer::where('customer_code', $customer_code)->first();
            $itemTax = Item::where('item_code', $detail['item_id'])->first();
            $taxs = TaxMaster::where('tax_code', $general->tax)->first();
            $detail['price'] = str_replace(',', '', $detail['price']);
            $detail['disc_percent'] = str_replace(',', '', $detail['disc_percent']??0);
            $detail['disc_nominal'] = str_replace(',', '', $detail['disc_nominal']??0);
            $detail['nominal'] = str_replace(',', '', $detail['nominal']??0);

            if($customer->pkp == 1 && strtolower($itemTax->category->item_category_name) != 'service') {
                if($customer->include == 1) {
                    if ($itemTax->additional_tax == 1 ) {
                        $totalAllAfterDiscountBeforeTax += (($detail['qty']*$detail['price']) / (1 + $taxs->tariff / 100)) - ($detail['disc_percent']/100*(($detail['qty']*$detail['price']) / (1 + $taxs->tariff / 100)))-$detail['disc_nominal'];
                    } else {
                        $totalAllAfterDiscountBeforeTax += $detail['qty']*$detail['price'] - ($detail['disc_percent']/100*(($detail['qty']*$detail['price'])))-$detail['disc_nominal'];
                    }
                } else {
                    $totalAllAfterDiscountBeforeTax += $detail['qty']*$detail['price'];
                }
            }else{
                $totalAllAfterDiscountBeforeTax += $detail['qty']*$detail['price'] - ($detail['disc_percent']/100*(($detail['qty']*$detail['price'])))-$detail['disc_nominal'];
            }

        }

        foreach ($SalesOrderDetails as $index => $detail) {
            $detail['price'] = str_replace(',', '', $detail['price']);
            $detail['disc_percent'] = str_replace(',', '', $detail['disc_percent']??0);
            $detail['disc_nominal'] = str_replace(',', '', $detail['disc_nominal']??0);
            $detail['nominal'] = str_replace(',', '', $detail['nominal']??0);
            // Ensure index is the same as the row number from the form input
            $detail['sales_order_number'] = $sales_order_number;
            $detail['number_row'] = $index + 1; // Correctly assign row number
            $detail['company_code'] = $company_code;
            $detail['department_code'] = $department_code;
            $nominal += $detail['qty']*$detail['price']-($detail['disc_percent']/100*$detail['qty']*$detail['price'])-$detail['disc_nominal'];
            $detail['created_by'] = Auth::user()->username;
            $detail['updated_by'] = Auth::user()->username;
            // $detail['unit']=$detail['unit'];
            $item = ItemDetail::where('department_code','DP01')->where('status',true)->where([
                ['unit_conversion', $detail['unit']],
                ['item_code',$detail['item_id']]
                ])->first();

            // dd($item,$detail['unit'],$detail['item_id']);

            $detail['base_qty'] = $item->conversion;
            $detail['qty_left'] = $detail['qty'];

            $detail['base_qty_left'] = $detail['base_qty']*$detail['qty'];
            $detail['base_unit'] = $item->base_unit;
            SalesOrderDetail::create($detail);
            // dd($detail);


        $customer = Customer::where('customer_code', $customer_code)->first();
        $itemTax = Item::where('department_code','DP01')->where('item_code', $detail['item_id'])->first();
        $categories = ItemCategory::where('item_category_code', $itemTax->item_category)->first();
        $taxs = TaxMaster::where('tax_code', $general->tax)->first();

        if ($customer->pkp == 1) {
            if (strtolower($itemTax->category->item_category_name) == 'service') {
                $services += $detail['nominal'];
            }
            if($customer->include == 1) {
                if ($itemTax->additional_tax == 1) {
                    $totalPriceBeforeTaxBeforeDiscount = ($detail['qty']*$detail['price'])/(1 + $taxs->tariff / 100)*$detail['conversion_value'];
                    $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                    $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $general->disc_nominal;
                    $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                    $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                    $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];
                    $taxed += $totalPriceBeforeTaxBeforeDiscount;
                }else{
                    $totalPriceBeforeTaxBeforeDiscount = $detail['qty']*$detail['price']*$detail['conversion_value'];
                    $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                    $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $general->disc_nominal;
                    $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                    $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                    $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];
                }
            } else {
                if ($itemTax->additional_tax == 1) {
                    $totalPriceBeforeTaxBeforeDiscount = $detail['qty']*$detail['price']*$detail['conversion_value'];
                    $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                    $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $general->disc_nominal;
                    $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                    $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                    $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];
                    $taxed += $totalPriceBeforeTaxBeforeDiscount;
                }else{
                    $totalPriceBeforeTaxBeforeDiscount = $detail['qty']*$detail['price']*$detail['conversion_value'];
                    $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                    $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $general->disc_nominal;
                    $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                    $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                    $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];
                }
            }
        } else {
            $revenueTax = 0;
            $addTax = 0;
            $totalPriceBeforeTaxBeforeDiscount = $detail['qty']*$detail['price']*$detail['conversion_value'];
            $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
            $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
            $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $general->disc_nominal;
            $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
        }
        $totalAllItemBeforeTax+=$totalPriceBeforeTaxAfterDiscount;

        }

        if ($customer->pkp == 1) {
            $addTax = $taxed * $taxs->tax_base * $taxs->tariff/100;
        } else {
            $addTax = 0;
        }
        $revenueTax = 0;

        $general->add_tax = $addTax;
        $general->tax_revenue = $revenueTax;

        $general->subtotal = $totalAllItemBeforeTax+$general->disc_nominal;
        $general->total = $general->subtotal -$general->disc_nominal + $general->add_tax + $general->tax_revenue;
        $general->save();
    //    dd($general);

    }

    public function printSalesOrderPDF($sales_order_number)
    {
        // Retrieve the sales order with related data
        $salesOrder = SalesOrder::with([
            'company',
            'department',
            'customers',
            'details.items.itemDetails.unitConversion', // Load items, itemDetails, and unitConversion
        ])->findOrFail($sales_order_number);

        // dd($salesOrder);
        $groupedDetails = $salesOrder->details->groupBy('item_id');
        // Generate and return the PDF
        return view('transaction.sales-order.sales_order_pdf', compact('salesOrder', 'groupedDetails'));

        // Create a safe file name by removing any unwanted characters
        $nameFile = Str::replace("/", "", $salesOrder->sales_order_number); // Ensure you have a valid 'sales_order_number' property

        // Stream the PDF to the browser
        return $pdf->stream("Sales_Order_{$nameFile}.pdf");
    }
    public function printSalesOrderPDFNetto($sales_order_number)
    {
        // Retrieve the sales order with related data
        $salesOrder = SalesOrder::with([
            'company',
            'department',
            'customers',
            'details.items.itemDetails.unitConversion', // Load items, itemDetails, and unitConversion
        ])->findOrFail($sales_order_number);

        // dd($salesOrder);
        $groupedDetails = $salesOrder->details->groupBy('item_id');
        $tax = TaxMaster::where('tax_code','PPN')->first();
        $totalHuruf = ucfirst($this->numberToWords($salesOrder->total)).' rupiah';
        // Generate and return the PDF
        // return view('transaction.sales-order.sales_order_summary_netto_print', compact('salesOrder'));
        return view('transaction.sales-order.sales_summary_netto_pdf', compact('salesOrder', 'groupedDetails','tax','totalHuruf'));

        // Create a safe file name by removing any unwanted characters
        $nameFile = Str::replace("/", "", $salesOrder->sales_order_number); // Ensure you have a valid 'sales_order_number' property

        // Stream the PDF to the browser
        return $pdf->stream("Sales_Order_{$nameFile}.pdf");
    }

    private function formatNumber($number)
    {
        // Ensure that the number is a valid numeric type (float or int)
        $number = is_numeric($number) ? (float) $number : 0;  // Cast to float if it's numeric, else default to 0
        return number_format($number, 2, '.', ',');
    }
    public function printSummary($id)
    {
        // Retrieve the sales order with related data (items, customers, salesOrderDetails, and taxMaster)
        $salesOrder = SalesOrder::with(['items', 'customers', 'details', 'taxs'])->find($id);

        // Debugging: Check if salesOrder and taxMaster are correctly loaded


        if (!$salesOrder) {
            abort(404, 'Sales order not found');
        }

        // Group the sales order details by 'item_id'
        $groupedDetails = $salesOrder->details->groupBy('item_id');
        $totalHuruf = ucfirst($this->numberToWords($salesOrder->total)).' rupiah';
        // Pass the grouped details and taxMaster to the view

        // return view('transaction.sales-order.sales_order_summary_print', compact('salesOrder'));
        return view('transaction.sales-order.sales_summary_pdf', compact('salesOrder', 'groupedDetails','totalHuruf'));

        // Remove "/" from the sales order number for the filename
        $nameFile = Str::replace("/", "", $salesOrder->sales_order_number);

        // Return the PDF as a response
        return $pdf->stream("Sales_Summary_{$nameFile}.pdf");
    }

    public function summaryDetail(Request $request)
    {
        // Initialize query for fetching sales invoices
        $query = SalesOrder::query();


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

        if(Auth::user()->role=='RO09')
        {
            $query = $query->whereRelation('customers','sales','ANDRE');
        }

        // Retrieve filtered sales invoices
        $salesOrders = $query->with('details', 'details.items')
            ->where('department_code', 'DP01')
            ->orderBy('id', 'asc')
            ->get();

        // Group details by item_id and sum qty, qty_left, and nominal
        $salesOrders = $salesOrders->map(function ($salesOrder) {
            $groupedDetails = $salesOrder->details->groupBy('item_id')->map(function ($group) {
                $awal = new DateTime();
                $firstDetail = $group->first();
                $awal->setTimezone(new DateTimeZone('Asia/Jakarta'));
                $gdAll = DB::table("warehouse")->get();
                $gd = $gdAll->where("warehouse_code",$firstDetail->items->warehouse_code)->first();
                $stock = Module::getStockByDate($firstDetail->item_id,$firstDetail->base_qty,$firstDetail->unit,$gd->id,$awal->format('Y-m-d'));
                return [
                    'id' => $firstDetail->id,
                    'items' => $firstDetail->items,
                    'item_id' => $firstDetail->item_id,
                    'qty' => $group->sum('qty'),
                    'qty_left' => $group->sum('qty_left'),
                    'nominal' => $group->sum('nominal'),
                    'base_qty' => $firstDetail->base_qty ?? 1,
                    'price' => $firstDetail->price ?? 0,
                    'stock' => $stock / $firstDetail->base_qty ?? 0,
                    'disc_nominal' => $firstDetail->disc_nominal ?? 0,
                    'disc_percent' => $firstDetail->disc_percent ?? 0,
                    'status'=>$firstDetail->status
                ];
            })->values();

            $salesOrder->details = $groupedDetails;
            return $salesOrder;
        });

        // Apply status filter if provided
        $statusFilter = $request->input('status');
        if ($statusFilter) {
            $salesOrders = $salesOrders->map(function ($salesOrder) use ($statusFilter) {
                $filteredDetails = $salesOrder->details->filter(function ($detail) use ($statusFilter) {
                    $status = $detail['qty'] == $detail['qty_left'] ? 'Open' : ($detail['qty_left'] == 0 ? 'Closed' : 'Partial');
                    return $status === $statusFilter;
                })->values();

                $salesOrder->details = $filteredDetails;
                return $salesOrder;
            })->filter(function ($salesOrder) {
                // Remove sales orders with no details after filtering
                return $salesOrder->details->isNotEmpty();
            })->values();
        }

        // Calculate the total amount from all filtered sales orders' details
        $totalAmount = $salesOrders->flatMap(function ($salesOrder) {
            return $salesOrder->details;
        })->sum('nominal');
        $customers = Customer::where('department_code','DP01')->get();
        $privileges = Auth::user()->roles->privileges['sales_order'];

        // Return the view with the sales invoices data and total amount
        return view('transaction.sales-order.sales_order_summary_detail', compact('salesOrders', 'totalAmount','customers','privileges'))
            ->with([
                'from_date' => $request->input('from_date'),
                'to_date' => $request->input('to_date')
            ]);
    }

    public function getCustomerItem(Request $request)
    {
        // Fetch sales order details
            $soDetails = SalesOrderDetail::where('item_id', $request->item_id)
            ->whereHas('so', function ($query) use ($request) {
                $query->where('customer_code', $request->customer_code)
                      ->where('department_code', 'DP01');
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($detail) {
                $formattedDate = Carbon::parse($detail->so->document_date)->format('d M Y');
                return [
                    'sales_order_number' => $detail->so->sales_order_number ?? '-', // Adjust based on your column name
                    'document_date' => $formattedDate ?? '-', // Adjust based on your column name
                    'price' => $detail->price ?? 0, // Adjust based on your column name
                ];
            });

        // Return JSON response
        return response()->json($soDetails);
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
}
