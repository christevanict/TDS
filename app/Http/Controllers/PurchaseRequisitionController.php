<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionDetail;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Journal;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceDetail;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceDetail;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\DeleteLog;
use App\Models\ItemUnit;
use App\Models\ItemDetail;
use App\Models\ItemSalesPrice;
use App\Models\ItemPurchase;
use App\Models\TaxMaster;
use App\Models\Debt;
use App\Models\Receivable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PurchaseRequisitionController extends Controller
{
    public function index()
    {
        $purchaseRequisitions = PurchaseRequisition::orderBy('id','desc')->get();

        return view('transaction.purchase-requisition.purchase_requisition_list', [
            'purchaseRequisitions' => $purchaseRequisitions,
        ]);
    }


    public function create(Request $request)
    {
        // Fetch sales orders with status 'Not' or 'Not Ready'
        // $salesOrders = SalesOrder::where(function ($query) {
        //     $query->where('status', 'Not')
        //         ->orWhere('status', 'Not Ready')
        //         ->orWhere('status_reimburse', 'Not');
        // })
        // ->orderBy('sales_order_number', 'asc')
        // ->get();

        // Set the selected sales order number
        $selectedSalesOrderNumber = $request->input('sales_order_number');

        // Fetch related data needed for the view
        $suppliers = Supplier::orderBy('supplier_code', 'asc')->get();
        $currencies = Currency::orderBy(
            'currency_code',
            'asc'
        )->get();

        $departments = Department::where('department_code', 'DP01')->first();

        $items = ItemPurchase::where('department_code','DP01')->with('items', 'unitn')->whereHas('items.category', function($query) {
            $query->where('item_category_name', '!=', 'Service');
        })->get();
// dd($items);
        // Additional data retrieval for the view
        $itemUnits = ItemUnit::orderBy('unit', 'asc')->get();
        $itemDetails = ItemDetail::where('department_code','DP01')->orderBy('item_code', 'asc')->get();
        $prices = ItemPurchase::where('department_code','DP01')->orderBy(
            'item_code',
            'asc'
        )->get();
        $company = Company::first();

        // Optionally, fetch tax rates if necessary
        $taxs = TaxMaster::orderBy('tariff', 'asc')->get();

        // Return view with all necessary data
        return view('transaction.purchase-requisition.purchase_requisition_input', compact(
            // 'salesOrders',
            'suppliers',
            'currencies',
            'departments',
            'items',
            'itemDetails',
            'itemUnits',
            'prices',
            'company',
            // 'purchaseRequisitionNumber',
            'taxs',
            'selectedSalesOrderNumber' // Pass the selected sales order number to the view
        ));
    }

    public function fetchItems(Request $request)
    {
        $salesOrderNumber = $request->query('sales_order_number');
        $items = Item::where('department_code','DP01')->whereHas('salesOrderDetails', function ($query) use ($salesOrderNumber) {
            $query->where('sales_order_number', $salesOrderNumber)
                ->where('status', '!=', 'Ready')->where('qty_left', '>', '0');
        })->get();
        $salesOrderDetail = SalesOrderDetail::where('sales_order_number',$salesOrderNumber)->get();
        $so = SalesOrder::where('sales_order_number',$salesOrderNumber)->first();

        return response()->json(
            ['item'=>$items,
            'so'=>$salesOrderDetail,
            'status_reimburse'=>$so->status_reimburse,
        ]);
    }

    public function store(Request $request)
    {
        // dd($request);
        // Generate a new purchase order number
        $purchaseRequisitionNumber = $this->generatePurchaseRequisitionNumber();
        DB::beginTransaction();
        try {
            // Generate a new PurchaseRequisition instance
            $purchaseRequisition = new PurchaseRequisition();
            $purchaseRequisition->purchase_requisition_number = $purchaseRequisitionNumber;
            $purchaseRequisition->supplier_code = $request->supplier_code;
            $purchaseRequisition->document_date = $request->document_date;
            // $purchaseRequisition->delivery_date = $request->delivery_date;
            // $purchaseRequisition->due_date = $request->due_date;
            // Assign default values for discount
            // $purchaseRequisition->disc_percent = $request->disc_percent ?? 0;
            // $purchaseRequisition->disc_nominal = $request->disc_nominal ?? 0;
            $purchaseRequisition->notes = $request->notes ?? '';
            // Set created_by and updated_by to the logged-in user
            $userId = Auth::id();
            $purchaseRequisition->created_by = $userId;
            $purchaseRequisition->updated_by = $userId;
            $purchaseRequisition->status= 'Not';
            // Set tax from the selected dropdown
            // $purchaseRequisition->tax = $request->tax;
            // Finalize total
            // $purchaseRequisition->company_code = $request->company_code ?? null;
            $purchaseRequisition->department_code = $request->department_code ?? null;
            // $purchaseRequisition->include = $request->include ?? false;
            // $purchaseRequisition->save();
            $total = 0;
            // $revenueTax = 0;
            // $addTax = 0;
            // Process purchase order details
            if (isset($request->details) && is_array($request->details)) {
                // $rowNumber = 1; // Initialize row number for purchase order details
                // dd($request->details);
                foreach ($request->details as $detail) {
                    $detail['purchase_requisition_number'] = $purchaseRequisitionNumber;
                    // $detail['number_row'] = $rowNumber; // Correctly assign row number
                    // $detail['company_code'] = $request->company_code;
                    $detail['department_code'] = $request->department_code;
                    $total += $detail['qty'];
                    $detail['created_by'] = Auth::user()->username;
                    $detail['updated_by'] = Auth::user()->username;
                    $detail['status']= 'Not';
                    $item = ItemDetail::where('department_code','DP01')->where([
                        ['unit_conversion', $detail['unit']],
                        ['item_code',$detail['item_id']]
                        ])->first();
                    $detail['base_qty'] = $item->conversion;
                    $detail['qty_left'] = $detail['qty'];
                    $detail['base_qty_left'] = $detail['base_qty'];
                    $detail['base_unit'] = $item->base_unit;
                    $detail['status'] = 'Not';
                    PurchaseRequisitionDetail::create($detail);
                    // $rowNumber++;
// dd($detail);

                }
            }

            $purchaseRequisition->total = $total;

            $purchaseRequisition->save();
            // dd($purchaseRequisition);



            DB::commit();
            return redirect()->route('transaction.purchase_requisition')->with('success', 'Purchase Requisition created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            // Log the error for further analysis
            Log::error('Error creating Purchase Requisition: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create Purchase Requisition: ' . $e->getMessage())->withInput();
        }
    }



    private function calculateSubtotal(array $details)
    {
        return array_reduce($details, function ($subtotal, $detail) {
            return $subtotal + ($detail['qty'] * $detail['price']);
        }, 0);
    }

    private function generatePurchaseRequisitionNumber() {
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
        $department = 'DP01';

        // Fetch the last purchase order created in the current month
        $lastPurchaseRequisition = PurchaseRequisition::whereYear('created_at', $today->year)
            ->whereMonth('created_at', $month)
            ->whereRaw("SUBSTRING(purchase_requisition_number,5,4) = '".$department."'")
            ->orderBy('id', 'desc')
            ->first();
        // dd($lastPurchaseRequisition);

        // Determine the new purchase order number
        if ($lastPurchaseRequisition) {
            // Extract the last number from the last purchase order number
            $lastNumber = (int)substr($lastPurchaseRequisition->purchase_requisition_number, strrpos($lastPurchaseRequisition->purchase_requisition_number, '-') + 1);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            // Reset counter to 00001 if no purchase orders found for the current month
            $newNumber = '00001';
        }

        // Return the new purchase order number in the desired format
        return "TDS/{$department}/PUR/{$romanMonth}/{$year}-{$newNumber}";
    }

    private function generatePurchaseInvoiceNumber() {
        $today = now();
        $month = $today->format('n'); // Numeric representation of a month (1-12)
        $year = $today->format('y'); // Last two digits of the year

        // Convert month to Roman numeral
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        $romanMonth = $romanMonths[$month];

        // Fetch the last purchase order created in the current month
        $lastPurchaseRequisition = PurchaseInvoice::whereYear('created_at', $today->year)
            ->whereMonth('created_at', $month)
            ->orderBy('purchase_invoice_number', 'desc')
            ->first();

        // Determine the new purchase order number
        if ($lastPurchaseRequisition) {
            // Extract the last number from the last purchase order number
            $lastNumber = (int)substr($lastPurchaseRequisition->purchase_requisition_number, strrpos($lastPurchaseRequisition->purchase_requisition_number, '-') + 1);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            // Reset counter to 00001 if no purchase orders found for the current month
            $newNumber = '00001';
        }

        // Return the new purchase order number in the desired format
        return "TDS/PUI/{$romanMonth}/{$year}-{$newNumber}";
    }


    private function calculateTaxRevenue(array $details, $taxRate)
    {
        $taxRevenue = 0;
        foreach ($details as $detail) {
            $itemId = Item::where('department_code','DP01')->where('item_code', $detail['item_code'])->value('id');
            if ($itemId) {
                // Assuming each detail has the qty and price
                $taxRevenue += ($detail['qty'] * $detail['price']) * $taxRate; // Adjust this logic based on how tax revenue is calculated
            }
        }
        return $taxRevenue;
    }

    public function edit($id)
    {
        // Fetch the existing purchase order
        $purchaseRequisition = PurchaseRequisition::findOrFail($id);
        $purchaseRequisitionNumber = $purchaseRequisition->purchase_requisition_number; // Get the existing order number for the edit view

        // Format document_date and delivery_date using Carbon
        $purchaseRequisition->document_date = Carbon::parse($purchaseRequisition->document_date)->format('Y-m-d'); // or any format you prefer
        $purchaseRequisition->delivery_date = Carbon::parse($purchaseRequisition->delivery_date)->format('Y-m-d'); // or any format you prefer
        $purchaseRequisition->due_date = Carbon::parse($purchaseRequisition->due_date)->format('Y-m-d');

        // Fetch related sales orders


        // Fetch purchase order details
        $purchaseRequisitionDetails = PurchaseRequisitionDetail::where('purchase_requisition_number', $purchaseRequisitionNumber)->with('items')->with('units')->get();
        // dd($purchaseRequisitionDetails);
        $salesOrderDetail = SalesOrderDetail::where('sales_order_number',$purchaseRequisition->sales_order_number)->get();
        $generate = PurchaseOrderDetail::where('purchase_requisition_number', $purchaseRequisitionNumber)->get();
        $editable = count($generate)>0 ? false:true;

        $itemDetails = ItemDetail::where('department_code','DP01')->orderBy('item_code', 'asc')->get();
        $prices = ItemPurchase::where('department_code','DP01')->orderBy(
            'item_code',
            'asc'
        )->get();


        // Fetch other related data needed for the view
        $suppliers = Supplier::orderBy('supplier_code', 'asc')->get();
        $currencies = Currency::orderBy('currency_code', 'asc')->get();
        $departments = Department::orderBy('department_code', 'asc')->get();

        $items = ItemPurchase::where('department_code','DP01')->with('items', 'unitn')->whereHas('items.category', function($query) {
            $query->where('item_category_name', '!=', 'Service');
        })->get();
        // dd($items);
        $itemUnits = ItemUnit::orderBy('unit', 'asc')->get();
        $itemDetails = ItemDetail::where('department_code','DP01')->orderBy('item_code', 'asc')->get();
        $company = Company::first();
        $taxs = TaxMaster::orderBy('tariff', 'asc')->get(); // Fetch tax rates

        $selectedSalesOrderNumber = $purchaseRequisition->sales_order_number ?? null;
        $salesOrders = SalesOrder::where('sales_order_number',$selectedSalesOrderNumber)
            ->first();
        // Return the view with the existing purchase order and related data
        return view('transaction.purchase-requisition.purchase_requisition_edit', compact(
            'salesOrders',
            'purchaseRequisition',
            'purchaseRequisitionDetails',
            'salesOrderDetail',
            'suppliers',
            'currencies',
            'departments',
            'items',
            'itemUnits',
            'itemDetails',
            'prices',
            'company',
            'purchaseRequisitionNumber', // Pass the existing purchase order number to the view
            'taxs',
            'editable',
            // 'formattedDocumentDate', // Pass formatted document date to the view
            // 'formattedDeliveryDate' // Pass formatted delivery date to the view
        ));
    }

    public function generate(Request $request, $id)
    {
        $purchaseInvoiceNumber = $this->generatePurchaseInvoiceNumber();
        DB::beginTransaction(); // Start the transaction
        try {

            DB::commit(); // Commit the transaction
            return redirect()->route('transaction.purchase_requisition')->with('success', 'Purchase Invoice generated successfully.');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            // dd($e);
            Log::error('Failed to generate Invoice: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate Invoice: ' . $e->getMessage());
        }

    }


    public function update(Request $request, $id)
    {
        DB::beginTransaction(); // Start the transaction
        try {
            // Retrieve the PurchaseRequisition record by its ID
            $purchaseRequisition = PurchaseRequisition::findOrFail($id);
            // General fields
            $purchaseRequisition->notes = $request->notes;
            $purchaseRequisition->updated_by = Auth::user()->username;
            $purchaseRequisition->document_date = $request->document_date;
            // Clear existing purchase order details
            PurchaseRequisitionDetail::where('purchase_requisition_number', $purchaseRequisition->purchase_requisition_number)->delete();


            // Save the updated purchase order details
            $this->savePurchaseRequisitionDetails($request->details, $purchaseRequisition);

            DB::commit(); // Commit the transaction
            return redirect()->route('transaction.purchase_requisition')->with('success', 'Purchase Requisition updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            // dd($e);
            Log::error('Failed to update Purchase Requisition: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update Purchase Requisition: ' . $e->getMessage());
        }
    }

    private function savePurchaseRequisitionDetails(array $details, $purchaseRequisition)
    {
        $total = 0;
        foreach ($details as $index => $detail) {
            // Prepare each detail row
            $newDetail = [
                'purchase_requisition_number' => $purchaseRequisition->purchase_requisition_number,
                'number_row' => $index + 1,
                'item_id' => $detail['item_id'],
                'qty' => $detail['qty'],
                'qty_left' => $detail['qty'],
                'unit' => $detail['unit'],
                'department_code' => $purchaseRequisition->department_code,
                'created_by' => Auth::user()->username,
                'updated_by' => Auth::user()->username,
            ];


            $item = ItemDetail::where('department_code','DP01')->where([
                ['unit_conversion', $detail['unit']],
                ['item_code',$detail['item_id']]
                ])->first();
            $newDetail['base_qty'] = $item->conversion;
            $newDetail['base_unit'] = $item->base_unit;
            $newDetail['status'] = 'Not';

            $newDetail['notes'] = $detail['notes'];
            PurchaseRequisitionDetail::create($newDetail);
            // dd($newDetail);


            $total +=$newDetail['qty'];

            // Add to totals for tax and nominal calculations

            // Calculate additional tax if the supplier is PKP and item has additional tax

        }

        $purchaseRequisition->total = $total;

        $purchaseRequisition->save();
// dd($purchaseRequisition);

    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $general = PurchaseRequisition::findOrFail($id);
            PurchaseRequisition::where('id', $id)->delete();
            PurchaseRequisitionDetail::where('purchase_requisition_number', $general->purchase_requisition_number)->delete();
            $general->delete();

            $reason = $request->input('reason');

            DeleteLog::create([
                'document_number' => $general->purchase_requisition_number,
                'document_date' => $general->document_date,
                'delete_notes' => $reason,
                'type' => 'PR',
                'company_code' => $general->company_code,
                'department_code' => $general->department_code,
                'deleted_by' => Auth::user()->username,
            ]);


            DB::commit();
            return redirect()->route('transaction.purchase_requisition')->with('success', 'Purchase Requisition deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error('Failed to delete Purchase Requisition: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete Purchase Requisition: ' . $e->getMessage());
        }
    }

    public function summary(Request $request)
    {
        // Initialize query for fetching purchase invoices
        $query = PurchaseRequisition::query();

        // Apply date filtering if 'from_date' and 'to_date' are present in the request
        if ($request->filled('from_date') && $request->filled('to_date')) {
            // Convert dates to Carbon instances for proper formatting and range filtering
            $fromDate = Carbon::parse($request->input('from_date'))->startOfDay();
            $toDate = Carbon::parse($request->input('to_date'))->endOfDay();

            // Apply the date range filter on the 'document_date' column
            $query->whereBetween('document_date', [$fromDate, $toDate]);
        }

        // Retrieve filtered purchase invoices
        $purchaseRequisitions = $query->get();

        // Calculate the total amount from all filtered purchase invoices
        $totalAmount = $purchaseRequisitions->sum('total');

        // Return the view with the purchase invoices data and total amount
        return view('transaction.purchase-requisition.purchase_requisition_summary', compact('purchaseRequisitions', 'totalAmount'))
        ->with([
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date')
        ]);
    }

}
