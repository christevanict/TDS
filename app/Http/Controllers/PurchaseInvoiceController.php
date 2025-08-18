<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Coa;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\CategorySupplier;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemUnit;
use App\Models\ItemDetail;
use App\Models\InventoryDetailPurchase;
use App\Models\ItemSalesPrice;
use App\Models\ItemPurchase;
use App\Models\DeleteLog;
use App\Models\Department;
use App\Models\Journal;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceDetail;
use App\Models\GoodReceipt;
use App\Models\GoodReceiptDetail;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceDetail;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\TaxMaster;
use App\Models\Debt;
use App\Models\InventoryDetail;
use App\Models\PayablePayment;
use App\Models\PayablePaymentDetail;
use App\Models\PurchaseDebtCreditNote;
use App\Models\PurchaseReturn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use TCPDF;

class PurchaseInvoiceController extends Controller
{
    public function index() {
        $companies = Company::all();
        $departments = Department::where('department_code', 'DP01')->first();
        $purchaseInvoices = PurchaseInvoice::where('department_code', 'DP01')->with('debts')->orderBy('document_date','desc')->orderBy('id','desc')->get();
        $coas = COA::all();
        $suppliers = Supplier::where('department_code','DP01')->get();
        $prices = ItemSalesPrice::where('department_code','DP01')->get();
        $taxs = TaxMaster::all();
        $privileges = Auth::user()->roles->privileges['purchase_invoice'];

        return view('transaction.purchase-invoice.purchase_invoice_list', compact('companies', 'departments', 'purchaseInvoices', 'coas',  'suppliers', 'suppliers', 'prices', 'taxs','privileges'));
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
            return redirect()->route('transaction.purchase_invoice')->with('success', 'Purchase Invoice cancelled successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->route('transaction.purchase_invoice')->with('error', 'Error canceling: ' . $e->getMessage());
        }
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
        $privileges = Auth::user()->roles->privileges['purchase_invoice'];

        // Return the view with the purchase invoices data and total amount
        return view('transaction.purchase-invoice.purchase_invoice_summary', compact('purchaseInvoices', 'totalAmount','privileges'))
        ->with([
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date')
        ]);
    }

    public function summaryDetail(Request $request)
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
        $purchaseInvoices = $query->with('details')->where('department_code','DP01')->orderBy('id','asc')->get();

        // Calculate the total amount from all filtered purchase invoices
        $totalAmount = $purchaseInvoices->sum('total');
        $privileges = Auth::user()->roles->privileges['purchase_invoice'];

        // Return the view with the purchase invoices data and total amount
        return view('transaction.purchase-invoice.purchase_invoice_summary_detail', compact('purchaseInvoices', 'totalAmount','privileges'))
        ->with([
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date')
        ]);
    }

    public function printPDF($purchase_invoice_number)
    {
        $purchaseInvoice = PurchaseInvoice::with([
            'details.items',
            'details.units',
            'company',
            'department',
            'suppliers',
            'taxs',
        ])->where('id', $purchase_invoice_number)->firstOrFail();
        $totalDiscount= 0;
        foreach ($purchaseInvoice->details as $key => $value) {
            $subtotal = $value->qty*$value->base_qty*$value->price;
            $totalDiscount+= ($subtotal *$value->disc_percent/100) +$value->disc_nominal;
        }
        $totalDiscount+=$purchaseInvoice->disc_nominal;
        // Generate and return PDF
        $totalHuruf = ucfirst($this->numberToWords($purchaseInvoice->total)).' rupiah';
        return view('transaction.purchase-invoice.purchase_invoice_pdf', compact('purchaseInvoice','totalHuruf','totalDiscount'));
        $nameFile = Str::replace("/", "", $purchaseInvoice->purchase_invoice_number);
        return $pdf->stream("Purchase_Invoice_{$nameFile}.pdf");
    }

    public function printPdfTc($purchase_invoice_number)
    {
        $purchaseInvoice = PurchaseInvoice::with([
            'details.items',
            'details.units',
            'company',
            'department',
            'suppliers',
            'taxs',
        ])->where('id', $purchase_invoice_number)->firstOrFail();
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
        $pdf->SetTitle('Purchase Invoice - ' . $purchaseInvoice->purchase_invoice_number);
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
        <td style="width: 53%;font-size:14px;font-weight:bold;text-align:left;">NOTA PETDSLIAN</td></tr>';
        $content .= '<tr><td style="width: 40%;font-size:10.5px;">Kepada Yth.</td><td style="width: 60%;"></td></tr>';
        $content .= '<tr><td style="width: 40%;">' . htmlspecialchars($purchaseInvoice->suppliers->supplier_name ?? 'N/A') . '</td>';
        $content .= '<td style="width: 60%; text-align: left;">No. Invoice : ' . $purchaseInvoice->purchase_invoice_number . '</td></tr>';
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
        $pdf->Output('purchase_invoice_' . $purchaseInvoice->purchase_invoice_number . '.pdf', 'I');
    }

    public function printNettoTc($purchase_invoice_number)
    {
        $purchaseInvoice = PurchaseInvoice::with([
            'details.items',
            'details.units',
            'company',
            'department',
            'suppliers',
            'taxs',
        ])->where('id', $purchase_invoice_number)->firstOrFail();
        $totalDiscount= 0;
        foreach ($purchaseInvoice->details as $key => $value) {
            $subtotal = $value->qty*$value->base_qty*$value->price;
            $totalDiscount+= ($subtotal *$value->disc_percent/100) +$value->disc_nominal;
        }
        $totalDiscount+=$purchaseInvoice->disc_nominal;
        $tax = TaxMaster::where('tax_code','PPN')->first();
        $totalHuruf = ucfirst($this->numberToWords($purchaseInvoice->total)).' rupiah';
        // Initialize TCPDF
        $pdf = new TCPDF('L', 'mm', [145, 210], true, 'UTF-8', false); // Landscape, 145mm x 152mm
        $pdf->SetCreator('Your App');
        $pdf->SetAuthor('Your Name');
        $pdf->SetTitle('Purchase Invoice - ' . $purchaseInvoice->purchase_invoice_number);
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
        <td style="width: 53%;font-size:14px;font-weight:bold;text-align:left;">NOTA PETDSLIAN NETTO</td></tr>';
        $content .= '<tr><td style="width: 40%;font-size:10.5px;">Kepada Yth.</td><td style="width: 60%;"></td></tr>';
        $content .= '<tr><td style="width: 40%;">' . htmlspecialchars($purchaseInvoice->suppliers->supplier_name ?? 'N/A') . '</td>';
        $content .= '<td style="width: 60%; text-align: left;">No. Invoice : ' . $purchaseInvoice->purchase_invoice_number . '</td></tr>';
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
                    $content .= '<td style="width: 12%; text-align: right; border-right: 1px solid black;">' . number_format($detail->price + ($detail->price*$tax->tax_base*$tax->tariff/100), 0,',','.') . '</td>';
                    $content .= '<td style="width: 15%; text-align: right;border-right: 1px solid black;">' . number_format(($detail->price*$detail->qty*$detail->base_qty) + ($detail->price*$detail->qty*$detail->base_qty*$tax->tax_base*$tax->tariff/100), 0,',','.') . '</td>';
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
        $content .= '<td style="width: 15%; text-align: right;border-bottom: 1px solid black;border-right: 1px solid black;border-top: 1px solid black;">' . number_format($purchaseInvoice->subtotal+$purchaseInvoice->add_tax+$totalDiscount+ ($totalDiscount*$tax->tax_base*$tax->tariff/100), 0,',','.') . '</td>';
        $content .= '</tr>';

        // Row 2: Second Terbilang line (if any) + Discount
        $content .= '<tr>';
        $content .= '<td colspan="3" style="width: 61%; vertical-align: top; font-weight: bold;font-size:9px;">' . (isset($terbilangLines[1]) ? htmlspecialchars($terbilangLines[1]) : '') . '</td>';
        $content .= '<td colspan="2" style="width: 24%; text-align: right; border-bottom: 1px solid black;border-left: 1px solid black;border-right: 1px solid black;">Discount</td>';
        $content .= '<td style="width: 15%; text-align: right;border-bottom: 1px solid black;border-right: 1px solid black;">' . number_format(($totalDiscount)+ ($totalDiscount*$tax->tax_base*$tax->tariff/100), 0, ',', '.') . '</td>';
        $content .= '</tr>';

        // Row 3: Third Terbilang line (if any) + PPN
        $content .= '<tr>';
        $content .= '<td colspan="3" style="width: 61%; vertical-align: top; font-weight: bold;font-size:9px;">' . (isset($terbilangLines[2]) ? htmlspecialchars($terbilangLines[2]) : '') . '</td>';
        $content .= '<td colspan="2" rowspan="2" style="width: 24%; text-align: right; border-bottom: 1px solid black;border-left: 1px solid black;border-right: 1px solid black;">Total Invoice</td>';
        $content .= '<td rowspan="2" style="width: 15%; text-align: right;border-bottom: 1px solid black;border-right: 1px solid black;">' . number_format($purchaseInvoice->total, 0, ',', '.') . '</td>';
        $content .= '</tr>';

        // Row 4: BCA KCP + Total Invoice
        $content .= '<tr>';
        $content .= '<td colspan="2" style="width: 55%; vertical-align: top; font-weight: bold;border: 1px solid black;font-size:9px;">BCA KCP MARGOMULYO 4700 36 8080<br>A/N: TDS</td>';
        $content .= '<td style="width:6%"></td>';
        $content .= '<td colspan="2" style="width: 24%; text-align: right;"></td>';
        $content .= '<td style="width: 15%; text-align: right;"></td>';
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
        $pdf->Output('purchase_invoice_' . $purchaseInvoice->purchase_invoice_number . '.pdf', 'I');
    }

    public function printPDFNetto($purchase_invoice_number)
    {
        $purchaseInvoice = PurchaseInvoice::with([
            'details.items',
            'details.units',
            'company',
            'department',
            'suppliers',
            'taxs',
        ])->where('id', $purchase_invoice_number)->firstOrFail();
        $tax = TaxMaster::where('tax_code','PPN')->first();
        $totalDiscount= 0;
        foreach ($purchaseInvoice->details as $key => $value) {
            $subtotal = $value->qty*$value->base_qty*$value->price;
            $totalDiscount+= ($subtotal *$value->disc_percent/100) +$value->disc_nominal;
        }
        $totalDiscount+=$purchaseInvoice->disc_nominal;
        // Generate and return PDF
        $totalHuruf = ucfirst($this->numberToWords($purchaseInvoice->total)).' rupiah';
        return view('transaction.purchase-invoice.purchase_invoice_netto_pdf', compact('purchaseInvoice','tax','totalHuruf','totalDiscount'));
        $nameFile = Str::replace("/", "", $purchaseInvoice->purchase_invoice_number);
        return $pdf->stream("Purchase_Invoice_{$nameFile}.pdf");
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



    public function edit($id) {
        try {
            $companies = Company::all();
            $departments = Department::where('department_code', 'DP01')->first();
            $coas = Coa::all();
            $items = ItemPurchase::where('department_code','DP01')->whereHas('items')->orderBy('item_code')->distinct('item_code')->with('items', 'unitn','itemDetails','items.warehouses')->get();
            $itemUnits = ItemUnit::all();
            $itemDetails = ItemDetail::where('department_code','DP01')->where('status',true)->get();
            $suppliers = Supplier::where('department_code','DP01')->get();
            $taxs = TaxMaster::all();

            $purchaseOrder = PurchaseInvoice::with('details')->findOrFail($id);
            // dd($PurchaseInvoice);
            $goodReceipt = GoodReceipt::with('department')->orderBy('id', 'asc')->where('status', 'Open')->where('department_code', 'DP01')->get();
            $goodReceiptD = GoodReceiptDetail::orderBy('id', 'asc')->with(['items','units'])->get();
            $purchaseOrderDetails = PurchaseInvoiceDetail::where('purchase_invoice_number', $purchaseOrder->purchase_invoice_number)->with(['items','units'])->get();
            // Format dates for display
            $purchaseOrder->document_date = Carbon::parse($purchaseOrder->document_date)->format('Y-m-d');
            $purchaseOrder->delivery_date = Carbon::parse($purchaseOrder->delivery_date)->format('Y-m-d');
            $purchaseOrder->due_date = Carbon::parse($purchaseOrder->due_date)->format('Y-m-d');

            $editable = true;
            $payable = PayablePaymentDetail::where('document_number',$purchaseOrder->purchase_invoice_number)->get();
            $returns = PurchaseReturn::where('purchase_invoice_number',$purchaseOrder->purchase_invoice_number)->get();
            $note = PurchaseDebtCreditNote::where('invoice_number',$purchaseOrder->purchase_invoice_number)->get();
            $editable = count($payable)>0 ||count($returns)>0||count($note)>0 ? false:true;
            $privileges = Auth::user()->roles->privileges['purchase_invoice'];

            return view('transaction.purchase-invoice.purchase_invoice_edit', compact('purchaseOrder', 'companies', 'goodReceipt','goodReceiptD', 'departments', 'coas', 'items', 'itemUnits','itemDetails', 'suppliers', 'suppliers', 'taxs', 'purchaseOrderDetails','editable','privileges'));
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with('error', 'Failed to load edit form: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // Generate a new PurchaseOrder instance
            $purchaseOrder = PurchaseInvoice::findOrFail($id);
            // $purchaseOrder->good_receipt_number = $request->good_receipt_number;
            $purchaseOrder->delivery_date = $request->delivery_date;
            $purchaseOrder->due_date = $request->due_date;
            $purchaseOrder->document_date = $request->document_date;
            $purchaseOrder->tax_revenue_tariff = $request->tax_revenue;
            // Assign default values for discount
            $purchaseOrder->manual_number = $request->manual_number;
            $purchaseOrder->disc_percent = $request->disc_percent ?? 0;
            $purchaseOrder->disc_nominal = str_replace(',', '', $request->disc_nominal??0);
            $purchaseOrder->notes = $request->notes ?? '';
            // Set created_by and updated_by to the logged-in user
            $userId = Auth::id();
            $purchaseOrder->updated_by = $userId;
            // Set tax from the selected dropdown
            $purchaseOrder->tax = 'PPN';
            // Finalize total
            // $purchaseOrder->company_code = $request->company_code ?? null;
            // $purchaseOrder->department_code = 'DP01' ?? null;
            // $purchaseOrder->include = $request->include ?? false;
            // $purchaseOrder->save();
            $purchaseOrder->tax_revenue_tariff = $request->tax_revenue;
            $tax_revenue_tariff=0;
            if($request->tax_revenue!=0){
                $tax_revenue_tariffs = TaxMaster::where('tax_code',$request->tax_revenue)->first();
                $tax_revenue_tariff = $tax_revenue_tariffs->tariff;
            }

            Journal::where('document_number', $purchaseOrder->purchase_invoice_number)->delete();

            $purchaseInvoiceNumber = $purchaseOrder->purchase_invoice_number;
            $nominal = 0;
            $revenueTax = 0;
            $addTax = 0;
            $services = 0;
            $taxed = 0;

            PurchaseInvoiceDetail::where('purchase_invoice_number', $purchaseOrder->purchase_invoice_number)->delete();


            // Process purchase order details
            Debt::where('document_number', $purchaseInvoiceNumber)->delete();
            InventoryDetailPurchase::where('document_number',$purchaseInvoiceNumber)->delete();
            $this->savePurchaseInvoiceDetails($request->details,$purchaseOrder);

            DB::commit();
            return redirect()->route('transaction.purchase_invoice')->with('success', 'Purchase Invoice created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to create Purchase Invoice: ' . $e->getMessage())->withInput();
        }
    }

    public function updateStatus(Request $request, $id)
    {
        DB::beginTransaction(); // Start the transaction
        try {
            // Retrieve the PurchaseInvoice record by purchase_invoice_number

            $general = PurchaseInvoice::where('id', $id)->firstOrFail();
            $general->status = 'Ready';
            $general->save();
            // Parse and assign date fields
            DB::commit(); // Commit the transaction
            return redirect()->route('transaction.purchase_invoice')->with('success', 'APPROVED');;
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to update Purchase Invoice: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id) {
        DB::beginTransaction();
        try {
            $general = PurchaseInvoice::findOrFail($id);
            $piDetails = PurchaseInvoiceDetail::where('purchase_invoice_number', $general->purchase_invoice_number)->get();
            Journal::where('document_number',$general->purchase_invoice_number)->delete();
            foreach ($piDetails as $key => $value) {
                // $exactInventDetail = InventoryDetail::where([
                //     ['document_number',$general->purchase_invoice_number],
                //     ['item_id',$value->item_id],
                //     ['unit',$value->unit],
                // ])->first();
                // $qty = $exactInventDetail->quantity;
                // $nextInventoryDetail = InventoryDetail::where([
                //     ['id','>',$exactInventDetail->id],
                //     ['item_id',$value->item_id],
                //     ['unit',$value->unit],
                // ])->get();
                // foreach ($nextInventoryDetail as $detail) {
                //     $detail->first_qty -= $qty; // Subtract qty from first_qty
                //     $detail->last_qty -= $qty;  // Subtract qty from last_qty
                //     $detail->updated_by = Auth::user()->username;
                //     $detail->save(); // Save the changes to the database
                // }
                if($value->good_receipt_number){
                    GoodReceipt::where('good_receipt_number',$value->good_receipt_number)->update(['status'=>'Open']);
                }
                // $exactInventDetail->delete();
            }
            Debt::where('document_number',$general->purchase_invoice_number)->delete();
            $piDetails->each->delete();
            $general->delete();
            InventoryDetailPurchase::where('document_number',$general->purchase_invoice_number)->delete();

            $reason = $request->input('reason');

            DeleteLog::create([
                'document_number' => $general->purchase_invoice_number,
                'document_date' => $general->document_date,
                'delete_notes' => $reason,
                'type' => 'PI',
                'company_code' => $general->company_code,
                'department_code' => $general->department_code,
                'deleted_by' => Auth::user()->username,
            ]);

            DB::commit(); // Commit transaction
            return redirect()->route('transaction.purchase_invoice')->with('success', 'Purchase Invoice deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->route('transaction.purchase_invoice')->with('error', 'Error deleting: ' . $e->getMessage());
        }
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
        $department = 'DP01';
        // Fetch the last purchase order created in the current month
        $lastPurchaseOrder = PurchaseInvoice::whereYear('created_at', $today->year)
            ->whereMonth('created_at', $month)
            ->where('department_code','DP01')
            ->orderBy('purchase_invoice_number', 'desc')
            ->first();

        // Determine the new purchase order number
        if ($lastPurchaseOrder) {
            // Extract the last number from the last purchase order number
            $lastNumber = (int)substr($lastPurchaseOrder->purchase_invoice_number, strrpos($lastPurchaseOrder->purchase_invoice_number, '-') + 1);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            // Reset counter to 00001 if no purchase orders found for the current month
            $newNumber = '00001';
        }

        // Return the new purchase order number in the desired format
        return "TDS/PUI/{$romanMonth}/{$year}-{$newNumber}";
    }

    public function create(Request $request)
    {

        $suppliers = Supplier::where('department_code','DP01')->get();
        $departments = Department::where('department_code', 'DP01')->first();

        $items = ItemPurchase::where('department_code','DP01')->whereHas('items')->orderBy('item_code')->distinct('item_code')->with('items', 'unitn','itemDetails','items.warehouses')->get();
        $itemUnits = ItemUnit::all();
        $itemDetails = ItemDetail::where('department_code','DP01')->where('status',true)->get();
        $prices = ItemPurchase::where('department_code','DP01')->orderBy(
            'item_code',
            'asc'
        )->get();
        $token = str()->random(16);
        $company = Company::first();

        $goodReceipt = GoodReceipt::with('department')->orderBy('id', 'asc')->where('status', 'Open')->where('department_code', 'DP01')->get();
        $goodReceiptD = GoodReceiptDetail::orderBy('id', 'asc')->with(['items','units'])->get();


        $taxs = TaxMaster::orderBy('tariff', 'asc')->get();
        $privileges = Auth::user()->roles->privileges['purchase_invoice'];
        return view('transaction.purchase-invoice.purchase_invoice_input', compact(
            // 'salesOrders',
            'suppliers',
            'departments',
            'items',
            'itemDetails',
            'goodReceipt',
            'goodReceiptD',
            'itemUnits',
            'prices',
            'company',
            'taxs',
            'privileges',
            'token',
        ));
    }
    public function store(Request $request)
    {
        $exist = PurchaseInvoice::where('token',$request->token)->where('department_code','DP01')->whereDate('created_at',Carbon::today())->first();
        if($exist){
            $id = PurchaseInvoice::where('created_by',Auth::user()->username)->orderBy('id','desc')->select('id')->first()->id;
            return redirect()->route('transaction.purchase_invoice.create')->with('success', 'Purchase Invoice created successfully.')->with('id',$id);
        }
        // Generate a new purchase order number
        $purchaseInvoiceNumber = $this->generatePurchaseInvoiceNumber();
        DB::beginTransaction();
        try {
            // Generate a new PurchaseOrder instance
            $purchaseOrder = new PurchaseInvoice();
            $purchaseOrder->purchase_invoice_number = $purchaseInvoiceNumber;
            // $purchaseOrder->good_receipt_number = $request->good_receipt_number;
            $purchaseOrder->supplier_code = $request->supplier_code;
            $purchaseOrder->document_date = $request->document_date;
            $purchaseOrder->delivery_date = $request->delivery_date;
            $purchaseOrder->due_date = $request->due_date;
            $purchaseOrder->token = $request->token;
            $purchaseOrder->manual_number = $request->manual_number;
            $purchaseOrder->tax_revenue_tariff = $request->tax_revenue;
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
            // Finalize total
            $purchaseOrder->company_code = $request->company_code ?? null;
            $purchaseOrder->department_code = 'DP01';
            $purchaseOrder->include = $request->include ?? false;
            // $purchaseOrder->save();
            $purchaseOrder->tax_revenue_tariff = $request->tax_revenue;


            $this->savePurchaseInvoiceDetails($request->details,$purchaseOrder);


            $id = PurchaseInvoice::where('purchase_invoice_number',$purchaseInvoiceNumber)->select('id')->first()->id;

            DB::commit();
            return redirect()->route('transaction.purchase_invoice.create')->with('success', 'Purchase Invoice created successfully.')->with('id',$id);
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to create Purchase Invoice: ' . $e->getMessage())->withInput();
        }
    }

    private function savePurchaseInvoiceDetails(array $piDetails,$purchaseOrder){
        $tax_revenue_tariff=0;
            if($purchaseOrder->tax_revenue!=0){
                $tax_revenue_tariffs = TaxMaster::where('tax_code',$purchaseOrder->tax_revenue)->first();
                $tax_revenue_tariff = $tax_revenue_tariffs->tariff;
            }
        $nominal = 0;
        $revenueTax = 0;
        $addTax = 0;
        $services = 0;
        $taxed = 0;
        $totalAllAfterDiscountBeforeTax = 0;
        $totalHPP = 0;
        $itemNameConcat = "";

        // Process purchase order details
            $rowNumber = 1;
            foreach ($piDetails as $detail) {
                $detail['price'] = str_replace(',', '', $detail['price']);
                $detail['disc_percent'] = str_replace(',', '', $detail['disc_percent']??0);
                $detail['disc_nominal'] = str_replace(',', '', $detail['disc_nominal']??0);
                $detail['nominal'] = str_replace(',', '', $detail['nominal']??0);
                $supplier = Supplier::where('supplier_code', $purchaseOrder->supplier_code)->first();
                $itemTax = Item::where('department_code','DP01')->where('department_code','DP01')->where('item_code', $detail['item_id'])->first();
                $taxs = TaxMaster::where('tax_code', $purchaseOrder->tax)->first();
                $gr = GoodReceipt::where('good_receipt_number',$detail['good_receipt_number'])->first();
                $vendorNumber = $gr?($gr->vendor_number?$gr->vendor_number:$gr->notes):'';
                $purchaseOrder->vendor_number = $vendorNumber;


                if($supplier->pkp == 1 && strtolower($itemTax->category->item_category_name) != 'service') {
                    if($supplier->include == 1) {
                        if ($itemTax->additional_tax == 1 ) {
                            $totalAllAfterDiscountBeforeTax += (($detail['qty']*$detail['price']*$detail['base_qty']) / (1 + $taxs->tariff / 100)) - ($detail['disc_percent']/100*(($detail['qty']*$detail['price']*$detail['base_qty']) / (1 + $taxs->tariff / 100)))-$detail['disc_nominal'];
                        } else {
                            $totalAllAfterDiscountBeforeTax += $detail['qty']*$detail['base_qty']*$detail['price'] - ($detail['disc_percent']/100*(($detail['qty']*$detail['base_qty']*$detail['price'])))-$detail['disc_nominal'];
                        }
                    } else {
                        $totalAllAfterDiscountBeforeTax += $detail['qty']*$detail['base_qty']*$detail['price'] - ($detail['disc_percent']/100*(($detail['qty']*$detail['base_qty']*$detail['price'])))-$detail['disc_nominal'];
                    }
                }else {
                    $totalAllAfterDiscountBeforeTax += $detail['qty']*$detail['base_qty']*$detail['price'] - ($detail['disc_percent']/100*(($detail['qty']*$detail['base_qty']*$detail['price'])))-$detail['disc_nominal'];
                }

            }
            $totalAllItemBeforeTax = 0;
            $totalAllDiscountDetail = 0;

            foreach ($piDetails as $detail) {
                $detail['price'] = str_replace(',', '', $detail['price']);
                $detail['disc_percent'] = str_replace(',', '', $detail['disc_percent']??0);
                $detail['disc_nominal'] = str_replace(',', '', $detail['disc_nominal']??0);
                $detail['nominal'] = str_replace(',', '', $detail['nominal']??0);
                $detail['purchase_invoice_number'] = $purchaseOrder->purchase_invoice_number;
                $detail['number_row'] = $rowNumber; // Correctly assign row number
                $detail['company_code'] = $purchaseOrder->company_code;
                $detail['department_code'] = 'DP01';
                $nominal += $detail['qty']*$detail['price']-($detail['disc_percent']/100*$detail['qty']*$detail['price'])-$detail['disc_nominal'];
                $detail['created_by'] = Auth::user()->username;
                $detail['updated_by'] = Auth::user()->username;
                $detail['unit']=$detail['unit'];

                // dd($nominal);
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
                // dd($detail);


                $supplier = Supplier::where('supplier_code', $purchaseOrder->supplier_code)->first();
                $itemTax = Item::where('department_code','DP01')->where('item_code', $detail['item_id'])->first();
                $taxs = TaxMaster::where('tax_code', $purchaseOrder->tax)->first();
                if ($itemTax->additional_tax == 1) {
                    $detail['add_tax_detail'] = $detail['nominal'] * $taxs->tax_base * $taxs->tariff/100;
                }else{
                    $detail['add_tax_detail'] = 0;
                }

                $totalPriceBeforeTaxBeforeDiscount = 0;
                $totalPriceBeforeTaxAfterDiscount = 0;
                $totalDiscountPerDetail = 0;
                $discPerDetail = 0;
                // dd($supplier);
                if ($supplier->pkp == 1) {
                    if (strtolower($itemTax->category->item_category_name) == 'service') {
                        $services += $detail['nominal'];
                    }

                    if($supplier->include == 1) {
                        if ($itemTax->additional_tax == 1) {
                            $totalPriceBeforeTaxBeforeDiscount = ($detail['qty']*$detail['price'])/(1 + $taxs->tariff / 100)*$detail['base_qty'];
                            $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                            $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $purchaseOrder->disc_nominal;
                            $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                            $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                            $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];
                            $taxed += $totalPriceBeforeTaxAfterDiscount;
                        }else{
                            $totalPriceBeforeTaxBeforeDiscount = $detail['qty']*$detail['price']*$detail['base_qty'];
                            $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                            $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $purchaseOrder->disc_nominal;
                            $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                            $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                            $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];
                        }
                    } else {
                        if ($itemTax->additional_tax == 1) {
                            $totalPriceBeforeTaxBeforeDiscount = $detail['qty']*$detail['price']*$detail['base_qty'];
                            $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                            $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $purchaseOrder->disc_nominal;
                            $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                            $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                            $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];
                            $taxed += $totalPriceBeforeTaxAfterDiscount;
                        }else{
                            $totalPriceBeforeTaxBeforeDiscount = $detail['qty']*$detail['price']*$detail['base_qty'];
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
                    $totalPriceBeforeTaxBeforeDiscount = $detail['qty']*$detail['price']*$detail['base_qty'];
                    $totalPriceBeforeTaxAfterDiscount = $totalPriceBeforeTaxBeforeDiscount  - (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']);
                    $discPerDetail = $totalPriceBeforeTaxAfterDiscount/$totalAllAfterDiscountBeforeTax * $purchaseOrder->disc_nominal;
                    $totalPriceBeforeTaxAfterDiscount -=$discPerDetail;
                    $totalDiscountPerDetail =  (($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal']) + $discPerDetail;
                    $totalAllDiscountDetail +=($detail['disc_percent']/100*$totalPriceBeforeTaxBeforeDiscount)+$detail['disc_nominal'];
                }
                $totalAllItemBeforeTax+=$totalPriceBeforeTaxAfterDiscount;
                $detail['disc_header'] = $discPerDetail;
                $priceUpdate = $detail['price'];
                // dd($priceUpdate);
                PurchaseInvoiceDetail::create($detail);
                unset($detail['item_name']);
                unset($detail['unit_name']);
                unset($detail['number_row']);
                unset($detail['status']);

                $itemUnit = ItemPurchase::where('department_code','DP01')->where('item_code', $detail['item_id'])->first();

                $suppliers = Supplier::where('supplier_code', $purchaseOrder->supplier_code)->first();
                $itemTax = Item::where('department_code','DP01')->where('item_code', $detail['item_id'])->first();
                $categories;
                if($itemTax){
                    $categories = ItemCategory::where('item_category_code', $itemTax->item_category)->first();
                }

                //PI Detail Value
                $PIJournalD = new Journal();
                $PIJournalD->document_number = $detail['purchase_invoice_number'];
                $PIJournalD->document_date = $purchaseOrder->document_date;
                $PIJournalD->account_number = $categories->acc_number_purchase??'1001';
                $PIJournalD->debet_nominal = $totalPriceBeforeTaxAfterDiscount+$totalDiscountPerDetail;
                $PIJournalD->credit_nominal = 0;
                $PIJournalD->notes = 'Purchase '.$itemTax->item_name.' : '.$detail['qty'].' '.$itemUnit->units->unit_name ;
                $PIJournalD->company_code = $purchaseOrder->company_code;
                $PIJournalD->department_code = $purchaseOrder->department_code;
                $PIJournalD->created_by = Auth::user()->username;
                $PIJournalD->updated_by = Auth::user()->username;
                $PIJournalD -> save();


                //PI Discount Total(Discount per detail + Discount Allocation from header)
                $PIJournalDd = new Journal();
                $PIJournalDd->document_number = $detail['purchase_invoice_number'];
                $PIJournalDd->document_date = $purchaseOrder->document_date;
                $PIJournalDd->account_number = $categories->acc_number_purchase_discount??'1001';
                $PIJournalDd->debet_nominal = 0;
                $PIJournalDd->credit_nominal = $totalDiscountPerDetail;
                $PIJournalDd->notes = 'Discount on purchase '.$itemTax->item_name.' : '.$detail['qty'].' '.$itemUnit->units->unit_name ;
                $PIJournalDd->company_code = $purchaseOrder->company_code;
                $PIJournalDd->department_code = $purchaseOrder->department_code;
                $PIJournalDd->created_by = Auth::user()->username;
                $PIJournalDd->updated_by = Auth::user()->username;
                $PIJournalDd -> save();


                $totalHPP+=$totalPriceBeforeTaxAfterDiscount;
                $itemNameConcat.=$itemTax->item_name." | ";

                // $firstQ = InventoryDetail::where([
                //     ['item_id', $detail['item_id']],
                //     ['unit',$detail['unit']],
                //     ['department_code',$purchaseOrder->department_code]
                //     ])->orderBy('id','desc')->first();

                // $firstQty = $firstQ->last_qty??0;

                // $cogs = $detail['nominal'];

                InventoryDetailPurchase::create([
                    'document_number'=>$purchaseOrder->purchase_invoice_number,
                    'document_date'=>$purchaseOrder->document_date,
                    'transaction_type'=>'Purchase',
                    'from_to'=>$purchaseOrder->supplier_code,
                    'item_id'=>$detail['item_id'],
                    'quantity'=>$detail['qty'],
                    'unit'=>$detail['unit'],
                    'base_quantity'=>$detail['base_qty'],
                    'unit_base'=>$detail['base_unit'],
                    'company_code'=>$purchaseOrder->company_code,
                    'department_code'=>$purchaseOrder->department_code,
                    'first_qty'=>0,
                    'last_qty'=>0,
                    'created_by' => Auth::user()->username,
                    'updated_by' => Auth::user()->username,
                    'total' => $detail['nominal'],
                    'cogs' => $detail['nominal'],
                    'qty_actual' => $detail['qty']
                ]);



                if($detail['good_receipt_number']){
                    GoodReceipt::where('good_receipt_number',$detail['good_receipt_number'])->update(['status'=>'Closed']);
                }


                $rowNumber++;
            }

        //PI HPP
        $PIJournalDd = new Journal();
        $PIJournalDd->document_number = $purchaseOrder->purchase_invoice_number;
        $PIJournalDd->document_date = $purchaseOrder->document_date;
        $PIJournalDd->account_number = $categories->acc_cogs??'1001';
        $PIJournalDd->debet_nominal = 0;
        $PIJournalDd->credit_nominal = $totalHPP;
        $PIJournalDd->notes = 'HPP on item '.$itemNameConcat ;
        $PIJournalDd->company_code = $purchaseOrder->company_code;
        $PIJournalDd->department_code = $purchaseOrder->department_code;
        $PIJournalDd->created_by = Auth::user()->username;
        $PIJournalDd->updated_by = Auth::user()->username;
        // $PIJournalDd -> save();


        //PI Persediaan
        $PIJournalDd = new Journal();
        $PIJournalDd->document_number = $purchaseOrder->purchase_invoice_number;
        $PIJournalDd->document_date = $purchaseOrder->document_date;
        $PIJournalDd->account_number = $categories->account_inventory??'1001';
        $PIJournalDd->debet_nominal = $totalHPP;
        $PIJournalDd->credit_nominal = 0;
        $PIJournalDd->notes = 'Persediaan '.$itemNameConcat ;
        $PIJournalDd->company_code = $purchaseOrder->company_code;
        $PIJournalDd->department_code = $purchaseOrder->department_code;
        $PIJournalDd->created_by = Auth::user()->username;
        $PIJournalDd->updated_by = Auth::user()->username;
        // $PIJournalDd -> save();


        $supplier = Supplier::where('supplier_code', $purchaseOrder->supplier_code)->first();
        if ($supplier->pkp == 1) {
            $addTax = $taxed * $taxs->tax_base* $taxs->tariff/100;
            $revenueTax = $services * $tax_revenue_tariff/100; //nanti diubah dengan pilihan dari header
        } else {
            $revenueTax = 0;
            $addTax = 0;
        }
        $purchaseOrder->add_tax = $addTax;
        $purchaseOrder->tax_revenue = $revenueTax;
        $purchaseOrder->subtotal =$totalAllItemBeforeTax;
        $purchaseOrder->total = $totalAllItemBeforeTax + $purchaseOrder->add_tax + $purchaseOrder->tax_revenue;

        $purchaseOrder->save();
        // dd($purchaseOrder);

        $suppliers = Supplier::where('supplier_code', $purchaseOrder->supplier_code)->first();
        $taxes = TaxMaster::where('tax_code', $purchaseOrder->tax_revenue_tariff)->first();

        //PI Header Total Purchase
        $PIJournal = new Journal();
        $PIJournal->document_number = $purchaseOrder->purchase_invoice_number;
        $PIJournal->document_date = $purchaseOrder->document_date;
        $PIJournal->account_number = $suppliers->account_payable??'1001';
        $PIJournal->debet_nominal = 0;
        $PIJournal->credit_nominal = $purchaseOrder->total;
        $PIJournal->notes = 'Purchase from '.$suppliers->supplier_name.' ('.$purchaseOrder->purchase_invoice_number.')';
        $PIJournal->company_code = $purchaseOrder->company_code;
        $PIJournal->department_code = $purchaseOrder->department_code;
        $PIJournal->created_by = Auth::user()->username;
        $PIJournal->updated_by = Auth::user()->username;
        $PIJournal -> save();


        //PI Header Discount
        if($purchaseOrder->disc_nominal>0){
            $itemTax = Item::where('department_code','DP01')->where('item_code', $detail['item_id'])->first();
            $categories;
            if($itemTax){
                $categories = ItemCategory::where('item_category_code', $itemTax->item_category)->first();
            }
            //
            $PIJournald = new Journal();
            $PIJournald->document_number = $purchaseOrder->purchase_invoice_number;
            $PIJournald->document_date = $purchaseOrder->document_date;
            $PIJournald->account_number = $categories->acc_number_purchase_discount??'1001';
            $PIJournald->debet_nominal = 0;
            $PIJournald->credit_nominal = $purchaseOrder->disc_nominal;
            $PIJournald->notes = 'Discount on purchase from '.$suppliers->supplier_name.' ('.$purchaseOrder->purchase_invoice_number.')';
            $PIJournald->company_code = $purchaseOrder->company_code;
            $PIJournald->department_code = $purchaseOrder->department_code;
            $PIJournald->created_by = Auth::user()->username;
            $PIJournald->updated_by = Auth::user()->username;
            // $PIJournald -> save();
        }

        //PI Header Add Tax
        $PIJournala = new Journal();
        $PIJournala->document_number = $purchaseOrder->purchase_invoice_number;
        $PIJournala->document_date = $purchaseOrder->document_date;
        $PIJournala->account_number = $suppliers->account_add_tax??'1001';
        $PIJournala->debet_nominal = $purchaseOrder->add_tax;
        $PIJournala->credit_nominal = 0;
        $PIJournala->notes = 'Add. Tax on purchase from '.$suppliers->supplier_name.' ('.$purchaseOrder->purchase_invoice_number.')';
        $PIJournala->company_code = $purchaseOrder->company_code;
        $PIJournala->department_code = $purchaseOrder->department_code;
        $PIJournala->created_by = Auth::user()->username;
        $PIJournala->updated_by = Auth::user()->username;
        $PIJournala -> save();


        //PI Header Total Discount
        $PIJournala = new Journal();
        $PIJournala->document_number = $purchaseOrder->purchase_invoice_number;
        $PIJournala->document_date = $purchaseOrder->document_date;
        $PIJournala->account_number = $suppliers->account_add_tax??'1001';
        $PIJournala->debet_nominal = 0;
        $PIJournala->credit_nominal = $totalAllDiscountDetail;
        $PIJournala->notes = 'Discount Items on purchase from '.$suppliers->supplier_name.' ('.$purchaseOrder->purchase_invoice_number.')';
        $PIJournala->company_code = $purchaseOrder->company_code;
        $PIJournala->department_code = $purchaseOrder->department_code;
        $PIJournala->created_by = Auth::user()->username;
        $PIJournala->updated_by = Auth::user()->username;
        // $PIJournala -> save();

        //PI Header Revenue Tax
        if($taxes){
            $PIJournala = new Journal();
            $PIJournala->document_number = $purchaseOrder->purchase_invoice_number;
            $PIJournala->document_date = $purchaseOrder->document_date;
            $PIJournala->account_number = $taxes->account_number??'1001';
            $PIJournala->debet_nominal = $purchaseOrder->tax_revenue;
            $PIJournala->credit_nominal = 0;
            $PIJournala->notes = 'Revenue Tax on purchase from '.$suppliers->supplier_name.' ('.$purchaseOrder->purchase_invoice_number.')';
            $PIJournala->company_code = $purchaseOrder->company_code;
            $PIJournala->department_code = $purchaseOrder->department_code;
            $PIJournala->created_by = Auth::user()->username;
            $PIJournala->updated_by = Auth::user()->username;
            $PIJournala -> save();
        }

        Debt::create([
            'document_number'=>$purchaseOrder->purchase_invoice_number,
            'document_date'=>$purchaseOrder->document_date,
            'due_date'=>$purchaseOrder->due_date,
            'total_debt'=>$purchaseOrder->total,
            'debt_balance'=>$purchaseOrder->total,
            'supplier_code'=>$purchaseOrder->supplier_code,
            'due_date'=>$purchaseOrder->due_date,
            'company_code'=>$purchaseOrder->company_code,
            'department_code'=>$purchaseOrder->department_code,
            'created_by' => Auth::user()->username,
            'updated_by' => Auth::user()->username,
        ]);

    }

    public function recalcJournal()
    {
        DB::beginTransaction();
        try {
            $purchaseInvoices = PurchaseInvoice::all();
            foreach($purchaseInvoices as $purchaseOrder){
                Journal::where('document_number',
                $purchaseOrder->purchase_invoice_number)->delete();
                    $details = PurchaseInvoiceDetail::where('purchase_invoice_number',$purchaseOrder->purchase_invoice_number)->get();
                    $categories = ItemCategory::first();
                    $suppliers = Supplier::where('supplier_code',$purchaseOrder->supplier_code)->first();
                    $totalHPP=0;
                    $itemNameConcate="";
                    $discHeader = $purchaseOrder->disc_nominal;
                    $subtotal = $purchaseOrder->subtotal;
                    foreach($details as $detail){
                        $itemTax = Item::where('department_code',$purchaseOrder->department_code)->where('item_code', $detail->item_id)->first();
                        $totalHPP+=$detail->nominal;
                        $itemNameConcate.=$itemTax->item_name.'|';

                        $discPerDetail = $detail->disc_header;
                        //PI Detail Value
                        $PIJournalD = new Journal();
                        $PIJournalD->document_number = $purchaseOrder->purchase_invoice_number;
                        $PIJournalD->document_date = $purchaseOrder->document_date;
                        $PIJournalD->account_number = $categories->acc_number_purchase??'1001';
                        $PIJournalD->debet_nominal = $detail->nominal+(($detail->disc_percent/100*$detail->qty*$detail->price*$detail->base_qty)+$detail->disc_nominal);
                        $PIJournalD->credit_nominal = 0;
                        $PIJournalD->notes = 'Purchase '.$itemTax->item_name.' : '.$detail['qty'].' '.$detail->unit;
                        $PIJournalD->company_code = $purchaseOrder->company_code;
                        $PIJournalD->department_code = $purchaseOrder->department_code;
                        $PIJournalD->created_by = Auth::user()->username;
                        $PIJournalD->updated_by = Auth::user()->username;
                        $PIJournalD->save();

                        //PI Discount Total(Discount per detail + Discount Allocation from header)
                        $PIJournalDde = new Journal();
                        $PIJournalDde->document_number = $detail->purchase_invoice_number;
                        $PIJournalDde->document_date = $purchaseOrder->document_date;
                        $PIJournalDde->account_number = $categories->acc_number_purchase_discount??'1001';
                        $PIJournalDde->debet_nominal = 0;
                        $PIJournalDde->credit_nominal = (($detail->disc_percent/100*$detail->qty*$detail->price*$detail->base_qty)+$detail->disc_nominal)+$discPerDetail;
                        $PIJournalDde->notes = 'Discount on purchase '.$itemTax->item_name.' : '.$detail->qty.' '.$detail->unit ;
                        $PIJournalDde->company_code = $purchaseOrder->company_code;
                        $PIJournalDde->department_code = $purchaseOrder->department_code;
                        $PIJournalDde->created_by = Auth::user()->username;
                        $PIJournalDde->updated_by = Auth::user()->username;
                        if((($detail->disc_percent/100*$detail->qty*$detail->price*$detail->base_qty)+$detail->disc_nominal)+$discPerDetail>0){
                            $PIJournalDde -> save();
                        }
                    }

                    //PI HPP
                    $PIJournalDd = new Journal();
                    $PIJournalDd->document_number = $purchaseOrder->purchase_invoice_number;
                    $PIJournalDd->document_date = $purchaseOrder->document_date;
                    $PIJournalDd->account_number = $categories->acc_cogs??'1001';
                    $PIJournalDd->debet_nominal = 0;
                    $PIJournalDd->credit_nominal = $totalHPP;
                    $PIJournalDd->notes = 'HPP on item '.$itemNameConcate ;
                    $PIJournalDd->company_code = $purchaseOrder->company_code;
                    $PIJournalDd->department_code = $purchaseOrder->department_code;
                    $PIJournalDd->created_by = Auth::user()->username;
                    $PIJournalDd->updated_by = Auth::user()->username;
                    $PIJournalDd -> save();

                    //PI Persediaan
                    $PIJournalDd = new Journal();
                    $PIJournalDd->document_number = $purchaseOrder->purchase_invoice_number;
                    $PIJournalDd->document_date = $purchaseOrder->document_date;
                    $PIJournalDd->account_number = $categories->account_inventory??'1001';
                    $PIJournalDd->debet_nominal = $totalHPP;
                    $PIJournalDd->credit_nominal = 0;
                    $PIJournalDd->notes = 'Persediaan '.$itemNameConcate ;
                    $PIJournalDd->company_code = $purchaseOrder->company_code;
                    $PIJournalDd->department_code = $purchaseOrder->department_code;
                    $PIJournalDd->created_by = Auth::user()->username;
                    $PIJournalDd->updated_by = Auth::user()->username;
                    // $PIJournalDd -> save();

                    //PI Header Total Purchase
                    $PIJournal = new Journal();
                    $PIJournal->document_number = $purchaseOrder->purchase_invoice_number;
                    $PIJournal->document_date = $purchaseOrder->document_date;
                    $PIJournal->account_number = $suppliers->account_payable??'1001';
                    $PIJournal->debet_nominal = 0;
                    $PIJournal->credit_nominal = $purchaseOrder->total;
                    $PIJournal->notes = 'Purchase from '.$suppliers->supplier_name.' ('.$purchaseOrder->purchase_invoice_number.')';
                    $PIJournal->company_code = $purchaseOrder->company_code;
                    $PIJournal->department_code = $purchaseOrder->department_code;
                    $PIJournal->created_by = Auth::user()->username;
                    $PIJournal->updated_by = Auth::user()->username;
                    // $PIJournal -> save();

                    //PI Header Add Tax
                    $PIJournala = new Journal();
                    $PIJournala->document_number = $purchaseOrder->purchase_invoice_number;
                    $PIJournala->document_date = $purchaseOrder->document_date;
                    $PIJournala->account_number = $suppliers->account_add_tax??'1001';
                    $PIJournala->debet_nominal = $purchaseOrder->add_tax;
                    $PIJournala->credit_nominal = 0;
                    $PIJournala->notes = 'Add. Tax on purchase from '.$suppliers->supplier_name.' ('.$purchaseOrder->purchase_invoice_number.')';
                    $PIJournala->company_code = $purchaseOrder->company_code;
                    $PIJournala->department_code = $purchaseOrder->department_code;
                    $PIJournala->created_by = Auth::user()->username;
                    $PIJournala->updated_by = Auth::user()->username;
                    if($purchaseOrder->department_code=='DP01'){
                        $PIJournala -> save();
                    }
            }
            // dd('a');
            DB::commit();
                return redirect()->route('transaction.purchase_invoice')->with('success', 'Purchase Invoice recalculate successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to create Purchase Invoice: ' . $e->getMessage())->withInput();
        }
    }

    public function setVendorNumber()
    {
        DB::beginTransaction();
        try {
            $pis = PurchaseInvoice::all();
            foreach ($pis as $value) {
                $pid = PurchaseInvoiceDetail::where('purchase_invoice_number',$value->purchase_invoice_number)->first();
                $gr = GoodReceipt::where('good_receipt_number',$pid->good_receipt_number)->first();
                PurchaseInvoice::where('purchase_invoice_number',$value->purchase_invoice_number)->update(['vendor_number'=>$gr?$gr->vendor_number:'']);
            }
            DB::commit();
            return redirect()->route('transaction.purchase_invoice')->with('success', 'Purchase Invoice recalculate successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to create Purchase Invoice: ' . $e->getMessage())->withInput();
        }
    }
}
