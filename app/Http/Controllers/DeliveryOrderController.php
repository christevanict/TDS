<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Warehouse;
use App\Models\Customer;
use App\Models\Company;
use App\Models\DeliveryOrder;
use App\Models\DeleteLog;
use App\Models\DeliveryOrderDetail;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\ItemDetail;
use App\Models\InventoryDetail;
use App\Models\ItemSalesPrice;
use App\Models\SalesInvoiceDetail;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use stdClass;

class DeliveryOrderController extends Controller
{
    public function index() {
        $deliveryOrderRecords = DeliveryOrder::with(['customer','department'])->orderBy('id','desc')->get();
        // dd($deliveryOrderRecords);

        return view('transaction.delivery-order.delivery_order_list', compact('deliveryOrderRecords'));
    }

    public function cancel(Request $request, $id) {
        DB::beginTransaction();
        try {
            $general = DeliveryOrder::findOrFail($id);
            $reason = $request->input('reason');

            $general->update([
                'cancel_notes'=>$reason,
                'status'=>'Cancelled'
            ]);
            DB::commit(); // Commit transaction
            return redirect()->route('transaction.warehouse.delivery_order')->with('success', 'Delivery Order cancelled successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->route('transaction.warehouse.delivery_order')->with('error', 'Error canceling: ' . $e->getMessage());
        }
    }

    private function generateDeliveryOrderNumber() {
        $today = now();
        $month = $today->format('n'); // Numeric representation of a month (1-12)
        $year = $today->format('y'); // Last two digits of the year

        // Convert month to Roman numeral
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12   => 'XII'
        ];
        $romanMonth = $romanMonths[$month];
        $department = 'DP01';
        // Fetch the last purchase order created in the current month
        $lastSalesOrder = DeliveryOrder::whereYear('created_at', $today->year)
            ->whereRaw("SUBSTRING(delivery_order_number, 5,  4) = '".$department."'")
            ->whereMonth('created_at', $month)
            ->orderBy('delivery_order_number', 'desc')
            ->first();
        // dd($lastSalesOrder);
        // Determine the new purchase order number
        if ($lastSalesOrder) {
            // Extract the last number from the last purchase order number
            $lastNumber = (int)substr($lastSalesOrder->delivery_order_number, strrpos($lastSalesOrder->delivery_order_number, '-') + 1);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
            // dd($lastNumber,$newNumber);
        } else {
            // Reset counter to 00001 if no purchase orders found for the current month
            $newNumber = '00001';
        }

        // Return the new purchase order number in the desired format
        return "TDS/{$department}/DOR/{$romanMonth}/{$year}-{$newNumber}";
    }

    public function create() {
        $warehouses = Warehouse::all();
        $customers = Customer::orderBy('id', 'asc')->get();
        $companies = Company::all(); // Ambil semua company

        $itemUnits = ItemUnit::all();
        $itemDetails = ItemDetail::where('department_code','DP01')->get();
        $items = ItemSalesPrice::where('department_code','DP01')->with('items', 'unitn')->whereHas('items.category', function($query) {
            $query->where('item_category_name', '!=', 'Service');
        })->get();
        $salesOrders = SalesOrder::where('department_code','DP01')->with(['details','details.items','details.units'])->where('status','!=','Closed')->where('status','!=','Cancelled')->get();
        // Generate nomor DeliveryOrder secara otomatis

        // Pilih company pertama secara otomatis
        $firstCompany = $companies->first();

        return view('transaction.delivery-order.delivery_order_input', compact('warehouses', 'customers', 'companies', 'itemDetails', 'itemUnits', 'items', 'firstCompany','salesOrders'));
    }

    public function store(Request $request) {
        DB::beginTransaction();
        try {
            $DeliveryOrder = new DeliveryOrder();
            $DeliveryOrder->delivery_order_number = $this->generateDeliveryOrderNumber();
            $DeliveryOrder->document_date = $request->document_date;
            $DeliveryOrder->customer_code = $request->customer_code;
            $DeliveryOrder->notes = $request->notes ?? '';
            $DeliveryOrder->department_code = 'DP01';
            $DeliveryOrder->status = 'Open';
            $DeliveryOrder->created_by = Auth::user()->username;
            $DeliveryOrder->updated_by = Auth::user()->username;

            $DeliveryOrder->save();


            // Aggregate DeliveryOrder details before saving
            $this->saveDeliveryOrderDetails($request->details, $DeliveryOrder->delivery_order_number,  $request->document_date,$DeliveryOrder->department_code, []);
            DB::commit();

            return redirect()->route('transaction.warehouse.delivery_order')->with('success', 'DeliveryOrder saved successfully! DeliveryOrder Number: ' . $DeliveryOrder->delivery_order_number);
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save: ' . $e->getMessage());
        }
    }

    public function edit($id) {
        try {
            $itemDetails = ItemDetail::where('department_code','DP01')->get();
            $itemUnits = ItemUnit::all();
            $items = ItemSalesPrice::where('department_code','DP01')->with('items', 'unitn')->whereHas('items.category', function($query) {
                $query->where('item_category_name', '!=', 'Service');
            })->get();
            $deliveryOrder = DeliveryOrder::with('deliveryOrderDetails','deliveryOrderDetails.sos')->findOrFail($id);
            $poDetails = SalesOrderDetail::where('department_code',$deliveryOrder->department_code)->get();
            $generate = SalesInvoiceDetail::where('delivery_order_number', $deliveryOrder->delivery_order_number)->get();
            $editable = count($generate)>0 ? false:true;

            foreach ($deliveryOrder->deliveryOrderDetails as $key => $value) {
                # code...
                $so = SalesOrder::where('sales_order_number', $value->sales_order_number)->first();
                if($so->status == 'Cancelled'){
                    $editable = false;
            }}
            return view('transaction.delivery-order.delivery_order_edit', compact('deliveryOrder', 'itemDetails', 'itemUnits', 'items','editable','poDetails'));
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with('error', 'Failed to load edit form: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id) {
        DB::beginTransaction();
        try {
            $DeliveryOrder = DeliveryOrder::findOrFail($id);
            $DeliveryOrder->document_date = $request->document_date;
            $DeliveryOrder->notes = $request->notes ?? $DeliveryOrder->notes;
            $DeliveryOrder->save();

            $oldDeliveryOrder = DeliveryOrderDetail::where('delivery_order_number', $DeliveryOrder->delivery_order_number)->get();
            // foreach ($oldDeliveryOrder as $value) {
            //     $item = Item::where('item_code', $value->item_code)->first();
            //     if ($item) {
            //         $item->qty = (float)$item->qty - (float)$value->qty * (float)$value->base_qty;
            //         $item->save();
            //     }
            // }

            DeliveryOrderDetail::where('delivery_order_number', $DeliveryOrder->delivery_order_number)->delete();


            // dd($request->details);
            $this->saveDeliveryOrderDetails($request->details, $DeliveryOrder->delivery_order_number, $DeliveryOrder->document_date, $DeliveryOrder->department_code, $oldDeliveryOrder);
            DB::commit();
            return redirect()->route('transaction.warehouse.delivery_order')->with('success', 'DeliveryOrder updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to update: ' . $e->getMessage());
        }
    }

    private function saveDeliveryOrderDetails(array $DeliveryOrderDetails, $delivery_order_number,$document_date, $department_code, $oldDeliveryOrder) {


            foreach ($DeliveryOrderDetails as $index => $detail) {
                $detail['delivery_order_number'] = $delivery_order_number;
                $detail['department_code'] = $department_code;
                $detail['item_id'] = $detail['item_code'];
                $detail['base_qty'] = $detail['base_qty'] ?? 0;
                $detail['created_by'] = Auth::user()->username;
                $detail['updated_by'] = Auth::user()->username;

                $item = Item::where('item_code', $detail['item_code'])->first();
                // $firstQty = $item->qty;
                if ($item) {
                    $detail['base_unit'] = $item->base_unit;
                }

                // $podetail = SalesOrderDetail::where([
                //     ['sales_order_number',$detail['sales_order_number']],
                //     ['item_id',$detail['item_id']],
                //     ['unit',$detail['unit']],
                // ])->first();
                // // dd($podetail);

                // $detail['nominal'] = $podetail->nominal;

                if (array_key_exists('unit', $detail) && $item && $detail['unit'] == $item['base_unit']) {
                    $detail['base_qty'] = 1;
                } else {
                    $itemDetails = ItemDetail::where('department_code','DP01')->where([
                    ['item_code', '=', $detail['item_code']],
                        ['unit_conversion', '=', $detail['unit'] ?? null],
                    ])->first();

                    if ($itemDetails) {
                        $detail['base_qty'] = $itemDetails->conversion;
                        $detail['base_unit'] = $itemDetails->base_unit ?? $itemDetails->unit_conversion;
                    } else {
                        $detail['base_qty'] = 0;
                    }
                }

                // $item->qty = (float)$item->qty + (float)$detail['qty'] * (float)$detail['base_qty'];
                // $lastQty = $item->qty;
                // $item->save();



                $itemQtyL = SalesOrderDetail::where('sales_order_number', $detail['sales_order_number'])->where('item_id', $detail['item_id'])->where('unit', $detail['unit'])->first();

                // foreach ($oldDeliveryOrder as $key => $value) {
                //     if($value->item_id==$detail['item_id']&&$value->unit==$detail['unit']){
                //         $itemQtyLeft = $value->qty-$detail['qty'];
                //         if($itemQtyLeft<0){
                //             $itemQtyLeft = 0;
                //         }
                //         SalesOrderDetail::where('sales_order_number', $detail['sales_order_number'])->where('item_id', $detail['item_id'])->where('unit', $detail['unit'])->update(['qty_left'=>$itemQtyLeft]);

                //         $checkLeft = SalesOrderDetail::where('sales_order_number', $detail['sales_order_number'])->where('qty_left', '!=', 0)->count();

                //         if($checkLeft<=0) {
                //             SalesOrder::where('sales_order_number', $detail['sales_order_number'])->update(['status'=> 'Closed']);
                //         } else {
                //             SalesOrder::where('sales_order_number', $detail['sales_order_number'])->update(['status'=> 'Partial']);
                //         }
                //     }
                // }


                if(sizeof($oldDeliveryOrder)<=0){
                    $itemQtyLeft = $itemQtyL->qty_left-$detail['qty'];

                        if($itemQtyLeft<0){
                            $itemQtyLeft = 0;
                        }
                        SalesOrderDetail::where('sales_order_number', $detail['sales_order_number'])->where('item_id', $detail['item_id'])->where('unit', $detail['unit'])->update(['qty_left'=>$itemQtyLeft]);

                        $checkLeft = SalesOrderDetail::where('sales_order_number', $detail['sales_order_number'])->where('qty_left', '!=', 0)->count();

                        if($checkLeft<=0) {
                            SalesOrder::where('sales_order_number', $detail['sales_order_number'])->update(['status'=> 'Closed']);
                        } else {
                            SalesOrder::where('sales_order_number', $detail['sales_order_number'])->update(['status'=> 'Partial']);
                        }
                }


                DeliveryOrderDetail::create($detail);


            }

            foreach($oldDeliveryOrder as $key => $value){
                $exist = false;
                // dd($value);
                foreach($DeliveryOrderDetails as $key2 => $value2){
                    // dd($value2);
                    // dd($value);
                    // dd($value2);
                    $itemQtyL = SalesOrderDetail::where('sales_order_number', $value2['sales_order_number'])->where('item_id', $value2['item_code'])->where('unit', $value2['unit'])->first();
                    if($value->item_id == $value2['item_code']) {
                        $left = $itemQtyL->qty_left + $value->qty - $value2['qty'];
                        // dd($left);
                        SalesOrderDetail::where('sales_order_number', $value2['sales_order_number'])->where('item_id', $value2['item_code'])->where('unit', $value2['unit'])->update(['qty_left'=>$left]);
                        $exist=true;
                    }
                }
                if(!$exist) {
                    $itemQtyL = SalesOrderDetail::where('sales_order_number', $value->sales_order_number)->where('item_id', $value->item_id)->where('unit', $value->unit)->first();
                    $itemQtyL->qty_left = $itemQtyL->qty_left + $value->qty;
                    $itemQtyL->save();
                    // dd($itemQtyL);
                }
                $checkLeft = SalesOrderDetail::where('sales_order_number', $detail['sales_order_number'])->where('qty_left', '!=', 0)->count();

                if($checkLeft<=0) {
                    SalesOrder::where('sales_order_number', $detail['sales_order_number'])->update(['status'=> 'Closed']);
                } else {
                    SalesOrder::where('sales_order_number', $detail['sales_order_number'])->update(['status'=> 'Partial']);
                }

            }
    }

    public function printPDF($delivery_order_number)
    {
        $deliveryOrder = DeliveryOrder::with([
            'deliveryOrderDetails',
            'deliveryOrderDetails.units',
            'deliveryOrderDetails.items',
            'department',
            'customer',
        ])->where('id', $delivery_order_number)->firstOrFail();

        $details = [];
        $pos = [];

        foreach ($deliveryOrder->deliveryOrderDetails as $index => $detail) {
            $item = new stdClass();
            $itemDetail  = ItemDetail::where('department_code','DP01')->where([
                ['item_code', $detail->item_id],
                ['unit_conversion', $detail->unit]
            ])->first();
            $item->barcode = $itemDetail->barcode;
            $item->item_code = $detail->item_id;
            $item->item_name = $detail->items->item_name;
            $item->qty = $detail->qty;
            $item->unit = $detail->units->unit_name;
            array_push($details, $item);

            if ($detail->sales_order_number && $detail->sales_order_number != '') {
                $salesOrderNumbers = array_unique(explode(',', $detail->sales_order_number));
                $pos = array_merge($pos, $salesOrderNumbers);
            }
        }
        $pos = array_unique($pos);
        // dd($pos);

        // Generate and return PDF
        $pdf = \PDF::loadView('transaction.delivery-order.delivery_order_pdf', compact('deliveryOrder', 'details', 'pos'));
        $nameFile = Str::replace("/", "", $deliveryOrder->delivery_order_number);
        return $pdf->stream("Purchase_Order_{$nameFile}.pdf");
    }

    public function destroy(Request $request, $id) {
        DB::beginTransaction();
        try {
            $DeliveryOrder = DeliveryOrder::findOrFail($id);

            $details = DeliveryOrderDetail::where('delivery_order_number', $DeliveryOrder->delivery_order_number)->get();
            $soNumber = '';
            foreach ($details as $key => $value) {
                $poDetail = SalesOrderDetail::where([
                    ['sales_order_number', $value->sales_order_number],
                    ['item_id', $value->item_id],
                    ['unit', $value->unit]
                ])->first();
                $poDetail->qty_left = $poDetail->qty_left+$value->qty;
                $poDetail->save();
                if(!str_contains($soNumber,$value->sales_order_number)){
                    $soNumber = $soNumber.' '.$value->sales_order_number;
                }
                $totalAll = SalesOrderDetail::count();

                $checkLeft = SalesOrderDetail::where('sales_order_number', $value->sales_order_number)->whereColumn('qty_left', 'qty')->count();

                $so = SalesOrder::where('sales_order_number',$value->sales_order_number)->first();
                if($so->status!='Cancelled'){
                    if($checkLeft==$totalAll) {
                        $so->update(['status'=> 'Open']);
                    } else {
                        $so->update(['status'=> 'Partial']);
                    }
                }
                $so->save();
            }
            DeliveryOrderDetail::where('delivery_order_number', $DeliveryOrder->delivery_order_number)->delete();
            $DeliveryOrder->delete();

            $reason = $request->input('reason');

            DeleteLog::create([
                'document_number' => $DeliveryOrder->delivery_order_number,
                'document_date' => $DeliveryOrder->document_date,
                'delete_notes' => $reason.' :'.$soNumber,
                'type' => 'DO',
                'company_code' => $DeliveryOrder->company_code,
                'department_code' => $DeliveryOrder->department_code,
                'deleted_by' => Auth::user()->username,
            ]);

            DB::commit();
            return redirect()->route('transaction.warehouse.delivery_order')->with('success', 'DeliveryOrder deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->route('transaction.warehouse.delivery_order')->with('error', 'Error deleting DeliveryOrder: ' . $e->getMessage());
        }
    }


}
