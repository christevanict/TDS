<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Warehouse;
use App\Models\Supplier;
use App\Models\Company;
use App\Models\GoodReceipt;
use App\Models\GoodReceiptDetail;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\ItemDetail;
use App\Models\InventoryDetail;
use App\Models\DeleteLog;
use App\Models\ItemPurchase;
use App\Models\PurchaseInvoiceDetail;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use stdClass;
use TCPDF;

class GoodReceiptController extends Controller
{
    public function index() {
        $goodReceiptRecords = GoodReceipt::where('department_code', 'DP01')->with(['supplier','department'])->orderBy('id','desc')->get();
        // dd($goodReceiptRecords);
        $privileges = Auth::user()->roles->privileges['good_receipt'];

        return view('transaction.good-receipt.good_receipt_list', compact('goodReceiptRecords','privileges'));
    }

    private function generateGoodReceiptNumber() {
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
        $lastPurchaseOrder = GoodReceipt::whereYear('created_at', $today->year)
            ->where('department_code','DP01')
            ->whereMonth('created_at', $month)
            ->orderBy('good_receipt_number', 'desc')
            ->first();
        // dd($lastPurchaseOrder);
        // Determine the new purchase order number
        if ($lastPurchaseOrder) {
            // Extract the last number from the last purchase order number
            $lastNumber = (int)substr($lastPurchaseOrder->good_receipt_number, strrpos($lastPurchaseOrder->good_receipt_number, '-') + 1);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
            // dd($lastNumber,$newNumber);
        } else {
            // Reset counter to 00001 if no purchase orders found for the current month
            $newNumber = '00001';
        }

        // Return the new purchase order number in the desired format
        return "TDS/GOR/{$romanMonth}/{$year}-{$newNumber}";
    }

    public function create() {
        $warehouses = Warehouse::all();
        $suppliers = Supplier::where('department_code','DP01')->get();
        $companies = Company::all(); // Ambil semua company

        $itemUnits = ItemUnit::all();
        $itemDetails = ItemDetail::where('department_code','DP01')->where('status',true)->get();
        $items = ItemPurchase::where('department_code','DP01')->whereHas('items')->distinct('item_code')->with('items', 'unitn')->whereHas('items.category', function($query) {
            $query->where('item_category_name', '!=', 'Service');
        })->get();
        $purchaseOrders = PurchaseOrder::where('department_code','DP01')->with(['details','details.items','details.units'])->where('status','!=','Closed')->get();
        $token = str()->random(16);
        // Generate nomor GoodReceipt secara otomatis

        // Pilih company pertama secara otomatis
        $firstCompany = $companies->first();
        $privileges = Auth::user()->roles->privileges['good_receipt'];

        return view('transaction.good-receipt.good_receipt_input', compact('warehouses', 'suppliers', 'companies', 'itemDetails', 'itemUnits', 'items', 'firstCompany','purchaseOrders','privileges','token'));
    }

    public function store(Request $request) {
        $exist = GoodReceipt::where('token',$request->token)->where('department_code','DP01')->whereDate('created_at',Carbon::today())->first();
        if($exist){
            $id = GoodReceipt::where('created_by',Auth::user()->username)->orderBy('id','desc')->select('id')->first()->id;
            return redirect()->route('transaction.warehouse.good_receipt.create')->with('success', 'Good Receipt created successfully.')->with('id',$id);
        }
        DB::beginTransaction();
        try {
            $GoodReceipt = new GoodReceipt();
            $GoodReceipt->good_receipt_number = $this->generateGoodReceiptNumber();
            $GoodReceipt->document_date = $request->document_date;
            $GoodReceipt->supplier_code = $request->supplier_code;
            $GoodReceipt->warehouse_code = $request->warehouse_code;
            $GoodReceipt->vendor_number = $request->vendor_number;
            $GoodReceipt->token = $request->token;
            $GoodReceipt->notes = $request->notes ?? '';
            $GoodReceipt->department_code = 'DP01';
            $GoodReceipt->status = 'Open';
            $GoodReceipt->created_by = Auth::user()->username;
            $GoodReceipt->updated_by = Auth::user()->username;
            $GoodReceipt->save();
            // dd($GoodReceipt);


            // Aggregate GoodReceipt details before saving
            $this->saveGoodReceiptDetails($request->details, $GoodReceipt->good_receipt_number,  $request->document_date,$GoodReceipt->department_code, [],$GoodReceipt);

            $id = GoodReceipt::where('good_receipt_number',$GoodReceipt->good_receipt_number)->select('id')->first()->id;

            DB::commit();

            return redirect()->route('transaction.warehouse.good_receipt.create')->with('success', 'Good Receipt saved successfully! Good  Receipt Number: ' . $GoodReceipt->good_receipt_number)->with('id',$id);
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to save: ' . $e->getMessage());
        }
    }

    public function edit($id) {
        try {
            $warehouses = Warehouse::all();
            $itemDetails = ItemDetail::where('department_code','DP01')->where('status',true)->get();
            $itemUnits = ItemUnit::all();
            $items = ItemPurchase::where('department_code','DP01')->whereHas('items')->with('items', 'unitn')->whereHas('items.category', function($query) {
                $query->where('item_category_name', '!=', 'Service');
            })->get();

            $goodReceipt = GoodReceipt::with(['goodReceiptDetails' => function($query) {
                $query->orderBy('purchase_order_number', 'asc')->orderBy('id','asc'); // Order by purchase_order_number
            }])->findOrFail($id);
            $poDetails = PurchaseOrderDetail::where('department_code',$goodReceipt->department_code)->get();
            $generate = PurchaseInvoiceDetail::where('good_receipt_number', $goodReceipt->good_receipt_number)->get();
            $editable = count($generate)>0 ? false:true;
            if($goodReceipt->status=='Cancelled') {
                $editable = false;
            }
            $privileges = Auth::user()->roles->privileges['good_receipt'];
            return view('transaction.good-receipt.good_receipt_edit', compact('warehouses','goodReceipt', 'itemDetails', 'itemUnits', 'items','editable','poDetails','privileges'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load edit form: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id) {
        DB::beginTransaction();
        try {
            $GoodReceipt = GoodReceipt::findOrFail($id);
            $GoodReceipt->notes = $request->notes ?? $GoodReceipt->notes;
            $GoodReceipt->warehouse_code = $request->warehouse_code;
            $GoodReceipt->vendor_number = $request->vendor_number;
            $GoodReceipt->document_date = $request->document_date;
            $GoodReceipt->save();




            $oldGoodReceipt = GoodReceiptDetail::where('good_receipt_number', $GoodReceipt->good_receipt_number)->get();

            InventoryDetail::where('document_number', $GoodReceipt->good_receipt_number)->delete();

            GoodReceiptDetail::where('good_receipt_number', $GoodReceipt->good_receipt_number)->delete();
            // dd($request->details);
            $this->saveGoodReceiptDetails($request->details, $GoodReceipt->good_receipt_number, $GoodReceipt->document_date, $GoodReceipt->department_code, $oldGoodReceipt,$GoodReceipt);
            DB::commit();
            return redirect()->route('transaction.warehouse.good_receipt')->with('success', 'GoodReceipt updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to update: ' . $e->getMessage());
        }
    }

    private function saveGoodReceiptDetails(array $GoodReceiptDetails, $good_receipt_number,$document_date, $department_code, $oldGoodReceipt,$GoodReceipt) {
        // dd($GoodReceiptDetails);
        foreach ($GoodReceiptDetails as $index => $detail) {
            $detail['good_receipt_number'] = $good_receipt_number;
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

            $firstQ = InventoryDetail::where([
                ['item_id', $detail['item_id']],
                ['department_code',$GoodReceipt->department_code]
                ])->orderBy('id','desc')->first();

            $firstQty = $firstQ->last_qty??0;

            $cogs = 0;
            $company = Company::first();
            $gd = Warehouse::where("warehouse_code",$GoodReceipt->warehouse_code)->first();



            if (array_key_exists('unit', $detail) && $item && $detail['unit'] == $item['base_unit']) {
                $detail['base_qty'] = 1;
            } else {
                $itemDetails = ItemDetail::where('department_code','DP01')->where('status',true)->where([
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

            InventoryDetail::create([
                'document_number'=>$good_receipt_number,
                'document_date'=>$document_date,
                'transaction_type'=>'Good Receipt',
                'from_to'=>$GoodReceipt->supplier_code,
                'item_id'=>$detail['item_id'],
                'quantity'=>$detail['qty'],
                'unit'=>$detail['unit'],
                'base_quantity'=>$detail['base_qty'],
                'unit_base'=>$detail['base_unit'],
                'company_code'=>$company->company_code,
                'department_code'=>$GoodReceipt->department_code,
                'first_qty'=>$firstQty,
                'warehouse_id' => $gd->id,
                'last_qty'=>$firstQty+$detail['qty']*$detail['base_qty'],
                'created_by' => Auth::user()->username,
                'updated_by' => Auth::user()->username,
                'total' => 0,
                'cogs' => $cogs,
                'qty_actual' => $detail['qty']
            ]);


            // $item->qty = (float)$item->qty + (float)$detail['qty'] * (float)$detail['base_qty'];
            // $lastQty = $item->qty;
            // $item->save();
                $poNumbers = explode(',',$detail['purchase_order_number']);
                $poNumbers = array_unique($poNumbers);
                foreach ($poNumbers as $key => $po) {
                    $itemQtyL = PurchaseOrderDetail::where('purchase_order_number', $po)->where('item_id', $detail['item_id'])->first();
                    if(sizeof($oldGoodReceipt)<=0&&$itemQtyL){
                        $itemQtyLeft = $itemQtyL->qty_left-$detail['qty'];
                        if($itemQtyLeft<0){
                            $itemQtyLeft = 0;
                        }
                        PurchaseOrderDetail::where('purchase_order_number', $po)->where('item_id', $detail['item_id'])->where('unit', $detail['unit'])->update(['qty_left'=>$itemQtyLeft]);

                        $checkLeft = PurchaseOrderDetail::where('purchase_order_number', $po)->where('qty_left', '!=', 0)->count();

                        if($checkLeft<=0) {
                            PurchaseOrder::where('purchase_order_number', $po)->update(['status'=> 'Closed']);
                        } else {
                            PurchaseOrder::where('purchase_order_number', $po)->update(['status'=> 'Partial']);
                        }
                    }
                }




            GoodReceiptDetail::create($detail);
            // dd($detail);


        }

        foreach($oldGoodReceipt as $key => $value){
            $exist = false;
            // dd($value);
            foreach($GoodReceiptDetails as $key2 => $value2){
                // dd($value2);
                // dd($value);
                // dd($value2);
                $itemQtyL = PurchaseOrderDetail::where('purchase_order_number', $value2['purchase_order_number'])->where('item_id', $value2['item_code'])->where('unit', $value2['unit'])->first();
                if($value->item_id == $value2['item_code']&&$itemQtyL) {
                    $left = $itemQtyL->qty_left + $value->qty - $value2['qty'];
                    // dd($left);
                    PurchaseOrderDetail::where('purchase_order_number', $value2['purchase_order_number'])->where('item_id', $value2['item_code'])->where('unit', $value2['unit'])->update(['qty_left'=>$left]);
                    $exist=true;
                }
            }
            if(!$exist) {
                $itemQtyL = PurchaseOrderDetail::where('purchase_order_number', $value->purchase_order_number)->where('item_id', $value->item_id)->where('unit', $value->unit)->first();
                if($itemQtyL){
                    $itemQtyL->qty_left = $itemQtyL->qty_left + $value->qty;
                    $itemQtyL->save();
                }
                // dd($itemQtyL);
            }
            $checkLeft = PurchaseOrderDetail::where('purchase_order_number', $detail['purchase_order_number'])->where('qty_left', '!=', 0)->count();

            if($checkLeft<=0) {
                PurchaseOrder::where('purchase_order_number', $detail['purchase_order_number'])->update(['status'=> 'Closed']);
            } else {
                PurchaseOrder::where('purchase_order_number', $detail['purchase_order_number'])->update(['status'=> 'Partial']);
            }
        }
        // dd('a');

    }

    public function printPDF($good_receipt_number)
    {
        $goodReceipt = GoodReceipt::with([
            'goodReceiptDetails' => function ($query) {
                $query->orderBy('id', 'asc');
            },
            'goodReceiptDetails.units',
            'goodReceiptDetails.items',
            'department',
            'supplier',
        ])->where('id', $good_receipt_number)->firstOrFail();

        $details = [];
        $pos = [];

        foreach ($goodReceipt->goodReceiptDetails as $index => $detail) {
            $item = new stdClass();
            $itemDetail  = ItemDetail::where('department_code','DP01')->where('status',true)->where([
                ['item_code',$detail->item_id],
                ['unit_conversion',$detail->unit]
            ])->first();
            $item->barcode = $itemDetail->barcode;
            $item->item_code = $detail->item_id;
            $item->item_name = $detail->items->item_name;
            $item->qty = $detail->qty;
            $item->base_qty = $detail->base_qty;
            $item->unit = $detail->units->unit_name;
            $item->baseUnit = $detail->baseUnits->unit_name;
            array_push($details,$item);

            if($detail->purchase_order_number && $detail->purchase_order_number != ''){
                $purchaseOrderNumbers = array_unique(explode(',', $detail->purchase_order_number));
                $pos = array_merge($pos, $purchaseOrderNumbers);
            }
        }
        $pos = array_unique($pos);
        // dd($pos);

        // Generate and return PDF
        return view('transaction.good-receipt.good_receipt_pdf', compact('goodReceipt', 'details','pos'));
        $nameFile = Str::replace("/", "", $goodReceipt->good_receipt_number);
        return $pdf->stream("Purchase_Order_{$nameFile}.pdf");

    }

    public function printPdfTc($good_receipt_number)
    {
        // Retrieve the sales invoice with related data
        $goodReceipt = GoodReceipt::with([
            'goodReceiptDetails',
            'goodReceiptDetails.units',
            'goodReceiptDetails.items',
            'department',
            'supplier',
        ])->where('id', $good_receipt_number)->firstOrFail();

        $details = [];
        $pos = [];

        foreach ($goodReceipt->goodReceiptDetails as $index => $detail) {
            $item = new stdClass();
            $itemDetail  = ItemDetail::where('department_code','DP01')->where([
                ['item_code',$detail->item_id],
                ['unit_conversion',$detail->unit]
            ])->first();
            $item->barcode = $itemDetail->barcode;
            $item->item_code = $detail->item_id;
            $item->item_name = $detail->items->item_name;
            $item->qty = $detail->qty;
            $item->base_qty = $detail->base_qty;
            $item->unit = $detail->units->unit_name;
            $item->baseUnit = $detail->baseUnits->unit_name;
            array_push($details,$item);

            if($detail->purchase_order_number && $detail->purchase_order_number != ''){
                $purchaseOrderNumbers = array_unique(explode(',', $detail->purchase_order_number));
                $pos = array_merge($pos, $purchaseOrderNumbers);
            }
        }
        $pos = array_unique($pos);

        // Initialize TCPDF for continuous form
        $pdf = new TCPDF('L', 'mm', [145, 210], true, 'UTF-8', false); // Landscape, 145mm x 152mm
        $pdf->SetCreator('Your App');
        $pdf->SetAuthor('Your Name');
        $pdf->SetTitle('Delivery Order - ' . $goodReceipt->good_receipt_number);
        $pdf->SetSubject('Delivery Order');
        $pdf->SetKeywords('delivery, order, pdf');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(5, 5, 5);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();
        $pdf->SetFont('dejavusansmono', '', 10.5);

        // Build content with tables
        $content = '<style>
            table { font-family: dejavusansmono; font-size: 10.5pt; width: 100%; border-collapse: collapse; }
            td {  line-height: 1; } /* Added 2px horizontal padding */
            .right { text-align: right; }
            .left { text-align: left; }
            .divider { border-bottom: 1px solid black; padding: 0; margin: 0; height: 0; line-height: 0; }
        </style>';

        // $content = '<style>
        //     table { font-family: dejavusansmono; font-size: 10.5pt; width: 100%; border-collapse: collapse; }
        //     td { padding: 0; } /* Changed from 1px to 0 */
        //     .right { text-align: right; }
        //     .left { text-align: left; }
        //     .divider { border-bottom: 1px solid black; padding: 0; margin: 0; height: 0; line-height: 0; } /* Enhanced control */
        // </style>';

        // Top spacer
        $content .= '<div style="height: 10mm;"></div>';

        // Header Table
        $content .= '<table>';
        $content .= '<tr><td style="width: 40%;font-weight:bold;font-size:14px;">TDS, CV</td><td style="width: 60%;font-weight:bold;font-size:14px;">PENERIMAAN BARANG</td></tr>';
        $content .= '<tr><td style="width: 40%;">Kepada Yth.</td><td style="width: 60%;"></td></tr>';
        $content .= '<tr><td style="width: 40%;">' . htmlspecialchars($goodReceipt->supplier->supplier_name ?? 'N/A') . '</td>';
        $content .= '<td style="width: 60%; text-align: left;">No. Penerimaan Barang : ' . $goodReceipt->good_receipt_number . '</td></tr>';
        $content .= '<tr><td style="width: 40%;">' . htmlspecialchars($goodReceipt->supplier->address ?? 'N/A') . '</td>';
        $content .= '<td style="width: 60%; text-align: left;">No. Dokumen Supplier  : ' . htmlspecialchars($goodReceipt->vendor_number) . '</td></tr>';
        $content .= '<tr><td style="width: 40%;"></td>';
        $content .= '<td style="width: 60%; text-align: left;">Tanggal               : ' . Carbon::parse($goodReceipt->document_date)->format('d M Y') . '</td></tr>';
        $content .= '</table>';

        // Divider
        $content .= '<table><tr><td class="divider"></td></tr></table><br>';

        // Items Table
        $content .= '<table>';
        $content .= '<tr>';
        $content .= '<td style="width: 5%; border-right: 1px solid black;border-left: 1px solid black;">NO.</td>';
        $content .= '<td style="width: 67%; border-right: 1px solid black;">NAMA BARANG</td>';
        $content .= '<td style="width: 10%; border-right: 1px solid black; text-align: right;">COLY</td>';
        $content .= '<td style="width: 18%; text-align: right;border-right: 1px solid black;">QTY</td>';
        $content .= '</tr>';
        $content .= '<tr><td colspan="4" class="divider"></td></tr>';

        foreach ($details as $index => $detail) {
            $maxLength = 61;
            $itemName = $detail->item_name;
            $lines = explode("\n", wordwrap($itemName, $maxLength, "\n", false));

            foreach ($lines as $lineIndex => $line) {
                $content .= '<tr>';
                if ($lineIndex == 0) {
                    $content .= '<td style="width: 5%; text-align: left; border-right: 1px solid black;border-left: 1px solid black;">' . ($index + 1) . '.</td>';
                    $content .= '<td style="width: 67%; text-align: left; border-right: 1px solid black;">' . htmlspecialchars($line) . '</td>';
                    $content .= '<td style="width: 10%; text-align: right; border-right: 1px solid black;">' . number_format($detail->qty, 0) . '</td>';
                    $content .= '<td style="width: 18%; text-align: right;border-right: 1px solid black;">' . number_format($detail->base_qty * $detail->qty, 2, ',', '.') . ' ' . htmlspecialchars($detail->baseUnit) . '</td>';
                } else {
                    $content .= '<td style="width: 5%; border-right: 1px solid black;border-left: 1px solid black;"></td>';
                    $content .= '<td style="width: 67%; text-align: left; border-right: 1px solid black;">' . htmlspecialchars($line) . '</td>';
                    $content .= '<td style="width: 10%; border-right: 1px solid black;"></td>';
                    $content .= '<td style="width: 18%;border-right: 1px solid black;"></td>';
                }
                $content .= '</tr>';
            }
            $content .= '<tr><td colspan="4" class="divider"></td></tr>';
        }
        $content .= '</table>';

        // Write content
        $lineHeight = 3; // Approx. mm per line
        $lines = substr_count($content, '<tr>') + substr_count($content, '<br>');
        $totalHeight = $lines * $lineHeight;
        $maxHeightPerPage = 142; // 152mm - 10mm top spacer

        if ($totalHeight > $maxHeightPerPage) {
            $splitPoint = strpos($content, '<tr>', intval($maxHeightPerPage / $lineHeight) * strlen('<tr>'));
            $pdf->writeHTML(substr($content, 0, $splitPoint), true, false, true, false, '');
            $pdf->AddPage();
            $content = '<div style="height: 10mm;"></div>' . substr($content, $splitPoint);
        }

        $pdf->writeHTML($content, true, false, true, false, '');

        // Output PDF
        $pdf->Output('delivery_order_' . $goodReceipt->good_receipt_number . '.pdf', 'I');
    }

    public function cancel(Request $request, $id) {
        DB::beginTransaction();
        try {
            $general = GoodReceipt::findOrFail($id);
            $reason = $request->input('reason');

            $general->update([
                'cancel_notes'=>$reason,
                'status'=>'Cancelled'
            ]);
            DB::commit(); // Commit transaction
            return redirect()->route('transaction.warehouse.good_receipt')->with('success', 'Good Receipt cancelled successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->route('transaction.warehouse.good_receipt')->with('error', 'Error canceling: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id) {
        DB::beginTransaction();
        try {
            $GoodReceipt = GoodReceipt::findOrFail($id);

            $details = GoodReceiptDetail::where('good_receipt_number', $GoodReceipt->good_receipt_number)->get();
            foreach ($details as $key => $value) {
                $poDetail = PurchaseOrderDetail::where([
                    ['purchase_order_number', $value->purchase_order_number],
                    ['item_id', $value->item_id],
                    ['unit', $value->unit]
                ])->first();
                if($value->purchase_order_number){
                    $poDetail->qty_left = $poDetail->qty_left+$value->qty;
                    $poDetail->save();

                    $totalAll = PurchaseOrderDetail::where('purchase_order_number', $value->purchase_order_number)->count();

                    $checkLeft = PurchaseOrderDetail::where('purchase_order_number', $value->purchase_order_number)->whereColumn('qty_left', 'qty')->count();

                    if($checkLeft==$totalAll) {
                        PurchaseOrder::where('purchase_order_number',$value->purchase_order_number)->update(['status'=> 'Open']);
                    } else {
                        PurchaseOrder::where('purchase_order_number', $value->purchase_order_number)->update(['status'=> 'Partial']);
                    }
                }

                InventoryDetail::where('document_number', $GoodReceipt->good_receipt_number)->delete();

            }
            GoodReceiptDetail::where('good_receipt_number', $GoodReceipt->good_receipt_number)->delete();
            $GoodReceipt->delete();



            $reason = $request->input('reason');

            DeleteLog::create([
                'document_number' => $GoodReceipt->good_receipt_number,
                'document_date' => $GoodReceipt->document_date,
                'delete_notes' => $reason,
                'type' => 'GR',
                'company_code' => $GoodReceipt->company_code,
                'department_code' => $GoodReceipt->department_code,
                'deleted_by' => Auth::user()->username,
            ]);

            DB::commit();
            return redirect()->route('transaction.warehouse.good_receipt')->with('success', 'Good Receipt deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->route('transaction.warehouse.good_receipt')->with('error', 'Error deleting Good Receipt: ' . $e->getMessage());
        }
    }


}
