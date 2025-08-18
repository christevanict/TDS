<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Coa;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\CategorySupplier;
use App\Models\Debt;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemUnit;
use App\Models\ItemDetail;
use App\Models\ItemSalesPrice;
use App\Models\ItemPurchase;
use App\Models\Department;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceDetail;
use App\Models\GoodReceipt;
use App\Models\GoodReceiptDetail;
use App\Models\InventoryDetailRecap;
use App\Models\Journal;
use App\Models\PayablePayment;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceDetail;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnDetail;
use App\Models\SalesOrder;
use App\Models\TaxMaster;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use TCPDF;

class PurchaseReturnRecapController extends Controller
{
    public function index() {
        $companies = Company::all();
        $departments = Department::where('department_code', 'DP01')->first();
        $purchaseReturns = PurchaseReturn::where('recap', 'yes')->orderBy('id','desc')->get();
        $coas = COA::all();
        $suppliers = Supplier::where('department_code','DP01')->get();
        $prices = ItemSalesPrice::where('department_code','DP01')->get();
        $taxs = TaxMaster::all();
        $privileges = Auth::user()->roles->privileges['purchase_return'];

        return view('transaction.purchase-return-recap.purchase_return_list', compact('companies', 'departments', 'purchaseReturns', 'coas', 'suppliers', 'suppliers', 'prices', 'taxs','privileges'));
    }


    public function summary(Request $request)
    {
        // Initialize query for fetching purchase invoices
        $query = PurchaseInvoice::query();

        // Apply date filtering if 'from_date' and 'to_date' are present in the request
        if ($request->filled('from_date') && $request->filled('to_date')) {
            // Convert dates to Carbon instances for proper formatting and range filtering
            $fromDate = Carbon::parse($request->input('from_date'))->startOfDay();
            $toDate = Carbon::parse($request->input('to_date'))->endOfDay();

            // Apply the date range filter on the 'document_date' column
            $query->whereBetween('document_date', [$fromDate, $toDate]);
        }

        // Retrieve filtered purchase invoices
        $purchaseInvoices = $query->get();

        // Calculate the total amount from all filtered purchase invoices
        $totalAmount = $purchaseInvoices->sum('total');

        // Return the view with the purchase invoices data and total amount
        return view('transaction.purchase-return-recap.purchase_return_summary', compact('purchaseInvoices', 'totalAmount'))
        ->with([
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date')
        ]);
    }

    public function printPDF($purchase_return_number)
    {
        $purchaseInvoice = PurchaseReturn::with([
            'details.items',
            'details.units',
            'company',
            'department',
            'suppliers',
            'taxs',
        ])->where('id', $purchase_return_number)->firstOrFail();
        // $imagePath = storage_path('app/images/ttd.png');
        // $imageData = file_get_contents($imagePath);
        // Generate and return PDF

        $totalHuruf = ucfirst($this->numberToWords($purchaseInvoice->total)).' rupiah';
        return view('transaction.purchase-return-recap.purchase_return_pdf', compact('purchaseInvoice','totalHuruf'));
        $nameFile = Str::replace("/", "", $purchaseInvoice->purchase_return_number);
        return $pdf->stream("Purchase_Invoice_{$nameFile}.pdf");
    }

    public function printTc($purchase_return_number)
    {
        $purchaseInvoice = PurchaseReturn::with([
            'details.items',
            'details.units',
            'company',
            'department',
            'suppliers',
            'taxs',
        ])->where('id', $purchase_return_number)->firstOrFail();
        // $imagePath = storage_path('app/images/ttd.png');
        // $imageData = file_get_contents($imagePath);
        // Generate and return PDF
        $totalDiscount= 0;
        foreach ($purchaseInvoice->details as $key => $value) {
            $subtotal = $value->qty*$value->base_qty*$value->price;
            $totalDiscount+= ($subtotal *$value->disc_percent/100) +$value->disc_nominal;
        }
        $totalDiscount+=$purchaseInvoice->disc_nominal;
        $totalHuruf = ucfirst($this->numberToWords($purchaseInvoice->total)).' rupiah';

        // Initialize TCPDF
        $pdf = new TCPDF('L', 'mm', [145, 210], true, 'UTF-8', false); // Landscape, 145mm x 152mm
        $pdf->SetCreator('Your App');
        $pdf->SetAuthor('Your Name');
        $pdf->SetTitle('Purchase Invoice - ' . $purchaseInvoice->purchase_return_number);
        $pdf->SetSubject('Nota Pembelian');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(5, 5, 5);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();
        $pdf->SetFont('dejavusansmono', '', 9.5);

        // Build content with tables
        $content = '<style>
            table { font-family: dejavusansmono; font-size: 9.5pt; width: 100%; border-collapse: collapse; }
            td { padding-left: 2px; padding-right: 2px; line-height: 1; }
            .right { text-align: right; }
            .left { text-align: left; }
            .divider { border-bottom: 1px solid black; padding: 0; margin: 0; height: 0; line-height: 0; }
        </style>';


        // Header Table
        $content .= '<table>';
        $content .= '<tr><td style="height: 5mm;"></td></tr>';
        $content .= '<tr><td style="width: 47%; font-size:10.5px; font-weight:bold;">TDS, CV</td>
        <td style="width: 53%;font-size:14px;font-weight:bold;text-align:left;">NOTA RETUR PETDSLIAN</td></tr>';
        $content .= '<tr><td style="width: 40%;font-size:10.5px;">Kepada Yth.</td><td style="width: 60%;"></td></tr>';
        $content .= '<tr><td style="width: 40%;">' . htmlspecialchars($purchaseInvoice->suppliers->supplier_name ?? 'N/A') . '</td>';
        $content .= '<td style="width: 60%; text-align: left;">No. Invoice : ' . $purchaseInvoice->purchase_return_number . '</td></tr>';
        $content .= '<tr><td style="width: 40%;">' . htmlspecialchars($purchaseInvoice->suppliers->address ?? 'N/A') . '</td>';
        $content .= '<td style="width: 60%; text-align: left;">Tanggal     : ' . Carbon::parse($purchaseInvoice->document_date)->format('d M Y') . '</td></tr>';
        $content .= '</table>';

        // Divider
        $content .= '<table><tr><td class="divider"></td></tr></table>';

        // Items Table
        $content .= '<table>';
        $content .= '<tr>';
        $content .= '<td style="width: 5%; border-right: 1px solid black;border-left: 1px solid black;">NO</td>';
        $content .= '<td style="width: 50%; border-right: 1px solid black;">NAMA BARANG</td>';
        $content .= '<td style="width: 6%; border-right: 1px solid black; text-align: right;">COLY</td>';
        $content .= '<td style="width: 12%; border-right: 1px solid black; text-align: center;">QTY</td>';
        $content .= '<td style="width: 12%; border-right: 1px solid black; text-align: right;">HARGA</td>';
        $content .= '<td style="width: 15%; text-align: right;border-right: 1px solid black;">JUMLAH</td>';
        $content .= '</tr>';
        $content .= '<tr><td colspan="6" class="divider"></td></tr>';

        foreach ($purchaseInvoice->details as $index => $detail) {
            $maxLength = 35;
            $itemName = $detail->items->item_name;
            $lines = explode("\n", wordwrap($itemName, $maxLength, "\n", false));

            foreach ($lines as $lineIndex => $line) {
                $content .= '<tr>';
                if ($lineIndex == 0) {
                    $content .= '<td style="width: 5%; text-align: right; border-right: 1px solid black;border-left: 1px solid black;">' . ($index + 1) . '</td>';
                    $content .= '<td style="width: 50%; text-align: left; border-right: 1px solid black;">' . htmlspecialchars($line) . '</td>';
                    $content .= '<td style="width: 6%; text-align: right; border-right: 1px solid black;">' . number_format($detail->qty, 0) . '</td>';
                    $content .= '<td style="width: 12%; text-align: right; border-right: 1px solid black;">' . number_format($detail->base_qty * $detail->qty, 2, ',', '.') . ' ' . htmlspecialchars($detail->baseUnit->unit_name) . '</td>';
                    $content .= '<td style="width: 12%; text-align: right; border-right: 1px solid black;">' . number_format($detail->price, 0, ',', '.') . '</td>';
                    $content .= '<td style="width: 15%; text-align: right;border-right: 1px solid black;">' . number_format(($detail->qty * $detail->price * $detail->base_qty), 0, ',', '.') . '</td>';
                } else {
                    $content .= '<td style="width: 5%; border-right: 1px solid black;border-left: 1px solid black;"></td>';
                    $content .= '<td style="width: 50%; text-align: left; border-right: 1px solid black;">' . htmlspecialchars($line) . '</td>';
                    $content .= '<td style="width: 6%; border-right: 1px solid black;"></td>';
                    $content .= '<td style="width: 12%; border-right: 1px solid black;"></td>';
                    $content .= '<td style="width: 12%; border-right: 1px solid black;"></td>';
                    $content .= '<td style="width: 15%;border-right: 1px solid black;"></td>';
                }
                $content .= '</tr>';
            }
            $content .= '<tr><td colspan="6" class="divider"></td></tr>';
        }
        $content .= '<br>';

        // Footer Table (Terbilang and Totals)
        $maxLength = 50;
        $terbilang = "Terbilang: " . $totalHuruf;
        $terbilangLines = explode("\n", wordwrap($terbilang, $maxLength, "\n", false));
        while (count($terbilangLines) < 3) {
            $terbilangLines[] = '';
        }


        $content .= '<tr>';
        $content .= '<td colspan="3" style="width: 61%; vertical-align: top; font-weight: bold;font-size:9px;">' . htmlspecialchars($terbilangLines[0]) . '</td>';
        $content .= '<td colspan="2" style="width: 24%; text-align: right; border-bottom: 1px solid black;border-left: 1px solid black;border-right: 1px solid black;border-top: 1px solid black;">Sub Total</td>';
        $content .= '<td style="width: 15%; text-align: right;border-bottom: 1px solid black;border-right: 1px solid black;border-top: 1px solid black;">' . number_format($purchaseInvoice->subtotal + $totalDiscount, 0, ',', '.') . '</td>';
        $content .= '</tr>';

        // Row 2: Second Terbilang line (if any) + Discount
        $content .= '<tr>';
        $content .= '<td colspan="3" style="width: 61%; vertical-align: top; font-weight: bold;font-size:9px;">' . (isset($terbilangLines[1]) ? htmlspecialchars($terbilangLines[1]) : '') . '</td>';
        $content .= '<td colspan="2" style="width: 24%; text-align: right; border-bottom: 1px solid black;border-left: 1px solid black;border-right: 1px solid black;">Discount</td>';
        $content .= '<td style="width: 15%; text-align: right;border-bottom: 1px solid black;border-right: 1px solid black;">' . number_format($totalDiscount, 0, ',', '.') . '</td>';
        $content .= '</tr>';

        // Row 3: Third Terbilang line (if any) + PPN
        $content .= '<tr>';
        $content .= '<td colspan="3" style="width: 61%; vertical-align: top; font-weight: bold;font-size:9px;">' . (isset($terbilangLines[2]) ? htmlspecialchars($terbilangLines[2]) : '') . '</td>';
        $content .= '<td colspan="2" style="width: 24%; text-align: right; border-bottom: 1px solid black;border-left: 1px solid black;border-right: 1px solid black;">PPN</td>';
        $content .= '<td style="width: 15%; text-align: right;border-bottom: 1px solid black;border-right: 1px solid black;">' . number_format($purchaseInvoice->add_tax, 0, ',', '.') . '</td>';
        $content .= '</tr>';

        // Row 4: BCA KCP + Total Invoice
        $content .= '<tr>';
        $content .= '<td colspan="2" style="width: 55%; vertical-align: top; font-weight: bold;border: 1px solid black;font-size:9px;">BCA KCP MARGOMULYO 4700 36 8080<br>A/N: TDS</td>';
        $content .= '<td style="width:6%"></td>';
        $content .= '<td colspan="2" style="width: 24%; text-align: right; border-bottom: 1px solid black;border-left: 1px solid black;border-right: 1px solid black;">Total Invoice</td>';
        $content .= '<td style="width: 15%; text-align: right;border-bottom: 1px solid black;border-right: 1px solid black;">' . number_format($purchaseInvoice->total, 0, ',', '.') . '</td>';
        $content .= '</tr>';

        // Extra Terbilang lines (if any)
        if (isset($terbilangLines[3])) {
            foreach (array_slice($terbilangLines, 3) as $extraLine) {
                $content .= '<tr>';
                $content .= '<td colspan="3" style="width: 61%; vertical-align: top; font-weight: bold; font-size:9px;">' . htmlspecialchars($extraLine) . '</td>';
                $content .= '<td colspan="2" style="width: 24%;"></td>';
                $content .= '<td style="width: 15%;"></td>';
                $content .= '</tr>';
            }
        }

        $content .= '</table>'; // Close main table

        // Write content
        $pdf->writeHTML($content, true, false, true, false, '');

        // Output PDF
        $pdf->Output('purchase_invoice_' . $purchaseInvoice->purchase_return_number . '.pdf', 'I');
    }



    public function edit($id) {
        try {
            $companies = Company::all();
            $purchaseReturn = PurchaseReturn::with('details')->findOrFail($id);
            $departments = Department::where('department_code', $purchaseReturn->department_code)->first();
            $coas = Coa::all();
            $items = ItemPurchase::where('department_code',$purchaseReturn->department_code)->whereHas('items')->orderBy('item_code')->distinct('item_code')->with('items','unitn','itemDetails')->get();
            $itemDetails = ItemDetail::where('department_code',$purchaseReturn->department_code)->orderBy('item_code', 'asc')->get();
            $itemUnits = ItemUnit::all();
            $suppliers = Supplier::where('department_code',$purchaseReturn->department_code)->get();
            $prices = ItemSalesPrice::where('department_code',$purchaseReturn->department_code)->get();
            $taxs = TaxMaster::all();

            // Format dates for display
            $purchaseReturn->document_date = Carbon::parse($purchaseReturn->document_date)->format('Y-m-d');
            $privileges = Auth::user()->roles->privileges['purchase_return'];

            return view('transaction.purchase-return-recap.purchase_return_edit', compact('purchaseReturn', 'companies',  'departments', 'coas', 'items', 'itemUnits','itemDetails', 'suppliers', 'prices', 'taxs','privileges'));
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
            // Retrieve the PurchaseInvoice record by purchase_return_number

            $purchaseReturn = PurchaseReturn::where('id', $id)->firstOrFail();
            $purchaseReturn->notes = $request->notes;
            $purchaseReturn->document_date = $request->document_date;
            $purchaseReturn->disc_nominal = str_replace(',', '', $request->disc_nominal??0) ?? 0;
            $oldTotal = $purchaseReturn->total;
            $purchaseReturn->recap = 'yes';
            $oldPrDetails = PurchaseReturnDetail::where('purchase_return_number',
            $purchaseReturn->purchase_return_number)->get();


            Journal::where('document_number',$purchaseReturn->purchase_return_number)->delete();
            InventoryDetailRecap::where('document_number',$purchaseReturn->purchase_return_number)->delete();
            PurchaseReturnDetail::where('purchase_return_number',$purchaseReturn->purchase_return_number)->delete();

            Debt::where('document_number',$purchaseReturn->purchase_return_number)->delete();
            Debt::where('document_number',$purchaseReturn->purchase_return_number)->delete();

            $this->savePurchaseDetails($request->details,$purchaseReturn,$request);

            // Parse and assign date fields
            DB::commit(); // Commit the transaction
            return redirect()->route('transaction.purchase_return_recap')->with('success', 'Purchase Return edited successfully!');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            dd($e);
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to update Purchase Invoice: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, $id)
    {
        DB::beginTransaction(); // Start the transaction
        try {
            // Retrieve the PurchaseInvoice record by purchase_return_number

            $general = PurchaseInvoice::where('id', $id)->firstOrFail();
            $general->status = 'Ready';
            $general->save();
            // Parse and assign date fields
            DB::commit(); // Commit the transaction
            return redirect()->route('transaction.purchase_return_recap')->with('success', 'APPROVED');;
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to update Purchase Invoice: ' . $e->getMessage());
        }
    }

    public function destroy($id) {
        DB::beginTransaction();
        try {
            $general = PurchaseReturn::findOrFail($id);
            Debt::where('document_number',$general->purchase_return_number)->first()->delete();
            Journal::where('document_number',$general->purchase_return_number)->delete();
            InventoryDetailRecap::where('document_number',$general->purchase_return_number)->delete();
            PurchaseReturnDetail::where('purchase_return_number', $general->purchase_return_number)->delete();
            // foreach ($prDetails as $key => $value) {
            //     $piDetail = $piDetail = PurchaseInvoiceDetail::where([
            //         ['purchase_invoice_number',$general->purchase_invoice_number],
            //         ['item_id',$value->item_id],
            //         ['unit',$value->unit]
            //     ])->first();
            //     $piDetail->qty_left = $piDetail->qty_left+$value->qty;
            //     $piDetail->save();
            // }



            $general->delete();

            DB::commit(); // Commit transaction
            return redirect()->route('transaction.purchase_return_recap')->with('success', 'Purchase Invoice deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->route('transaction.purchase_return_recap')->with('error', 'Error deleting: ' . $e->getMessage());
        }
    }

    private function generatePurchaseReturnNumber($date,$department) {
        $today = Carbon::parse($date);
        $month = $today->format('n'); // Numeric representation of a month (1-12)
        $year = $today->format('y'); // Last two digits of the year

        // Convert month to Roman numeral
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        $romanMonth = $romanMonths[$month];
        // Fetch the last purchase order created in the current month
        $lastPurchaseOrder = PurchaseReturn::whereYear('created_at', $today->year)
            ->whereMonth('created_at', $month)
            ->where('department_code',$department)
            ->orderBy('purchase_return_number', 'desc')
            ->first();

        // Determine the new purchase order number
        if ($lastPurchaseOrder) {
            // Extract the last number from the last purchase order number
            $lastNumber = (int)substr($lastPurchaseOrder->purchase_return_number, strrpos($lastPurchaseOrder->purchase_return_number, '-') + 1);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            // Reset counter to 00001 if no purchase orders found for the current month
            $newNumber = '00001';
        }

        if($department=='DP02'){
            return "WIL/RPI/{$romanMonth}/{$year}-{$newNumber}";
        }else if($department=='DP03'){
            return "DRE/RPI/{$romanMonth}/{$year}-{$newNumber}";
        }else{
            return "TDS/RPI/{$romanMonth}/{$year}-{$newNumber}";
        }
    }

    public function changeDepartment(Request $request)
    {
        $department = $request->department_code;
        $suppliers = Supplier::where('department_code',$department)->get();
        $items = ItemPurchase::where('department_code',$department)->whereHas('items')->orderBy('item_code')->distinct('item_code')->with('items', 'unitn','itemDetails','items.warehouses')->get();
        $itemDetails = ItemDetail::where('department_code',$department)->get();
        return response()->json([
            'suppliers'=>$suppliers,
            'items'=>$items,
            'itemDetails'=>$itemDetails,
        ]);
    }

    public function create(Request $request)
    {

        $suppliers = Supplier::where('department_code','DP01')->get();
        $departments = Department::where('department_code', 'DP01')->first();

        // Additional data retrieval for the view
        $itemUnits = ItemUnit::orderBy('unit', 'asc')->get();
        $items = ItemPurchase::where('department_code','DP01')->whereHas('items')->orderBy('item_code')->distinct('item_code')->with('items','unitn','itemDetails')->get();
        $itemDetails = ItemDetail::where('department_code','DP01')->orderBy('item_code', 'asc')->get();
        $company = Company::first();
        $token = str()->random(16);

        // $purchaseInvoices = PurchaseInvoice::with('department','details')->orderBy('id', 'desc')->where('department_code', 'DP01')->get();

        // $purchaseInvoicesD = PurchaseInvoiceDetail::orderBy('id', 'asc')->with(['items','units'])->get();


        $taxs = TaxMaster::orderBy('tariff', 'asc')->get();
        $privileges = Auth::user()->roles->privileges['purchase_return'];
        return view('transaction.purchase-return-recap.purchase_return_input', compact(
            // 'salesOrders',
            'suppliers',
            'departments',
            'itemUnits',
            'items',
            'itemUnits',
            'itemDetails',
            'company',
            'taxs',
            'privileges',
            'token',
        ));
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
        $exist = PurchaseReturn::where('token',$request->token)->where('department_code',$request->department_code)->whereDate('created_at',Carbon::today())->first();
        if($exist){
            $id = PurchaseReturn::where('created_by',Auth::user()->username)->orderBy('id','desc')->select('id')->first()->id;
            return redirect()->route('transaction.purchase_return_recap.create')->with('success', 'Purchase Return created successfully.')->with('id',$id);
        }
        // Generate a new purchase order number
        $purchaseReturnNumber = $this->generatePurchaseReturnNumber($request->document_date,$request->department_code);
        // dd($request->all());
        DB::beginTransaction();
        try {
            // Generate a new PurchaseOrder instance
            $purchaseReturn = new PurchaseReturn();
            $purchaseReturn->purchase_return_number = $purchaseReturnNumber;
            // $purchaseReturn->good_receipt_number = $request->good_receipt_number;
            $purchaseReturn->supplier_code = $request->supplier_code;
            $purchaseReturn->document_date = $request->document_date;
            $purchaseReturn->token = $request->token;
            // Assign default values for discount
            $purchaseReturn->disc_percent = $request->disc_percent ?? 0;
            $purchaseReturn->disc_nominal = str_replace(',', '', $request->disc_nominal??0) ?? 0;
            $purchaseReturn->notes = $request->notes ?? '';
            // Set created_by and updated_by to the logged-in user
            $userId = Auth::id();
            $purchaseReturn->created_by = $userId;
            $purchaseReturn->updated_by = $userId;
            // Set tax from the selected dropdown
            $purchaseReturn->tax = 'PPN';
            $purchaseReturn->recap = 'yes';
            // Finalize total
            $purchaseReturn->company_code = $request->company_code ?? null;
            $purchaseReturn->department_code = $request->department_code;
            $purchaseReturn->purchase_invoice_number = $request->purchase_invoice_number??'';
            $purchaseReturn->include = $request->include ?? false;
            // $purchaseReturn->save();


            $this->savePurchaseDetails($request->details,$purchaseReturn,$request);

            $id = PurchaseReturn::where('purchase_return_number',$purchaseReturn->purchase_return_number)->select('id')->first()->id;

            DB::commit();
            return redirect()->route('transaction.purchase_return_recap.create')->with('success', 'Purchase Return created successfully.')->with('id',$id);
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            // Log the error for further analysis
            Log::error('Error creating Purchase Return: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create Purchase Invoice: ' . $e->getMessage())->withInput();
        }
    }

    private function savePurchaseDetails(array $details,$purchaseReturn,$request)
    {
            $nominal = 0;
            $revenueTax = 0;
            $addTax = 0;
            $services = 0;
            $taxed = 0;
            $totalAllAfterDiscountBeforeTax = 0;
            $totalAllItemBeforeTax = 0;
            $totalAllDiscountDetail = 0;
            $totalHPP = 0;
            $itemNameConcat = "";
            // Process purchase order details
            if (isset($details) && is_array($details)) {
                $rowNumber = 1; // Initialize row number for purchase order details
                // dd($request->details);

                foreach ($details as $detail) {
                    $detail['price'] = str_replace(',', '', $detail['price']);
                    $detail['disc_percent'] = str_replace(',', '', $detail['disc_percent']??0);
                    $detail['disc_nominal'] = str_replace(',', '', $detail['disc_nominal']??0);
                    $detail['nominal'] = str_replace(',', '', $detail['nominal']??0);
                    $supplier = Supplier::where('supplier_code', $purchaseReturn->supplier_code)->first();
                    $itemTax = Item::where('department_code',$purchaseReturn->department_code)->where('item_code', $detail['item_id'])->first();
                    $taxs = TaxMaster::where('tax_code', $purchaseReturn->tax)->first();

                    if($supplier->pkp == 1 && strtolower($itemTax->category->item_category_name) != 'service') {
                        if($supplier->include == 1) {
                            if ($itemTax->additional_tax == 1 ) {
                                $totalAllAfterDiscountBeforeTax += (($detail['qty']*$detail['price']*$detail['base_qty']) / (1 + $taxs->tax_base* $taxs->tariff / 100)) - ($detail['disc_percent']/100*(($detail['qty']*$detail['price']*$detail['base_qty']) / (1 + $taxs->tax_base* $taxs->tariff / 100)))-$detail['disc_nominal'];
                            } else {
                                $totalAllAfterDiscountBeforeTax += $detail['qty']*$detail['price']*$detail['base_qty'] - ($detail['disc_percent']/100*(($detail['qty']*$detail['price']*$detail['base_qty'])))-$detail['disc_nominal'];
                            }
                        } else {
                            $totalAllAfterDiscountBeforeTax += $detail['qty']*$detail['price']*$detail['base_qty'] - ($detail['disc_percent']/100*(($detail['qty']*$detail['price']*$detail['base_qty'])))-$detail['disc_nominal'];
                        }
                    }else {
                        $totalAllAfterDiscountBeforeTax += $detail['qty']*$detail['price']*$detail['base_qty'] - ($detail['disc_percent']/100*(($detail['qty']*$detail['price']*$detail['base_qty'])))-$detail['disc_nominal'];
                    }

                }


                foreach ($details as $detail) {
                    $detail['price'] = str_replace(',', '', $detail['price']);
                    $detail['disc_percent'] = str_replace(',', '', $detail['disc_percent']??0);
                    $detail['disc_nominal'] = str_replace(',', '', $detail['disc_nominal']??0);
                    $detail['nominal'] = str_replace(',', '', $detail['nominal']??0);
                    $detail['purchase_return_number'] = $purchaseReturn->purchase_return_number;
                    $detail['number_row'] = $rowNumber; // Correctly assign row number
                    $detail['company_code'] = $purchaseReturn->company_code;
                    $detail['department_code'] = $purchaseReturn->department_code;
                    $detail['created_by'] = Auth::user()->username;
                    $detail['updated_by'] = Auth::user()->username;
                    $detail['unit']=$detail['unit'];
                    $item = ItemDetail::where('department_code',$purchaseReturn->department_code)->where([
                        ['unit_conversion', $detail['unit']],
                        ['item_code',$detail['item_id']]
                        ])->first();
                    $detail['base_qty'] = $item->conversion;
                    $detail['qty_left'] = $detail['qty'];
                    $detail['base_qty_left'] = $detail['base_qty'];
                    $detail['base_unit'] = $item->base_unit;
                    $detail['description'] = $detail['notes']??'';
                    PurchaseReturnDetail::create($detail);
                    // dd($detail);


                    //Adjust Qty Left on PI Detail
                    // $piDetail = PurchaseInvoiceDetail::where([
                    //     ['purchase_invoice_number',$purchaseReturn->purchase_invoice_number],
                    //     ['item_id',$detail['item_id']],
                    //     ['unit',$detail['unit']]
                    // ])->first();

                    // $piDetail->qty_left = $piDetail->qty_left - $detail['qty'];
                    // $piDetail->save();

                    $supplier = Supplier::where('supplier_code', $purchaseReturn->supplier_code)->first();
                    $itemTax = Item::where('department_code',$purchaseReturn->department_code)->where('item_code', $detail['item_id'])->first();
                    $taxs = TaxMaster::where('tax_code', $purchaseReturn->tax)->first();

                    $subtotalBeforeTaxBeforeDisc =0;
                    $subtotalBeforeTaxAfterDisc =0;
                    $discTotalPerItem=0;
                    $totalPriceBeforeTaxBeforeDiscount = 0;
                    $totalPriceBeforeTaxAfterDiscount = 0;
                    $totalDiscountPerDetail = 0;
                    $discPerDetail = 0;
                    $itemTax = Item::where('department_code',$purchaseReturn->department_code)->where('item_code', $detail['item_id'])->first();


                    if ($supplier->pkp == 1) {
                        if (strtolower($itemTax->category->item_category_name) == 'service') {
                            $services += $detail['nominal'];
                        }

                        if($supplier->include == 1) {
                            if ($itemTax->additional_tax == 1) {
                                $totalPriceBeforeTaxBeforeDiscount = ($detail['qty']*$detail['price']*$detail['base_qty'])/(1 + $taxs->tax_base* $taxs->tariff / 100);
                                $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                                $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $purchaseReturn->disc_nominal;
                                $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                                $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                                $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];
                                $taxed += $totalPriceBeforeTaxBeforeDiscount;
                            }else{
                                $totalPriceBeforeTaxBeforeDiscount = $detail['qty']*$detail['price']*$detail['base_qty'];
                                $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                                $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $purchaseReturn->disc_nominal;
                                $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                                $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                                $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];
                            }
                        } else {
                            if ($itemTax->additional_tax == 1) {
                                $totalPriceBeforeTaxBeforeDiscount = $detail['qty']*$detail['price']*$detail['base_qty'];
                                $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                                $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $purchaseReturn->disc_nominal;
                                $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                                $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                                $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];
                                $taxed += $totalPriceBeforeTaxBeforeDiscount;
                            }else{
                                $totalPriceBeforeTaxBeforeDiscount = $detail['qty']*$detail['price']*$detail['base_qty'];
                                $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                                $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $purchaseReturn->disc_nominal;
                                $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                                $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                                $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];
                            }
                        }
                    } else {
                        $revenueTax = 0;
                        $addTax = 0;
                        $totalPriceBeforeTaxBeforeDiscount = $detail['qty']*$detail['price']*$detail['base_qty'];
                        $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                        $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $purchaseReturn->disc_nominal;
                        $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                        $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                        $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];
                    }
                    $detail['disc_header'] = $discPerDetail;
                    $discTotalPerItem = ($detail['disc_percent']/100 * $subtotalBeforeTaxBeforeDisc) + $detail['disc_header']??0 + $detail['disc_nominal'];
                    $subtotalBeforeTaxAfterDisc = $totalPriceBeforeTaxAfterDiscount - $discTotalPerItem;
                    $totalAllItemBeforeTax+=$totalPriceBeforeTaxAfterDiscount;

                    //PI Detail Nominal After discount
                    $suppliers = Supplier::where('supplier_code', $purchaseReturn->supplier_code)->first();
                    $itemTax = Item::where('department_code',$purchaseReturn->department_code)->where('item_code', $detail['item_id'])->first();
                    $categories;
                    if($itemTax){
                        $categories = ItemCategory::where('item_category_code', $itemTax->item_category)->first();
                    }

                    $PODJournal1 = new Journal();
                    $PODJournal1->document_number = $detail['purchase_return_number'];
                    $PODJournal1->document_date = $purchaseReturn->document_date;
                    $PODJournal1->account_number = $categories->acc_number_purchase_return??'1001';
                    $PODJournal1->debet_nominal = 0;
                    $PODJournal1->credit_nominal = $subtotalBeforeTaxAfterDisc;
                    $PODJournal1->notes ='Return '. $itemTax->item_name.' ('. $item->unitConversion->unit_name.') : '.$detail['qty'];
                    $PODJournal1->company_code = $purchaseReturn->company_code;
                    $PODJournal1->department_code = $detail['department_code'];
                    $PODJournal1->created_by = Auth::user()->username;
                    $PODJournal1->updated_by = Auth::user()->username;

                    $PODJournal1 -> save();


                    $totalHPP+=$totalPriceBeforeTaxAfterDiscount;
                    $itemNameConcat.=$itemTax->item_name." | ";
                    $firstQ = InventoryDetailRecap::where('item_id', $detail['item_id'])->orderBy('id','desc')->first();

                    $firstQty = $firstQ->last_qty??0;


                    $crAt = Carbon::now('Asia/Jakarta');
                    if(!is_null($purchaseReturn->id)){
                        $crAt = $purchaseReturn->created_at;
                    }
                    $item = Item::where('item_code',$detail['item_id'])->first();
                    $gd = Warehouse::where("warehouse_code",$item->warehouse_code)->first();
                    $cogs = Module::getCogs($purchaseReturn->document_date,$detail['item_id'],$purchaseReturn->company_code,$purchaseReturn->department_code,$gd->id);

                    InventoryDetailRecap::insert([
                        'document_number'=>$purchaseReturn->purchase_return_number,
                        'document_date'=>$purchaseReturn->document_date,
                        'transaction_type'=>'Purchase Return',
                        'from_to'=>$purchaseReturn->supplier_code,
                        'item_id'=>$detail['item_id'],
                        'quantity'=>$detail['qty'],
                        'unit'=>$detail['unit'],
                        'base_quantity'=>$detail['base_qty'],
                        'unit_base'=>$detail['base_unit'],
                        'company_code'=>$purchaseReturn->company_code,
                        'department_code'=>$purchaseReturn->department_code,
                        'first_qty'=>$firstQty,
                        'last_qty'=>$firstQty-($detail['qty']*$detail['base_qty']),
                        'created_at' => $crAt,
                        'updated_at' => Carbon::now('Asia/Jakarta'),
                        'created_by' => $purchaseReturn->created_by,
                        'updated_by' => Auth::user()->username,
                        'warehouse_id' => $gd->id,
                        'total' => $totalPriceBeforeTaxAfterDiscount* -1,
                        'cogs' => ($cogs * ($detail['qty'] * $detail['base_qty']))* -1,
                        'qty_actual' => $detail['qty'] *(-1)
                    ]);

                    $rowNumber++;
                }
            }

            //PR HPP
            $PODJournal1 = new Journal();
            $PODJournal1->document_number = $purchaseReturn->purchase_return_number;
            $PODJournal1->document_date = $purchaseReturn->document_date;
            $PODJournal1->account_number = $categories->acc_cogs??'1001';
            $PODJournal1->debet_nominal = $totalHPP;
            $PODJournal1->credit_nominal = 0;
            $PODJournal1->notes ='HPP '. $itemNameConcat;
            $PODJournal1->company_code = $purchaseReturn->company_code;
            $PODJournal1->department_code = $detail['department_code'];
            $PODJournal1->created_by = Auth::user()->username;
            $PODJournal1->updated_by = Auth::user()->username;
            $PODJournal1 -> save();

            //PR Persediaan
            $PODJournal1 = new Journal();
            $PODJournal1->document_number = $purchaseReturn->purchase_return_number;
            $PODJournal1->document_date = $purchaseReturn->document_date;
            $PODJournal1->account_number = $categories->account_inventory??'1001';
            $PODJournal1->debet_nominal = 0;
            $PODJournal1->credit_nominal = $totalHPP;
            $PODJournal1->notes ='Persediaan '. $itemNameConcat;
            $PODJournal1->company_code = $purchaseReturn->company_code;
            $PODJournal1->department_code = $purchaseReturn->department_code;
            $PODJournal1->created_by = Auth::user()->username;
            $PODJournal1->updated_by = Auth::user()->username;
            $PODJournal1 -> save();


            // dd($taxed);
            $supplier = Supplier::where('supplier_code', $purchaseReturn->supplier_code)->first();
            if ($supplier->pkp == 1) {
                $addTax = $taxed *$taxs->tax_base * $taxs->tariff/100;
            } else {
                $addTax = 0;
            }


            $purchaseReturn->add_tax = $addTax;

            $purchaseReturn->subtotal = $totalAllItemBeforeTax;
            $purchaseReturn->total = $purchaseReturn->subtotal + $purchaseReturn->add_tax;

            $purchaseReturn->save();
// dd($purchaseReturn);


            //PI Header Total
            $POJournal = new Journal();
            $suppliers = Supplier::where('supplier_code', $purchaseReturn->supplier_code)->first();
            $itemTax = Item::where('department_code',$purchaseReturn->department_code)->where('item_code', $detail['item_id'])->first();
            $taxes = TaxMaster::where('tax_code', $purchaseReturn->tax)->first();
            // dd($POJournal);
            $POJournal->document_number = $purchaseReturn->purchase_return_number;
            $POJournal->document_date = $purchaseReturn->document_date;
            $POJournal->account_number = $suppliers->account_payable;
            $POJournal->debet_nominal = $purchaseReturn->total;
            $POJournal->credit_nominal = 0;
            $POJournal->notes = 'Return Purchase from '.$request->supplier_name.' ('.$purchaseReturn->purchase_return_number.')';
            $POJournal->company_code = $detail['company_code'];
            $POJournal->department_code = $detail['department_code'];
            $POJournal->created_by = Auth::user()->username;
            $POJournal->updated_by = Auth::user()->username;

            $POJournal -> save();




            //PI Header Add Tax

            $POJournal2 = new Journal();
            $suppliers = Supplier::where('supplier_code', $request->supplier_code)->first();
            $itemTax = Item::where('department_code',$purchaseReturn->department_code)->where('item_code', $detail['item_id'])->first();
            $POJournal2->document_number = $purchaseReturn->purchase_return_number;
            $POJournal2->document_date = $purchaseReturn->document_date;
            $POJournal2->account_number = $suppliers->account_add_tax;
            $POJournal2->debet_nominal = 0;
            $POJournal2->credit_nominal = $purchaseReturn->add_tax;
            $POJournal2->notes = 'Return Add. tax on purchase from '.$request->supplier_name.' ('.$purchaseReturn->purchase_return_number.')';
            $POJournal2->company_code = $detail['company_code'];
            $POJournal2->department_code = $detail['department_code'];
            $POJournal2->created_by = Auth::user()->username;
            $POJournal2->updated_by = Auth::user()->username;

            $POJournal2 -> save();


            Debt::create([
                'document_number'=>$purchaseReturn->purchase_return_number,
                'document_date'=>$purchaseReturn->document_date,
                'due_date'=>$request->due_date,
                'total_debt'=>$purchaseReturn->total*(-1),
                'debt_balance'=>$purchaseReturn->total*(-1),
                'supplier_code'=>$purchaseReturn->supplier_code,
                'due_date'=>$request->due_date,
                'company_code'=>$purchaseReturn->company_code,
                'department_code'=>$purchaseReturn->department_code,
                'created_by' => Auth::user()->username,
                'updated_by' => Auth::user()->username,
            ]);

    }
}
