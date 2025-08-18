<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Journal;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseInvoiceDetail;
use App\Models\PurchaseRequisitionDetail;
use App\Models\SalesOrder;
use App\Models\GoodReceiptDetail;
use App\Models\SalesOrderDetail;
use App\Models\SalesInvoice;
use App\Models\DeleteLog;
use App\Models\SalesInvoiceDetail;
use App\Models\InventoryDetail;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemUnit;
use App\Models\ItemDetail;
use App\Models\ItemSalesPrice;
use App\Models\ItemPurchase;
use App\Models\TaxMaster;
use App\Models\Debt;
use App\Models\Receivable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $purchaseOrders = PurchaseOrder::where('department_code', 'DP01')->orderBy('id','desc')->get();
        $privileges = Auth::user()->roles->privileges['purchase_order'];
        return view('transaction.purchase-order.purchase_order_list', compact('purchaseOrders','privileges'));
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


        // Fetch related data needed for the view
        $suppliers = Supplier::where('department_code','DP01')->get();
        // $currencies = Currency::orderBy(
        //     'currency_code',
        //     'asc'
        // )->get();

        $departments = Department::where('department_code', 'DP01')->first();
        $token = str()->random(16);

        // $itemIds = PurchaseRequisitionDetail::where('purchase_requisition_number', 'TDS/PR/XI/24-00001')->pluck('item_id');

        $items = ItemPurchase::where('department_code','DP01')->whereHas('items')->orderBy('item_code')->distinct('item_code')->with('items','unitn','itemDetails')->get();

        // dd($items);
        // Additional data retrieval for the view
        $itemUnits = ItemUnit::orderBy('unit', 'asc')->get();
        $itemDetails = ItemDetail::where('department_code','DP01')->where('status',true)->orderBy('item_code', 'asc')->get();
        $prices = ItemPurchase::where('department_code','DP01')->orderBy(
            'item_code',
            'asc'
        )->get();
        $company = Company::first();


        $purchaseRequisition = PurchaseRequisition::with('department')->orderBy('id', 'desc')->where('status', 'Not')->where('department_code', 'DP01')->get();
        $purchaseRequisitionD = PurchaseRequisitionDetail::orderBy('id', 'asc')->with(['items','units'])->get();

        // Optionally, fetch tax rates if necessary
        $taxs = TaxMaster::orderBy('tariff', 'asc')->get();
        $privileges = Auth::user()->roles->privileges['purchase_order'];
        // Return view with all necessary data
        return view('transaction.purchase-order.purchase_order_input', compact(
            // 'salesOrders',
            'suppliers',
            'departments',
            'items',
            'itemDetails',
            'purchaseRequisition',
            'purchaseRequisitionD',
            'itemUnits',
            'prices',
            'company',
            'privileges',
            'taxs',
            'token',
        ));
    }

    // public function fetchItems(Request $request)
    // {
    //     $salesOrderNumber = $request->query('sales_order_number');
    //     $items = Item::whereHas('salesOrderDetails', function ($query) use ($salesOrderNumber) {
    //         $query->where('sales_order_number', $salesOrderNumber)
    //             ->where('status', '!=', 'Ready')->where('qty_left', '>', '0');
    //     })->get();
    //     $salesOrderDetail = SalesOrderDetail::where('sales_order_number',$salesOrderNumber)->get();
    //     $so = SalesOrder::where('sales_order_number',$salesOrderNumber)->first();

    //     return response()->json(
    //         ['item'=>$items,
    //         'so'=>$salesOrderDetail,
    //         'status_reimburse'=>$so->status_reimburse,
    //     ]);
    // }

    public function printPDF($purchase_order_number)
    {
        $purchaseOrders = PurchaseOrder::with([
            'details.items',
            'details.units',
            'company',
            'department',
            'suppliers',
            'taxs',
            'users',
        ])->where('id', $purchase_order_number)->firstOrFail();

        $groupedItems = [];
        // Loop through each detail in the purchase order
        foreach ($purchaseOrders->details as $detail) {
        // dd($detail);
                // Use item name as the key
                $key = $detail->items->item_name;
                $totalDisc = (($detail->disc_percent/100) *($detail->price*$detail->qty))+$detail->disc_nominal;
                // Initialize the item in the grouped array if it doesn't exist
                if (!isset($groupedItems[$key])) {
                    $groupedItems[$key] = [
                        'name' => $key,
                        'quantity' => 0,
                        'price' => $detail->price,
                        'discount' => $totalDisc,
                        'barcode' => $detail->itemDetail->barcode,
                        'base' => $detail->itemDetail->baseUnit->unit_name,
                        'unit' => $detail->units->unit_name,
                        'conversion' => $detail->base_qty,
                        'discp' => 0,
                        'disc' => 0,
                        'total' => 0,
                    ];
                }

                // if($detail->disc_percent > 0) {
                //     $groupedItems[$key]['discp'] = $detail->disc_percent;
                // }
                // $groupedItems[$key]['disc'] += $detail->disc_nominal;
                // Update the quantity and total price for the item
                $groupedItems[$key]['quantity'] += $detail->qty;
                $groupedItems[$key]['total'] += ($detail->qty * $detail->price - $totalDisc) ;

                // Rumus jika perlu: - $detail->disc_nominal - ($detail->qty * $detail->price * $detail->disc_percent / 100)

        }

            // $totalHuruf = $this->numberToWords($purchaseOrders->total);
            // Generate and return PDF
            return view('transaction.purchase-order.purchase_order_pdf', compact('purchaseOrders', 'groupedItems'));
            $nameFile = Str::replace("/", "", $purchaseOrders->purchase_order_number);
            return $pdf->stream("Purchase_Order_{$nameFile}.pdf");

    }

    public function printPDFNetto($purchase_order_number)
    {
        $purchaseOrders = PurchaseOrder::with([
            'details.items',
            'details.units',
            'company',
            'department',
            'suppliers',
            'taxs',
            'users',
        ])->where('id', $purchase_order_number)->firstOrFail();

        $groupedItems = [];
        // Loop through each detail in the purchase order
        foreach ($purchaseOrders->details as $detail) {
        // dd($detail);
                // Use item name as the key
                $key = $detail->items->item_name;
                $totalDisc = (($detail->disc_percent/100) *($detail->price*$detail->qty))+$detail->disc_nominal;
                // Initialize the item in the grouped array if it doesn't exist
                if (!isset($groupedItems[$key])) {
                    $groupedItems[$key] = [
                        'name' => $key,
                        'quantity' => 0,
                        'price' => $detail->price,
                        'discount' => $totalDisc,
                        'barcode' => $detail->itemDetail->barcode,
                        'base' => $detail->itemDetail->baseUnit->unit_name,
                        'unit' => $detail->units->unit_name,
                        'conversion' => $detail->base_qty,
                        'discp' => 0,
                        'disc' => 0,
                        'total' => 0,
                    ];
                }

                // if($detail->disc_percent > 0) {
                //     $groupedItems[$key]['discp'] = $detail->disc_percent;
                // }
                // $groupedItems[$key]['disc'] += $detail->disc_nominal;
                // Update the quantity and total price for the item
                $groupedItems[$key]['quantity'] += $detail->qty;
                $groupedItems[$key]['total'] += ($detail->qty * $detail->price - $totalDisc) ;

                // Rumus jika perlu: - $detail->disc_nominal - ($detail->qty * $detail->price * $detail->disc_percent / 100)

        }

            // $totalHuruf = ucfirst($this->numberToWords($purchaseOrders->total)).' rupiah';
            // Generate and return PDF
            return view('transaction.purchase-order.purchase_order_netto_pdf', compact('purchaseOrders', 'groupedItems'));
            $nameFile = Str::replace("/", "", $purchaseOrders->purchase_order_number);
            return $pdf->stream("Purchase_Order_{$nameFile}.pdf");

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


    public function store(Request $request)
    {
        $exist = PurchaseOrder::where('token',$request->token)->where('department_code','DP01')->whereDate('created_at',Carbon::today())->first();
        if($exist){
            $id = PurchaseOrder::where('created_by',Auth::user()->username)->orderBy('id','desc')->select('id')->first()->id;
            return redirect()->route('transaction.purchase_order.create')->with('success', 'Purchase Order created successfully.')->with('id',$id);
        }
        // Generate a new purchase order number
        $purchaseOrderNumber = $this->generatePurchaseOrderNumber();
        DB::beginTransaction();
        try {
            // Generate a new PurchaseOrder instance
            $purchaseOrder = new PurchaseOrder();
            $purchaseOrder->purchase_order_number = $purchaseOrderNumber;
            $purchaseOrder->purchase_requisition_number = $request->purchase_requisition_number;
            $purchaseOrder->supplier_code = $request->supplier_code;
            $purchaseOrder->document_date = $request->document_date;
            $purchaseOrder->delivery_date = $request->delivery_date;
            $purchaseOrder->due_date = $request->due_date;
            $purchaseOrder->token = $request->token;
            // Assign default values for discount
            $purchaseOrder->disc_percent = $request->disc_percent ?? 0;
            $purchaseOrder->disc_nominal = str_replace(',', '', $request->disc_nominal??0);
            $purchaseOrder->notes = $request->notes ?? '';
            // Set created_by and updated_by to the logged-in user
            $userId = Auth::id();
            $purchaseOrder->created_by = $userId;
            $purchaseOrder->updated_by = $userId;
            // Set tax from the selected dropdown
            $purchaseOrder->tax = 'PPN';
            $purchaseOrder->status = 'Open';
            // Finalize total
            $purchaseOrder->company_code = $request->company_code ?? null;
            $purchaseOrder->department_code = 'DP01';
            $purchaseOrder->include = $request->include ?? false;
            // $purchaseOrder->save();
            $purchaseOrder->tax_revenue_tariff = $request->tax_revenue;
            $tax_revenue_tariff=0;
            if($request->tax_revenue!=0){
                $tax_revenue_tariffs = TaxMaster::where('tax_code',$request->tax_revenue)->first();
                $tax_revenue_tariff = $tax_revenue_tariffs->tariff;
            }


            $nominal = 0;
            $revenueTax = 0;
            $addTax = 0;
            $services = 0;
            $taxed = 0;
            $totalAllAfterDiscountBeforeTax = 0;

            // Process purchase order details
            if (isset($request->details) && is_array($request->details)) {
                $rowNumber = 1; // Initialize row number for purchase order details
                // dd($request->details);
                foreach ($request->details as $detail) {
                    $detail['price'] = str_replace(',', '', $detail['price']);
                    $detail['disc_percent'] = str_replace(',', '', $detail['disc_percent']??0);
                    $detail['disc_nominal'] = str_replace(',', '', $detail['disc_nominal']??0);
                    $detail['nominal'] = str_replace(',', '', $detail['nominal']??0);
                    $supplier = Supplier::where('supplier_code', $purchaseOrder->supplier_code)->first();
                    $itemTax = Item::where('department_code','DP01')->where('item_code', $detail['item_id'])->first();
                    $taxs = TaxMaster::where('tax_code', $purchaseOrder->tax)->first();

                    if($supplier->pkp == 1 && strtolower($itemTax->category->item_category_name) != 'service') {
                        if($supplier->include == 1) {
                            if ($itemTax->additional_tax == 1 ) {
                                $totalAllAfterDiscountBeforeTax += (($detail['qty']*$detail['price']) / (1 + $taxs->tariff / 100)) - ($detail['disc_percent']/100*(($detail['qty']*$detail['price']) / (1 + $taxs->tariff / 100)))-$detail['disc_nominal'];
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
                // dd($totalAllAfterDiscountBeforeTax);
                $totalAllItemBeforeTax = 0;
                $totalAllDiscountDetail = 0;




                foreach ($request->details as $detail) {
                    $detail['price'] = str_replace(',', '', $detail['price']);
                    $detail['disc_percent'] = str_replace(',', '', $detail['disc_percent']??0);
                    $detail['disc_nominal'] = str_replace(',', '', $detail['disc_nominal']??0);
                    $detail['nominal'] = str_replace(',', '', $detail['nominal']??0);
                $detail['purchase_order_number'] = $purchaseOrderNumber;
                $detail['number_row'] = $rowNumber; // Correctly assign row number
                $detail['company_code'] = $request->company_code;
                $detail['department_code'] = 'DP01';
                $nominal += $detail['qty']*$detail['price']-($detail['disc_percent']/100*$detail['qty']*$detail['price'])-$detail['disc_nominal'];
                $detail['nominal'] = $detail['qty']*$detail['price']-($detail['disc_percent']/100*$detail['qty']*$detail['price'])-$detail['disc_nominal'];
                $detail['created_by'] = Auth::user()->username;
                $detail['updated_by'] = Auth::user()->username;
                $detail['unit']=$detail['unit'];


                    // dd('a');
                    $item = ItemDetail::where('department_code','DP01')->where('status',true)->where([
                        ['unit_conversion', $detail['unit']],
                        ['item_code',$detail['item_id']]
                        ])->first();
                    $detail['base_qty'] = $item->conversion;
                    $detail['qty_left'] = $detail['qty'];
                    $detail['base_qty_left'] = $detail['base_qty']*$detail['qty'];
                    $detail['base_unit'] = $item->base_unit;
                    $detail['status'] = 'Not';
                    $detail['description'] = '';

                PurchaseOrderDetail::create($detail);
        // dd($detail);


                    $supplier = Supplier::where('supplier_code', $purchaseOrder->supplier_code)->first();
                    $itemTax = Item::where('department_code','DP01')->where('item_code', $detail['item_id'])->first();
                    $taxs = TaxMaster::where('tax_code', $purchaseOrder->tax)->first();


                    // if ($supplier->pkp == 1) {
                    //     if (strtolower($itemTax->category->item_category_name) == 'service') {
                    //         $services += $detail['nominal'];
                    //     }

                    //     if($supplier->include == 1) {
                    //         if ($itemTax->additional_tax == 1) {
                    //             $totalPriceBeforeTaxBeforeDiscount = ($detail['qty']*$detail['price'])/(1 + $taxs->tariff / 100);
                    //             $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                    //             $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $purchaseOrder->disc_nominal;
                    //             $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                    //             $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                    //             $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];
                    //             $taxed += $totalPriceBeforeTaxBeforeDiscount;
                    //         }else{
                    //             $totalPriceBeforeTaxBeforeDiscount = $detail['qty']*$detail['price'];
                    //             $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                    //             $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $purchaseOrder->disc_nominal;
                    //             $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                    //             $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                    //             $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];
                    //         }
                    //     } else {
                    //         if ($itemTax->additional_tax == 1) {
                    //             $totalPriceBeforeTaxBeforeDiscount = $detail['qty']*$detail['price'];
                    //             $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                    //             $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $purchaseOrder->disc_nominal;
                    //             $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                    //             $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                    //             $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];
                    //             $taxed += $totalPriceBeforeTaxBeforeDiscount;
                    //         }else{
                    //             $totalPriceBeforeTaxBeforeDiscount = $detail['qty']*$detail['price'];
                    //             $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                    //             $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $purchaseOrder->disc_nominal;
                    //             $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                    //             $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                    //             $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];
                    //         }
                    //     }
                    // } else {
                    //     $revenueTax = 0;
                    //     $addTax = 0;
                    // }
                    $totalAllItemBeforeTax+=$totalPriceBeforeTaxAfterDiscount??0;

                    if(array_key_exists('purchase_requisition_number',$detail)){
                        $itemQtyL = PurchaseRequisitionDetail::where('purchase_requisition_number', $detail['purchase_requisition_number'])->where('item_id', $detail['item_id'])->where('unit', $detail['unit'])->first();

                        $itemQtyLeft = $itemQtyL->qty_left-$detail['qty'];

                        PurchaseRequisitionDetail::where('purchase_requisition_number', $detail['purchase_requisition_number'])->where('item_id', $detail['item_id'])->where('unit', $detail['unit'])->update(['qty_left'=>$itemQtyLeft]);

                        $checkLeft = PurchaseRequisitionDetail::where('purchase_requisition_number', $detail['purchase_requisition_number'])->where('qty_left','!=', 0)->count();

                        if($checkLeft==0) {
                            PurchaseRequisition::where('purchase_requisition_number', $detail['purchase_requisition_number'])->update(['status'=> 'Closed']);
                        }
                    }

                $rowNumber++;
            }
            }


            $supplier = Supplier::where('supplier_code', $purchaseOrder->supplier_code)->first();

            if ($supplier->pkp == 1) {
                $addTax = $taxed * $taxs->tariff/100;
                $revenueTax = $services * $tax_revenue_tariff/100; //nanti diubah dengan pilihan dari header
            } else {
                $revenueTax = 0;
                $addTax = 0;
            }
            $purchaseOrder->add_tax = $addTax;
            $purchaseOrder->tax_revenue = $revenueTax;

            $purchaseOrder->subtotal =$totalAllItemBeforeTax+$purchaseOrder->disc_nominal;
            $purchaseOrder->total = $totalAllItemBeforeTax + $purchaseOrder->add_tax + $purchaseOrder->tax_revenue;


            $purchaseOrder->save();
            // dd($purchaseOrder);


            DB::commit();
            $id = PurchaseOrder::where('purchase_order_number',$purchaseOrder->purchase_order_number)->select('id')->first()->id;
            // dd($id);
            return redirect()->route('transaction.purchase_order.create')->with('success', 'Purchase Order created successfully.')->with('id',$id);
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            // Log the error for further analysis
            Log::error('Error creating Purchase Order: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create Purchase Order: ' . $e->getMessage())->withInput();
        }
    }



    private function calculateSubtotal(array $details)
    {
        return array_reduce($details, function ($subtotal, $detail) {
            return $subtotal + ($detail['qty'] * $detail['price']);
        }, 0);
    }

    private function generatePurchaseOrderNumber() {
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
        $lastPurchaseOrder = PurchaseOrder::whereYear('created_at', $today->year)
            ->whereMonth('created_at', $month)
            ->where('department_code','DP01')
            ->orderBy('id', 'desc')
            ->first();

        // Determine the new purchase order number
        if ($lastPurchaseOrder) {
            // Extract the last number from the last purchase order number
            $lastNumber = (int)substr($lastPurchaseOrder->purchase_order_number, strrpos($lastPurchaseOrder->purchase_order_number, '-') + 1);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            // Reset counter to 00001 if no purchase orders found for the current month
            $newNumber = '00001';
        }

        // Return the new purchase order number in the desired format
        return "TDS/PUO/{$romanMonth}/{$year}-{$newNumber}";
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
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $purchaseOrderNumber = $purchaseOrder->purchase_order_number; // Get the existing order number for the edit view

        // Format document_date and delivery_date using Carbon
        $purchaseOrder->document_date = Carbon::parse($purchaseOrder->document_date)->format('Y-m-d'); // or any format you prefer
        $purchaseOrder->delivery_date = Carbon::parse($purchaseOrder->delivery_date)->format('Y-m-d'); // or any format you prefer
        $purchaseOrder->due_date = Carbon::parse($purchaseOrder->due_date)->format('Y-m-d');

        $items = ItemPurchase::where('department_code','DP01')->whereHas('items')->orderBy('item_code')->distinct('item_code')->with('items','unitn','itemDetails')->get();

        $purchaseRequisition = PurchaseRequisition::with('department')->where('department_code', 'DP01')->orderBy('id', 'asc')->where('status', 'Not')->get();
        $purchaseRequisitionD = PurchaseRequisitionDetail::orderBy('id', 'asc')->with(['items','units'])->get();

        // Fetch purchase order details
        $purchaseOrderDetails = PurchaseOrderDetail::where('purchase_order_number', $purchaseOrderNumber)->with(['items','units'])->get();
        $generate = GoodReceiptDetail::where('purchase_order_number', $purchaseOrderNumber)->get();
        $editable = count($generate)>0 ? false:true;
        if($purchaseOrder->status=='Cancelled') {
            $editable = false;
        }
        $itemDetails = ItemDetail::where('department_code','DP01')->where('status',true)->orderBy('item_code', 'asc')->get();
        $prices = ItemPurchase::where('department_code','DP01')->orderBy(
            'item_code',
            'asc'
        )->get();

        // Fetch other related data needed for the view

        $suppliers = Supplier::where('department_code','DP01')->get();
        $departments = Department::where('department_code', 'DP01')->first();
        $itemUnits = ItemUnit::orderBy('unit', 'asc')->get();
        $company = Company::first();
        $taxs = TaxMaster::orderBy('tariff', 'asc')->get(); // Fetch tax rates
        $privileges = Auth::user()->roles->privileges['purchase_order'];
        // Return the view with the existing purchase order and related data
        return view('transaction.purchase-order.purchase_order_edit', compact(
            'purchaseOrder',
            'items',
            'purchaseOrderDetails',
            'purchaseRequisition',
            'purchaseRequisitionD',
            'suppliers',
            'departments',
            'itemUnits',
            'itemDetails',
            'prices',
            'company',
            'purchaseOrderNumber',
            'taxs',
            'editable',
            'privileges',
        ));
    }


    public function update(Request $request, $id)
    {
        DB::beginTransaction(); // Start the transaction
        try {
            // Retrieve the PurchaseOrder record by its ID
            $purchaseOrder = PurchaseOrder::findOrFail($id);

            // Parse and assign date fields
            $purchaseOrder->delivery_date = Carbon::createFromFormat('Y-m-d', $request->delivery_date);
            $purchaseOrder->document_date = Carbon::createFromFormat('Y-m-d', $request->document_date);
            // $purchaseOrder->due_date = Carbon::createFromFormat('Y-m-d', $request->due_date);

            // Assign default values for discount
            $purchaseOrder->disc_percent = $request->disc_percent ?? 0;
            $purchaseOrder->disc_nominal = str_replace(',', '', $request->disc_nominal??0);
            $purchaseOrder->notes = $request->notes ?? '';
            // Set created_by and updated_by to the logged-in user
            $userId = Auth::id();
            $purchaseOrder->updated_by = $userId;
            // Set tax from the selected dropdown
            $purchaseOrder->tax = $request->tax;

            $purchaseOrder->include = $request->include ?? false;

            $purchaseOrder->tax_revenue_tariff = $request->tax_revenue;
            $tax_revenue_tariff=0;
            if($request->tax_revenue!=0){
                $tax_revenue_tariffs = TaxMaster::where('tax_code',$request->tax_revenue)->first();
                $tax_revenue_tariff = $tax_revenue_tariffs->tariff;
            }

            // Clear existing purchase order details
            PurchaseOrderDetail::where('purchase_order_number', $purchaseOrder->purchase_order_number)->delete();


            // Save the updated purchase order details
            $this->savePurchaseOrderDetails($request->details, $purchaseOrder, $tax_revenue_tariff);

            DB::commit(); // Commit the transaction
            return redirect()->route('transaction.purchase_order')->with('success', 'Purchase Order updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            // dd($e);
            Log::error('Failed to update Purchase Order: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update Purchase Order: ' . $e->getMessage());
        }
    }

    private function savePurchaseOrderDetails(array $details, $purchaseOrder, $tax_revenue_tariff)
    {

        $nominal = 0;
        $revenueTax = 0;
        $addTax = 0;
        $services = 0;
        $taxed = 0;
        $taxs = TaxMaster::where('tax_code', $purchaseOrder->tax)->first();

        $totalAllAfterDiscountBeforeTax = 0;
        // Process purchase order details
        if (isset($details) && is_array($details)) {
            $rowNumber = 1; // Initialize row number for purchase order details
            // dd($request->details);
            foreach ($details as $detail) {
                $detail['price'] = str_replace(',', '', $detail['price']);
                $detail['disc_percent'] = str_replace(',', '', $detail['disc_percent']??0);
                $detail['disc_nominal'] = str_replace(',', '', $detail['disc_nominal']??0);
                $detail['nominal'] = str_replace(',', '', $detail['nominal']??0);
                $supplier = Supplier::where('supplier_code', $purchaseOrder->supplier_code)->first();
                $itemTax = Item::where('department_code','DP01')->where('item_code', $detail['item_id'])->first();
                $taxs = TaxMaster::where('tax_code', $purchaseOrder->tax)->first();

                if($supplier->pkp == 1 && strtolower($itemTax->category->item_category_name) != 'service') {
                    if($supplier->include == 1) {
                        if ($itemTax->additional_tax == 1 ) {
                            $totalAllAfterDiscountBeforeTax += (($detail['qty']*$detail['price']) / (1 + $taxs->tariff / 100)) - ($detail['disc_percent']/100*(($detail['qty']*$detail['price']) / (1 + $taxs->tariff / 100)))-$detail['disc_nominal'];
                        } else {
                            $totalAllAfterDiscountBeforeTax += $detail['qty']*$detail['price'] - ($detail['disc_percent']/100*(($detail['qty']*$detail['price'])))-$detail['disc_nominal'];
                        }
                    } else {
                        $totalAllAfterDiscountBeforeTax += $detail['qty']*$detail['price'] - ($detail['disc_percent']/100*(($detail['qty']*$detail['price'])))-$detail['disc_nominal'];
                    }
                }else{
                    $totalAllAfterDiscountBeforeTax += $detail['qty']*$detail['price'] - ($detail['disc_percent']/100*(($detail['qty']*$detail['price'])))-$detail['disc_nominal'];
                }

            }
            $totalAllItemBeforeTax = 0;
            $totalAllDiscountDetail = 0;


            foreach ($details as $detail) {
                $detail['price'] = str_replace(',', '', $detail['price']);
                $detail['disc_percent'] = str_replace(',', '', $detail['disc_percent']??0);
                $detail['disc_nominal'] = str_replace(',', '', $detail['disc_nominal']??0);
                $detail['nominal'] = str_replace(',', '', $detail['nominal']??0);
            $detail['purchase_order_number'] = $purchaseOrder->purchase_order_number;
            $detail['number_row'] = $rowNumber; // Correctly assign row number
            $detail['company_code'] = $purchaseOrder->company_code;
            $detail['department_code'] = 'DP01';
            $nominal += $detail['qty']*$detail['price']-($detail['disc_percent']/100*$detail['qty']*$detail['price'])-$detail['disc_nominal'];
            $detail['nominal'] = $detail['qty']*$detail['price']-($detail['disc_percent']/100*$detail['qty']*$detail['price'])-$detail['disc_nominal'];
            $detail['created_by'] = Auth::user()->username;
            $detail['updated_by'] = Auth::user()->username;
            $detail['unit']=$detail['unit'];


                // dd('a');
                $item = ItemDetail::where('department_code','DP01')->where('status',true)->where([
                    ['unit_conversion', $detail['unit']],
                    ['item_code',$detail['item_id']]
                    ])->first();
                $detail['base_qty'] = $item->conversion;
                $detail['qty_left'] = $detail['qty'];
                $detail['base_qty_left'] = $detail['base_qty'];
                $detail['base_unit'] = $item->base_unit;
                $detail['status'] = 'Not';
                $detail['description'] = '';

            PurchaseOrderDetail::create($detail);


                $supplier = Supplier::where('supplier_code', $purchaseOrder->supplier_code)->first();
                $itemTax = Item::where('department_code','DP01')->where('item_code', $detail['item_id'])->first();
                $taxs = TaxMaster::where('tax_code', $purchaseOrder->tax)->first();


                if ($supplier->pkp == 1) {
                    if (strtolower($itemTax->category->item_category_name) == 'service') {
                        $services += $detail['nominal'];
                    }

                    if($supplier->include == 1) {
                        if ($itemTax->additional_tax == 1) {
                            $totalPriceBeforeTaxBeforeDiscount = ($detail['qty']*$detail['price'])/(1 + $taxs->tariff / 100);
                            $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                            $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $purchaseOrder->disc_nominal;
                            $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                            $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                            $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];
                            $taxed += $totalPriceBeforeTaxBeforeDiscount;
                        }else{
                            $totalPriceBeforeTaxBeforeDiscount = $detail['qty']*$detail['price'];
                            $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                            $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $purchaseOrder->disc_nominal;
                            $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                            $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                            $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];
                        }
                    } else {
                        if ($itemTax->additional_tax == 1) {
                            $totalPriceBeforeTaxBeforeDiscount = $detail['qty']*$detail['price'];
                            $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                            $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $purchaseOrder->disc_nominal;
                            $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                            $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                            $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];
                            $taxed += $totalPriceBeforeTaxBeforeDiscount;
                        }else{
                            $totalPriceBeforeTaxBeforeDiscount = $detail['qty']*$detail['price'];
                            $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                            $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $purchaseOrder->disc_nominal;
                            $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                            $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                            $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];
                        }
                    }
                } else {
                    $revenueTax = 0;
                    $addTax = 0;
                    $totalPriceBeforeTaxBeforeDiscount = $detail['qty']*$detail['price'];
                            $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                            $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $purchaseOrder->disc_nominal;
                            $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                            $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                            $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];
                }
                $totalAllItemBeforeTax+=$totalPriceBeforeTaxAfterDiscount;



            $rowNumber++;
        }
        }

        $supplier = Supplier::where('supplier_code', $purchaseOrder->supplier_code)->first();
        if ($supplier->pkp == 1) {
            $addTax = $taxed * $taxs->tariff/100;
            $revenueTax = $services * $tax_revenue_tariff/100; //nanti diubah dengan pilihan dari header
        } else {
            $revenueTax = 0;
            $addTax = 0;
        }
        $purchaseOrder->add_tax = $addTax;
        $purchaseOrder->tax_revenue = $revenueTax;

        $purchaseOrder->subtotal =$totalAllItemBeforeTax+$purchaseOrder->disc_nominal;
        $purchaseOrder->total = $totalAllItemBeforeTax - $purchaseOrder->disc_nominal + $purchaseOrder->add_tax + $purchaseOrder->tax_revenue;

        $purchaseOrder->save();
    }

    public function cancel(Request $request, $id) {
        DB::beginTransaction();
        try {
            $general = PurchaseOrder::findOrFail($id);
            $reason = $request->input('reason');

            $general->update([
                'cancel_notes'=>$reason,
                'status'=>'Cancelled'
            ]);
            DB::commit(); // Commit transaction
            return redirect()->route('transaction.purchase_order')->with('success', 'Purchase Order cancelled successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->route('transaction.purchase_order')->with('error', 'Error canceling: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $PurchaseOrder = PurchaseOrder::findOrFail($id);
            $details = PurchaseOrderDetail::where('purchase_order_number', $PurchaseOrder->purchase_order_number)->get();
            foreach ($details as $key => $value) {
                if($value->purchase_requisition_number){
                    $prDetail = PurchaseRequisitionDetail::where([
                        ['purchase_requisition_number', $value->purchase_requisition_number],
                        ['item_id', $value->item_id],
                        ['unit', $value->unit]
                    ])->first();
                    $prDetail->qty_left = $prDetail->qty_left+$value->qty;
                    $prDetail->save();

                    PurchaseRequisition::where('purchase_requisition_number',$value->purchase_requisition_number)->update(['status'=> 'Not']);
                }
            }
            $general = PurchaseOrder::findOrFail($id);
            PurchaseOrder::where('id', $id)->delete();
            PurchaseOrderDetail::where('purchase_order_number', $general->purchase_order_number)->delete();
            $general->delete();


            $reason = $request->input('reason');

            DeleteLog::create([
                'document_number' => $PurchaseOrder->purchase_order_number,
                'document_date' => $PurchaseOrder->document_date,
                'delete_notes' => $reason,
                'type' => 'PO',
                'company_code' => $PurchaseOrder->company_code,
                'department_code' => $PurchaseOrder->department_code,
                'deleted_by' => Auth::user()->username,
            ]);
            DB::commit(); // Commit transaction
            return redirect()->route('transaction.purchase_order')->with('success', 'Purchase Order deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error('Failed to delete Purchase Order: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete Purchase Order: ' . $e->getMessage());
        }
    }

    public function generate(Request $request, $id)
    {
        $purchaseInvoiceNumber = $this->generatePurchaseInvoiceNumber();
        DB::beginTransaction(); // Start the transaction
        try {
            $purchaseOrder = new PurchaseInvoice();
            $purchaseOrder->purchase_invoice_number = $purchaseInvoiceNumber;
            $purchaseOrder->purchase_order_number = $request->purchase_order_number;
            $purchaseOrder->supplier_code = $request->supplier_code;
            $purchaseOrder->document_date = Carbon::parse($purchaseOrder->document_date)->format('Y-m-d');
            $purchaseOrder->due_date = $request->due_date;

            // Assign default values for discount
            $purchaseOrder->disc_percent = $request->disc_percent ?? 0;
            $purchaseOrder->disc_nominal = $request->disc_nominal ?? 0;
            $purchaseOrder->notes = $request->notes ?? '';
            // Set created_by and updated_by to the logged-in user
            $userId = Auth::id();
            $purchaseOrder->created_by = $userId;
            $purchaseOrder->updated_by = $userId;
            // Set tax from the selected dropdown
            $purchaseOrder->tax = $request->tax;
            // Finalize total
            $purchaseOrder->company_code = $request->company_code ?? null;
            $purchaseOrder->department_code = $request->department_code ?? null;
            $purchaseOrder->include = $request->include ?? false;
            // $purchaseOrder->save();

            $nominal = 0;
            $revenueTax = 0;
            $addTax = 0;

            // Process purchase order details
            if (isset($request->details) && is_array($request->details)) {
                $rowNumber = 1; // Initialize row number for purchase order details
                foreach ($request->details as $detail) {

                $inventory = InventoryDetail::where('item_id', $detail['item_id'])->where('unit', $detail['unit'])->orderBy('id', 'desc')->first();

                $itemm = ItemDetail::where('department_code','DP01')->where([
                    ['unit_conversion', $detail['unit']],
                    ['item_code',$detail['item_id']]
                    ])->first();

                $inven_detail = new InventoryDetail();
                $inven_detail->item_id = $detail['item_id'];
                $inven_detail->unit = $detail['unit'];
                $inven_detail->quantity = $detail['qty'];
                $inven_detail->base_quantity = $detail['qty']*$item->conversion;
                $inven_detail->unit_base = $itemm->base_unit;
                $inven_detail->document_number = $purchaseInvoiceNumber;
                $inven_detail->document_date = $purchaseOrder->document_date;
                $inven_detail->transaction_type = 'Purchase';
                $inven_detail->from_to = $purchaseOrder->supplier_code;
                $inven_detail->department_code = 'DP01';
                $inven_detail->company_code = $purchaseOrder->company_code;
                $inven_detail->first_qty = $inventory->last_qty ?? 0;
                $inven_detail->last_qty = $inven_detail->first_qty +  $inven_detail->quantity;

                $inven_detail->created_by = $userId;
                $inven_detail->updated_by = $userId;

                $inven_detail->save();

                $purchaseReqDetail =  PurchaseRequisitionDetail::where('purchase_requisition_number', $detail['purchase_requisition_number'])->
                where('item_id',$detail['item_id'])->where('unit',$detail['unit'])->first();

                $purchaseReqDetail->qty_left = $purchaseReqDetail->qty_left - $detail['qty'];

                if($purchaseReqDetail->qty_left==0) {
                    $purchaseReqDetail->status = 'Ready';
                }

                $purchaseReqDetail->save();


                $detail['purchase_invoice_number'] = $purchaseInvoiceNumber;
                $detail['number_row'] = $rowNumber; // Correctly assign row number
                $detail['company_code'] = $request->company_code;
                $detail['department_code'] = $request->department_code;
                $nominal += $detail['qty']*$detail['price']-($detail['disc_percent']/100*$detail['qty']*$detail['price'])-$detail['disc_nominal'];
                $detail['nominal'] = $detail['qty']*$detail['price']-($detail['disc_percent']/100*$detail['qty']*$detail['price'])-$detail['disc_nominal'];
                $detail['created_by'] = Auth::user()->username;
                $detail['updated_by'] = Auth::user()->username;

                    $item = ItemDetail::where('department_code','DP01')->where('status',true)->where([
                        ['unit_conversion', $detail['unit']],
                        ['item_code',$detail['item_id']]
                        ])->first();
                    $detail['base_qty'] = $item->conversion;
                    $detail['qty_left'] = $detail['qty'];
                    $detail['base_qty_left'] = $detail['base_qty'];
                    $detail['base_unit'] = $item->base_unit;
                    $detail['status'] = 'Not';


                PurchaseInvoiceDetail::create($detail);


                $supplier = Supplier::where('supplier_code', $purchaseOrder->supplier_code)->first();
                $itemTax = Item::where('department_code','DP01')->where('item_code', $detail['item_id'])->first();
                $taxs = TaxMaster::where('tax_code', $purchaseOrder->tax)->first();

                if ($supplier->pkp == 1) {
                    if ($itemTax->additional_tax == 1) {
                        if (strtolower($itemTax->category->item_category_name) == 'service') {
                            $revenueTax = ($nominal - $purchaseOrder->disc_nominal) * $taxs->tariff/100;
                        }
                    $addTax = ($nominal - $purchaseOrder->disc_nominal) * $taxs->tariff/100;
                }
                } else {
                    $revenueTax = 0;
                    $addTax = 0;
                }

                //PI Detail Nominal Before discount
                $PODJournal1 = new Journal();
                $suppliers = Supplier::where('supplier_code', $request->supplier_code)->first();
                $itemTax = Item::where('department_code','DP01')->where('item_code', $detail['item_id'])->first();
                $categories;
                if($itemTax){
                    $categories = ItemCategory::where('item_category_code', $itemTax->item_category)->first();
                }
                $PODJournal1->document_number = $detail['purchase_invoice_number'];
                $PODJournal1->document_date = $purchaseOrder->document_date;
                $PODJournal1->account_number = $categories->acc_number_purchase??'1001';
                $PODJournal1->debet_nominal = $detail['qty']*$detail['price'];
                $PODJournal1->credit_nominal = 0;
                $PODJournal1->notes = $itemTax?$itemTax->item_name.' ('. $purchaseReqDetail->units->unit_name.') : '.$detail['qty']: $detail['description'];
                $PODJournal1->company_code = $request->company_code;
                $PODJournal1->department_code = $request->department_code;
                $PODJournal1->created_by = Auth::user()->username;
                $PODJournal1->updated_by = Auth::user()->username;

                $PODJournal1 -> save();

                //PI Detail Discount
                if(($detail['disc_percent']/100*$detail['qty']*$detail['price'])+$detail['disc_nominal']>0){
                    $PODJournal = new Journal();
                    $suppliers = Supplier::where('supplier_code', $request->supplier_code)->first();
                    $itemTax = Item::where('department_code','DP01')->where('item_code', $detail['item_id'])->first();
                    $categories = ItemCategory::where('item_category_code', $itemTax->item_category)->first();
                    $PODJournal->document_number = $detail['purchase_invoice_number'];
                    $PODJournal->document_date = $purchaseOrder->document_date;
                    $PODJournal->account_number = $categories->acc_number_purchase_discount??'1001';
                    $PODJournal->debet_nominal = 0;
                    $PODJournal->credit_nominal = ($detail['disc_percent']/100*$detail['qty']*$detail['price'])+$detail['disc_nominal'];
                    $PODJournal->notes = 'Discount for '.$itemTax->item_name.' ('.$detail['qty'].' '.$purchaseReqDetail->units->unit_name.')';
                    $PODJournal->company_code = $request->company_code;
                    $PODJournal->department_code = $request->department_code;
                    $PODJournal->created_by = Auth::user()->username;
                    $PODJournal->updated_by = Auth::user()->username;


                    $PODJournal -> save();
                }

                // if($salesInvoiceNumber->status=='Ready') {
                //     $SOJournal5 = new Journal();
                //     $customers = Customer::where('customer_code', $salesInvoiceNumber->customer_code)->first();
                //     $itemTax = Item::where('item_code', $detail['item_id'])->first();
                //     $categories = ItemCategory::where('item_category_code', $itemTax->item_category)->first();
                //     $taxes = TaxMaster::where('tax_code', $salesInvoiceNumber->tax)->first();
                //     $SOJournal5->document_number = $salesInvoiceDetail->sales_invoice_number;
                //     $SOJournal5->document_date = $salesInvoiceNumber->document_date;
                //     $SOJournal5->account_number = $categories->acc_number_sales;
                //     $SOJournal5->debet_nominal = 0;
                //     $SOJournal5->credit_nominal = $salesInvoiceDetail->nominal;
                //     $SOJournal5->notes = $salesInvoiceNumber->sales_order_number;
                //     $SOJournal5->company_code = $detail['company_code'];
                //     $SOJournal5->department_code = $detail['department_code'];
                //     $SOJournal5->created_by = Auth::user()->username;
                //     $SOJournal5->updated_by = Auth::user()->username;
                //     $SOJournal5 -> save();

                //     $SOJournal6 = new Journal();
                //     $SOJournal6->document_number = $salesInvoiceNumber->sales_invoice_number;
                //     $SOJournal6->document_date = $salesInvoiceNumber->document_date;
                //     $SOJournal6->account_number = $categories->acc_number_sales_discount;
                //     $SOJournal6->debet_nominal = 0;
                //     $SOJournal6->credit_nominal = $salesInvoiceDetail->disc_nominal;
                //     $SOJournal6->notes = $salesInvoiceNumber->sales_order_number;
                //     $SOJournal6->company_code = $detail['company_code'];
                //     $SOJournal6->department_code = $detail['department_code'];
                //     $SOJournal6->created_by = Auth::user()->username;
                //     $SOJournal6->updated_by = Auth::user()->username;
                //     $SOJournal6 -> save();
                // }

                $rowNumber++;
            }
            }

            $purchaseOrder->add_tax = $addTax;
            $purchaseOrder->tax_revenue = $revenueTax;

            $purchaseOrder->subtotal = $nominal;
            $purchaseOrder->total = $purchaseOrder->subtotal - $purchaseOrder->disc_nominal + $purchaseOrder->add_tax - $purchaseOrder->tax_revenue;

            $purchaseOrder->save();

            //PI Header Total
            $POJournal = new Journal();
            $suppliers = Supplier::where('supplier_code', $request->supplier_code)->first();
            $itemTax = Item::where('department_code','DP01')->where('item_code', $detail['item_id'])->first();
            $taxes = TaxMaster::where('tax_code', $purchaseOrder->tax)->first();
            // dd($POJournal);
            $POJournal->document_number = $purchaseInvoiceNumber;
            $POJournal->document_date = $purchaseOrder->document_date;
            $POJournal->account_number = $suppliers->account_payable;
            $POJournal->debet_nominal = 0;
            $POJournal->credit_nominal = $purchaseOrder->total;
            $POJournal->notes = 'Purchase from '.$request->supplier_name.' ('.$purchaseInvoiceNumber.')';
            $POJournal->company_code = $detail['company_code'];
            $POJournal->department_code = $detail['department_code'];
            $POJournal->created_by = Auth::user()->username;
            $POJournal->updated_by = Auth::user()->username;

            $POJournal -> save();

            // dd($purchaseOrder);
            //PI to Debt
            Debt::create([
                'document_number'=>$purchaseInvoiceNumber,
                'document_date'=>$purchaseOrder->document_date,
                'due_date'=>$purchaseOrder->due_date,
                'total_debt'=>$purchaseOrder->total,
                'debt_balance'=>$purchaseOrder->total,
                'supplier_code'=>$request->supplier_code,
                'due_date'=>$request->due_date,
                'company_code'=>$detail['company_code'],
                'department_code'=>$detail['department_code'],
                'created_by' => Auth::user()->username,
                'updated_by' => Auth::user()->username,
            ]);


            //PI Header Disc Nominal
            if($purchaseOrder->disc_nominal>0){
                $POJournal2 = new Journal();
                $suppliers = Supplier::where('supplier_code', $request->supplier_code)->first();
                $itemTax = Item::where('department_code','DP01')->where('item_code', $detail['item_id'])->first();
                $categories;
                if($itemTax){
                    $categories = ItemCategory::where('item_category_code', $itemTax->item_category)->first();
                }
                $POJournal2->document_number = $purchaseInvoiceNumber;
                $POJournal2->document_date = $purchaseOrder->document_date;
                $POJournal2->account_number = $categories->acc_number_purchase_discount??'1001';
                $POJournal2->debet_nominal = 0;
                $POJournal2->credit_nominal = $purchaseOrder->disc_nominal;
                $POJournal2->notes = 'Discount on purchase from '.$request->supplier_name.' ('.$purchaseInvoiceNumber.')';
                $POJournal2->company_code = $detail['company_code'];
                $POJournal2->department_code = $detail['department_code'];
                $POJournal2->created_by = Auth::user()->username;
                $POJournal2->updated_by = Auth::user()->username;

                $POJournal2 -> save();
            }
            //PI Header Add Tax

            $POJournal2 = new Journal();
            $suppliers = Supplier::where('supplier_code', $request->supplier_code)->first();
            $itemTax = Item::where('department_code','DP01')->where('item_code', $detail['item_id'])->first();
            $POJournal2->document_number = $purchaseInvoiceNumber;
            $POJournal2->document_date = $purchaseOrder->document_date;
            $POJournal2->account_number = $suppliers->account_add_tax;
            $POJournal2->debet_nominal = $purchaseOrder->add_tax;
            $POJournal2->credit_nominal = 0;
            $POJournal2->notes = 'Add. tax on purchase from '.$request->supplier_name.' ('.$purchaseInvoiceNumber.')';
            $POJournal2->company_code = $detail['company_code'];
            $POJournal2->department_code = $detail['department_code'];
            $POJournal2->created_by = Auth::user()->username;
            $POJournal2->updated_by = Auth::user()->username;

            $POJournal2 -> save();

            //PI Header Revenue Tax
            $POJournal3 = new Journal();
            $suppliers = Supplier::where('supplier_code', $request->supplier_code)->first();
            $itemTax = Item::where('department_code','DP01')->where('item_code', $detail['item_id'])->first();
            $POJournal3->document_number = $purchaseInvoiceNumber;
            $POJournal3->document_date = $purchaseOrder->document_date;
            $POJournal3->account_number = $taxes->account_number;
            $POJournal3->debet_nominal = 0;
            $POJournal3->credit_nominal = $purchaseOrder->tax_revenue;
            $POJournal3->notes = 'Revenue tax on purchase from '.$request->supplier_name.' ('.$purchaseInvoiceNumber.')';
            $POJournal3->company_code = $detail['company_code'];
            $POJournal3->department_code = $detail['department_code'];
            $POJournal3->created_by = Auth::user()->username;
            $POJournal3->updated_by = Auth::user()->username;

            $POJournal3 -> save();


            if (isset($request->details) && is_array($request->details)) {
                $rowNumber = 1;
                $counts = 0;
                foreach ($request->details as $detail) {

                    $purchaseReqDetail =  PurchaseRequisitionDetail::where('purchase_requisition_number', $detail['purchase_requisition_number'])->
                    where('item_id',$detail['item_id'])->where('unit',$detail['unit'])->first();

                    if($purchaseReqDetail->status=='Not') {
                        $counts += 1;
                    }
                    if($counts==0) {
                        PurchaseRequisition::where('purchase_requisition_number', $detail['purchase_requisition_number'])->update(['status' => 'Ready']);
                    }
                    if($counts>0) {
                        PurchaseRequisition::where('purchase_requisition_number', $detail['purchase_requisition_number'])->update(['status' => 'Not']);
                    }
                $rowNumber++;
                }

            }

            DB::commit(); // Commit the transaction
            return redirect()->route('transaction.purchase_order')->with('success', 'Purchase Invoice generated successfully.');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            // dd($e);
            Log::error('Failed to generate Invoice: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate Invoice: ' . $e->getMessage());
        }

    }
}
