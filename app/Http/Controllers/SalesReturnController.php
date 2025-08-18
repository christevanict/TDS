<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Coa;
use App\Models\Customer;
use App\Models\CategoryCustomer;
use App\Models\Receivable;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemUnit;
use App\Models\ItemDetail;
use App\Models\ItemSalesPricePrice;
use App\Models\ItemSalesPrice;
use App\Models\Department;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceDetail;
use App\Models\GoodReceipt;
use App\Models\GoodReceiptDetail;
use App\Models\InventoryDetail;
use App\Models\Journal;
use App\Models\ReceivablePayment;
use App\Models\SalesOrder;
use App\Models\SalesReturn;
use App\Models\SalesReturnDetail;
use App\Models\TaxMaster;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use TCPDF;

class SalesReturnController extends Controller
{
    public function index() {
        $companies = Company::all();
        $departments = Department::where('department_code', 'DP01')->first();
        $salesReturns = SalesReturn::where('department_code', 'DP01')->orderBy('id','desc')->get();
        $coas = COA::all();
        $customers = Customer::whereNot(function ($query) {
            $query->where('customer_code', 'like', 'DP02%')
                ->orWhere('customer_code', 'like', 'DP03%');
        })->get();
        $customers = Customer::whereNot(function ($query) {
            $query->where('customer_code', 'like', 'DP02%')
                ->orWhere('customer_code', 'like', 'DP03%');
        })->get();
        $prices = ItemSalesPrice::where('department_code','DP01')->get();
        $taxs = TaxMaster::all();
        $privileges = Auth::user()->roles->privileges['sales_return'];

        return view('transaction.sales-return.sales_return_list', compact('companies', 'departments', 'salesReturns', 'coas', 'customers', 'customers', 'prices', 'taxs','privileges'));
    }


    public function summary(Request $request)
    {
        // Initialize query for fetching sales invoices
        $query = SalesReturn::query();

        // Apply date filtering if 'from_date' and 'to_date' are present in the request
        if ($request->filled('from_date') && $request->filled('to_date')) {
            // Convert dates to Carbon instances for proper formatting and range filtering
            $fromDate = Carbon::parse($request->input('from_date'))->startOfDay();
            $toDate = Carbon::parse($request->input('to_date'))->endOfDay();

            // Apply the date range filter on the 'document_date' column
            $query->whereBetween('document_date', [$fromDate, $toDate]);
        }

        // Retrieve filtered sales invoices
        $salesInvoices = $query->where('department_code','DP01')->orderBy('document_date','asc')->orderBy('id','asc')->get();

        // Calculate the total amount from all filtered sales invoices
        $totalAmount = $salesInvoices->sum('total');
        $privileges = Auth::user()->roles->privileges['sales_return'];

        // Return the view with the sales invoices data and total amount
        return view('transaction.sales-return.sales_return_summary', compact('salesInvoices', 'totalAmount','privileges'))
        ->with([
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date')
        ]);
    }

    public function printSalesReturnPDF($sales_return_number)
    {
        // Retrieve the sales return with related data
        $salesReturn = SalesReturn::with([
            'company',
            'department',
            'customers',
            'details.items.itemDetails.unitConversion', // Load items, itemDetails, and unitConversion
        ])->findOrFail($sales_return_number);

        // Group the sales return details by item_id (if needed)
        $groupedDetails = $salesReturn->details->groupBy('item_id');
        $totalDiscount= 0;
        foreach ($salesReturn->details as $key => $value) {
            $subtotal = $value->qty*$value->base_qty*$value->price;
            $totalDiscount+= ($subtotal *$value->disc_percent/100) +$value->disc_nominal;
        }

        $totalHuruf = ucfirst($this->numberToWords($salesReturn->total)).' rupiah';
        // Generate and return the PDF
        return view('transaction.sales-return.sales_return_pdf', compact('salesReturn', 'groupedDetails','totalHuruf','totalDiscount'));

        // Create a safe file name by removing any unwanted characters
        $nameFile = Str::replace("/", "", $salesReturn->sales_return_number); // Ensure you have a valid 'sales_return_number' property

        // Stream the PDF to the browser
        return $pdf->stream("Sales_Return_{$nameFile}.pdf");
    }
    public function printTc($sales_return_number)
    {
        // Retrieve the sales return with related data
        $salesReturn = SalesReturn::with([
            'company',
            'department',
            'customers',
            'details.items.itemDetails.unitConversion', // Load items, itemDetails, and unitConversion
        ])->findOrFail($sales_return_number);
        $totalDiscount= 0;
        foreach ($salesReturn->details as $key => $value) {
            $subtotal = $value->qty*$value->base_qty*$value->price;
            $totalDiscount+= ($subtotal *$value->disc_percent/100) +$value->disc_nominal;
        }

        // Group the sales return details by item_id (if needed)
        $groupedDetails = $salesReturn->details->groupBy('item_id');

        $totalHuruf = ucfirst($this->numberToWords($salesReturn->total)).' rupiah';
        // Initialize TCPDF
        $pdf = new TCPDF('L', 'mm', [145, 210], true, 'UTF-8', false); // Landscape, continuous form

        // Set document information
        $pdf->SetCreator('Your App');
        $pdf->SetAuthor('Your Name');
        $pdf->SetTitle(__('Sales Invoice') . ' - ' . $salesReturn->sales_return_number);
        $pdf->SetSubject('Sales Invoice');
        $pdf->SetKeywords('invoice, sales, pdf');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(5, 5, 5);
        $pdf->SetAutoPageBreak(false);

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('dejavusansmono', '', 9.5);

        // Build the invoice content as HTML tables
        $content = '<style>
            table { font-family: dejavusansmono; font-size: 9.5pt; width: 100%; border-collapse: collapse; }
            td { padding: 0; } /* Changed from 1px to 0 */
            .right { text-align: right; }
            .left { text-align: left; }
            .divider { border-bottom: 1px solid black; padding: 0; margin: 0; height: 0; line-height: 0; } /* Enhanced control */
        </style>';

        // Header Table
        $content .= '<table>';
        $content .= '<tr><td style="height: 5mm;"></td></tr>';
        $content .= '<tr><td style="width: 47%; font-size:10.5px; font-weight:bold;">TDS, CV</td>
        <td style="width: 53%;font-size:14px;font-weight:bold;text-align:left;">NOTA RETUR</td></tr>';
        $content .= '<tr><td style="width: 40%;font-size:10.5px;">Kepada Yth.</td><td style="width: 60%;"></td></tr>';
        $content .= '<tr><td style="width: 40%;font-size:10.5px;">' . htmlspecialchars($salesReturn->customers->customer_name ?? 'N/A') . '</td>';
        $content .= '<td style="width: 60%;font-size:10.5px; text-align:right;">No. Invoice : ' . $salesReturn->sales_return_number . '</td></tr>';
        $content .= '<tr><td style="width: 40%;font-size:10.5px;">' . htmlspecialchars($salesReturn->customers->city ?? 'N/A') . '</td>';
        $content .= '<td style="width: 60%;font-size:10.5px;text-align:center;">Tanggal : ' . Carbon::parse($salesReturn->document_date)->format('d M Y') . '</td></tr>';
        $content .= '</table>';
        $content .= '<br>';

        // Divider
        $content .= '<table><tr><td class="divider"></td></tr></table>';

       // Items Table Header
        $content .= '<table>';
        $content .= '<tr>';
        $content .= '<td style="width: 5%; border-right: 1px solid black;border-left: 1px solid black;">NO.</td>';
        $content .= '<td style="width: 50%; border-right: 1px solid black;">NAMA BARANG</td>';
        $content .= '<td style="width: 6%; border-right: 1px solid black;text-align:right;">COLY</td>';
        $content .= '<td style="width: 12%; border-right: 1px solid black;text-align:center;">QTY</td>';
        $content .= '<td style="width: 12%; border-right: 1px solid black;text-align:right;">HARGA</td>';
        $content .= '<td style="width: 15%;border-right: 1px solid black; text-align:right;">JUMLAH</td>'; // No border on last column
        $content .= '</tr>';
        $content .= '<tr><td colspan="6" class="divider" style="padding: 0; height: 0; line-height: 0;"></td></tr>';

        // Items Table Body
        foreach ($salesReturn->details as $index => $detail) {
            $maxLength = 100;
            $itemName = $detail->items->item_name;
            $lines = explode("\n", wordwrap($itemName, $maxLength, "\n", false));

            foreach ($lines as $lineIndex => $line) {
                $content .= '<tr>';
                if ($lineIndex == 0) {
                    $content .= '<td style="width: 5%; text-align: left; border-right: 1px solid black;border-left: 1px solid black;text-align:right;">' . ($index + 1) . '</td>';
                    $content .= '<td style="width: 50%; text-align: left; border-right: 1px solid black;">' . htmlspecialchars($line) . '</td>';
                    $content .= '<td style="width: 6%; text-align: right; border-right: 1px solid black;">' . number_format($detail->qty, 0) . '</td>';
                    $content .= '<td style="width: 12%; text-align: right; border-right: 1px solid black;">' . number_format($detail->base_qty * $detail->qty, 2, ',', '.') . ' ' . $detail->baseUnit->unit_name . '</td>';
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
            $content .= '<tr><td colspan="6" class="divider" style="padding: 0; height: 0; line-height: 0;"></td></tr>';
        }

        // Divider
        $content .= '<br>';

        // Footer Table (Terbilang and Totals)
        $maxLength = 50;
        $terbilang = "Terbilang: " . $totalHuruf;
        $terbilangLines = explode("\n", wordwrap($terbilang, $maxLength, "\n", false));

        // Divider before footer
        $content .= '<tr><td colspan="6"  style="padding: 0; height: 0; line-height: 0;"></td></tr>';

        // Row 1: First Terbilang line + Sub Total
        $content .= '<tr>';
        $content .= '<td colspan="3" style="width: 61%; vertical-align: top; font-weight: bold;font-size:9px;">' . htmlspecialchars($terbilangLines[0]) . '</td>';
        $content .= '<td colspan="2" style="width: 24%; text-align: right; border-bottom: 1px solid black;border-left: 1px solid black;border-right: 1px solid black;border-top: 1px solid black;">Sub Total</td>';
        $content .= '<td style="width: 15%; text-align: right;border-bottom: 1px solid black;border-right: 1px solid black;border-top: 1px solid black;">' . number_format($salesReturn->subtotal + $totalDiscount, 0, ',', '.') . '</td>';
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
        $content .= '<td style="width: 15%; text-align: right;border-bottom: 1px solid black;border-right: 1px solid black;">' . number_format($salesReturn->add_tax, 0, ',', '.') . '</td>';
        $content .= '</tr>';

        // Row 4: BCA KCP + Total Invoice
        $content .= '<tr>';
        $content .= '<td colspan="2" style="width: 55%; vertical-align: top; font-weight: bold;border: 1px solid black;font-size:9px;">BCA KCP MARGOMULYO 4700 36 8080<br>A/N: TDS</td>';
        $content .= '<td style="width:6%"></td>';
        $content .= '<td colspan="2" style="width: 24%; text-align: right; border-bottom: 1px solid black;border-left: 1px solid black;border-right: 1px solid black;">Total Invoice</td>';
        $content .= '<td style="width: 15%; text-align: right;border-bottom: 1px solid black;border-right: 1px solid black;">' . number_format($salesReturn->total, 0, ',', '.') . '</td>';
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

        // Write the content to the PDF
        $pdf->writeHTML($content, true, false, true, false, '');

        // Output the PDF
        $pdf->Output('sales_invoice_' . $salesReturn->sales_return_number . '.pdf', 'I');
    }

    public function recalcJournal()
    {
        DB::beginTransaction();
        try {
            $salesReturns = SalesReturn::all();

            foreach ($salesReturns as $salesReturn) {
                Journal::where('document_number',$salesReturn->sales_return_number)->delete();
                $details = SalesReturnDetail::where('sales_return_number',$salesReturn->sales_return_number)->get();
                $categories = ItemCategory::first();
                $totalHPP=0;
                $itemNameConcate="";
                foreach ($details as $detail) {
                    $itemTax = Item::where('department_code',$salesReturn->department_code)->where('item_code', $detail->item_id)->first();
                    $totalHPP+=$detail->nominal;
                    $itemNameConcate.=$itemTax->item_name.'|';

                    //SR Detail
                    $PODJournal1 = new Journal();
                    $PODJournal1->document_number = $detail->sales_return_number;
                    $PODJournal1->document_date = $salesReturn->document_date;
                    $PODJournal1->account_number = $categories->acc_number_sales_return??'1001';
                    $PODJournal1->debet_nominal = $detail->nominal;
                    $PODJournal1->credit_nominal = 0;
                    $PODJournal1->notes ='Return '. $itemTax->item_name.' ('. $detail->unit.') : '.$detail['qty'];
                    $PODJournal1->company_code = $salesReturn->company_code;
                    $PODJournal1->department_code = $salesReturn->department_code;
                    $PODJournal1->created_by = Auth::user()->username;
                    $PODJournal1->updated_by = Auth::user()->username;
                    $PODJournal1 -> save();
                }
                //SR HPP
                $PODJournal1 = new Journal();
                $PODJournal1->document_number = $salesReturn->sales_return_number;
                $PODJournal1->document_date = $salesReturn->document_date;
                $PODJournal1->account_number = $categories->acc_cogs??'1001';
                $PODJournal1->debet_nominal = 0;
                $PODJournal1->credit_nominal = $totalHPP;
                $PODJournal1->notes ='HPP '. $itemNameConcate;
                $PODJournal1->company_code = $salesReturn->company_code;
                $PODJournal1->department_code = $salesReturn->department_code;
                $PODJournal1->created_by = Auth::user()->username;
                $PODJournal1->updated_by = Auth::user()->username;
                $PODJournal1 -> save();

                //SR Persediaan
                $PODJournal1 = new Journal();
                $PODJournal1->document_number = $salesReturn->sales_return_number;
                $PODJournal1->document_date = $salesReturn->document_date;
                $PODJournal1->account_number = $categories->account_inventory??'1001';
                $PODJournal1->debet_nominal = $totalHPP;
                $PODJournal1->credit_nominal = 0;
                $PODJournal1->notes ='Persediaan '. $itemNameConcate;
                $PODJournal1->company_code = $salesReturn->company_code;
                $PODJournal1->department_code = $salesReturn->department_code;
                $PODJournal1->created_by = Auth::user()->username;
                $PODJournal1->updated_by = Auth::user()->username;
                $PODJournal1 -> save();

                //PI Header Total
                $POJournal = new Journal();
                $customers = Customer::where('customer_code', $salesReturn->customer_code)->first();
                $POJournal->document_number = $salesReturn->sales_return_number;
                $POJournal->document_date = $salesReturn->document_date;
                $POJournal->account_number = $customers->account_receivable;
                $POJournal->debet_nominal = 0;
                $POJournal->credit_nominal = $salesReturn->total;
                $POJournal->notes = 'Return Sales from '.$customers->customer_name.' ('.$salesReturn->sales_return_number.')';
                $POJournal->company_code = $salesReturn->company_code;
                $POJournal->department_code = $salesReturn->department_code;
                $POJournal->created_by = Auth::user()->username;
                $POJournal->updated_by = Auth::user()->username;
                $POJournal -> save();

                $POJournal2 = new Journal();
                $customers = Customer::where('customer_code', $salesReturn->customer_code)->first();
                $POJournal2->document_number = $salesReturn->sales_return_number;
                $POJournal2->document_date = $salesReturn->document_date;
                $POJournal2->account_number = $customers->account_add_tax;
                $POJournal2->debet_nominal = $salesReturn->add_tax;
                $POJournal2->credit_nominal = 0;
                $POJournal2->notes = 'Return Add. tax on sales from '.$customers->customer_name.' ('.$salesReturn->sales_return_number.')';
                $POJournal2->company_code = $salesReturn->company_code;
                $POJournal2->department_code = $salesReturn->department_code;
                $POJournal2->created_by = Auth::user()->username;
                $POJournal2->updated_by = Auth::user()->username;

                $POJournal2 -> save();
            }
            DB::commit();
                return redirect()->route('transaction.sales_return')->with('success', 'Sales Return created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to create Purchase Invoice: ' . $e->getMessage())->withInput();
        }
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
            $items = ItemSalesPrice::where('department_code','DP01')->whereHas('items')->orderBy('item_code')->distinct('item_code')->with('items','unitn','itemDetails')->get();
            // Additional data retrieval for the view
            $itemUnits = ItemUnit::orderBy('unit', 'asc')->get();
            $itemDetails = ItemDetail::where('department_code','DP01')->where('status',true)->orderBy('item_code', 'asc')->get();
            $prices = ItemSalesPrice::where('department_code','DP01')->get();
            $taxs = TaxMaster::all();

            $salesReturn = SalesReturn::with('details')->findOrFail($id);
            // Format dates for display

            $salesReturn->document_date = Carbon::parse($salesReturn->document_date)->format('Y-m-d');
            $privileges = Auth::user()->roles->privileges['sales_return'];

            return view('transaction.sales-return.sales_return_edit', compact('salesReturn', 'companies',  'departments', 'coas', 'items', 'itemUnits', 'itemDetails', 'prices', 'taxs','privileges'));
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
            // Retrieve the SalesInvoice record by sales_return_number

            $salesReturn = SalesReturn::where('id', $id)->firstOrFail();
            $salesReturn->notes = $request->notes;
            $oldTotal = $salesReturn->total;
            $oldPrDetails = SalesReturnDetail::where('sales_return_number',$salesReturn->sales_return_number)->get();
            $salesReturn->due_date = $request->due_date;

            Journal::where('document_number',$salesReturn->sales_return_number)->delete();
            InventoryDetail::where('document_number',$salesReturn->sales_return_number)->delete();


            $this->saveSalesReturnDetail($request->details,$salesReturn);

            // Parse and assign date fields
            DB::commit(); // Commit the transaction
            return redirect()->route('transaction.sales_return')->with('success', 'Sales Return edited successfully!');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            // dd($e);
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to update Sales Invoice: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, $id)
    {
        DB::beginTransaction(); // Start the transaction
        try {
            // Retrieve the SalesInvoice record by sales_return_number

            $general = SalesInvoice::where('id', $id)->firstOrFail();
            $general->status = 'Ready';
            $general->save();
            // Parse and assign date fields
            DB::commit(); // Commit the transaction
            return redirect()->route('transaction.sales_return')->with('success', 'APPROVED');;
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to update Sales Invoice: ' . $e->getMessage());
        }
    }

    public function destroy($id) {
        DB::beginTransaction();
        try {
            $general = SalesReturn::findOrFail($id);
            Receivable::where('document_number',$general->sales_return_number)->delete();
            Journal::where('document_number',$general->sales_return_number)->delete();
            InventoryDetail::where('document_number',$general->sales_return_number)->delete();
            SalesReturnDetail::where('sales_return_number', $general->sales_return_number)->delete();

            $general->delete();

            DB::commit(); // Commit transaction
            return redirect()->route('transaction.sales_return')->with('success', 'Sales Invoice deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->route('transaction.sales_return')->with('error', 'Error deleting: ' . $e->getMessage());
        }
    }

    private function generateSalesReturnNumber() {
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
        // Fetch the last sales order created in the current month
        $lastSalesOrder = SalesReturn::whereYear('created_at', $today->year)
            ->whereMonth('created_at', $month)
            ->where('department_code','DP01')
            ->orderBy('sales_return_number', 'desc')
            ->first();

        // Determine the new sales order number
        if ($lastSalesOrder) {
            // Extract the last number from the last sales order number
            $lastNumber = (int)substr($lastSalesOrder->sales_return_number, strrpos($lastSalesOrder->sales_return_number, '-') + 1);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            // Reset counter to 00001 if no sales orders found for the current month
            $newNumber = '00001';
        }

        // Return the new sales order number in the desired format
        return "TDS/RSI/{$romanMonth}/{$year}-{$newNumber}";
    }

    public function create(Request $request)
    {

        $customers = Customer::orderBy('customer_code', 'asc')->get();
        $departments = Department::where('department_code', 'DP01')->first();

        $items = ItemSalesPrice::where('department_code','DP01')->whereHas('items')->orderBy('item_code')->distinct('item_code')->with('items','unitn','itemDetails')->get();

        // Additional data retrieval for the view
        $itemUnits = ItemUnit::orderBy('unit', 'asc')->get();
        $itemDetails = ItemDetail::where('department_code','DP01')->where('status',true)->orderBy('item_code', 'asc')->get();
        $prices = ItemSalesPrice::where('department_code','DP01')->orderBy(
            'item_code',
            'asc'
        )->get();
        $company = Company::first();
        $token = str()->random(16);

        $salesInvoices = SalesInvoice::with('department','details')->orderBy('id', 'desc')->where('department_code', 'DP01')->get();
        $salesInvoicesD = SalesInvoiceDetail::orderBy('id', 'asc')->with(['items','units'])->get();
        $privileges = Auth::user()->roles->privileges['sales_return'];

        $taxs = TaxMaster::orderBy('tariff', 'asc')->get();

        return view('transaction.sales-return.sales_return_input', compact(
            // 'salesOrders',
            'customers',
            'departments',
            'items',
            'itemDetails',
            'salesInvoices',
            'salesInvoicesD',
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
        $exist = SalesReturn::where('token',$request->token)->where('department_code','DP01')->whereDate('created_at',Carbon::today())->first();
        if($exist){
            $id = SalesReturn::where('created_by',Auth::user()->username)->orderBy('id','desc')->select('id')->first()->id;
            return redirect()->route('transaction.sales_return.create')->with('success', 'Sales Return created successfully.')->with('id',$id);
        }
        // Generate a new sales order number
        $salesReturnNumber = $this->generateSalesReturnNumber();
        // dd($request->all());
        DB::beginTransaction();
        try {
            // Generate a new SalesOrder instance
            $salesReturn = new SalesReturn();
            $salesReturn->sales_return_number = $salesReturnNumber;
            // $salesReturn->good_receipt_number = $request->good_receipt_number;
            $salesReturn->customer_code = $request->customer_code;
            $salesReturn->document_date = $request->document_date;
            $salesReturn->token = $request->token;
            $salesReturn->due_date = $request->due_date;
            // Assign default values for discount
            $salesReturn->disc_percent = $request->disc_percent ?? 0;
            $salesReturn->disc_nominal = $request->disc_nominal ?? 0;
            $salesReturn->notes = $request->notes ?? '';
            // Set created_by and updated_by to the logged-in user
            $userId = Auth::id();
            $salesReturn->created_by = $userId;
            $salesReturn->updated_by = $userId;
            // Set tax from the selected dropdown
            $salesReturn->tax = 'PPN';
            // Finalize total
            $salesReturn->company_code = $request->company_code ?? null;
            $salesReturn->department_code = 'DP01' ?? null;
            $salesReturn->sales_invoice_number = $request->sales_invoice_number;
            $salesReturn->include = $request->include ?? false;
            // $salesReturn->save();
            $nominal = 0;
            $addTax = 0;
            $taxed = 0;
            $totalAllItemBeforeTax = 0;
            // Process sales order details
            $this->saveSalesReturnDetail($request->details,$salesReturn);

            $id = SalesReturn::where('sales_return_number',$salesReturnNumber)->select('id')->first()->id;
            DB::commit();
            return redirect()->route('transaction.sales_return.create')->with('success', 'Sales Return created successfully.')->with('id',$id);
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to create Sales Invoice: ' . $e->getMessage())->withInput();
        }
    }

    private function saveSalesReturnDetail(array $SalesReturnDetails, $general) {
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

        foreach ($SalesReturnDetails as $detail) {
            $detail['price'] = str_replace(',', '', $detail['price']);
            $detail['disc_percent'] = str_replace(',', '', $detail['disc_percent']??0);
            $detail['disc_nominal'] = str_replace(',', '', $detail['disc_nominal']??0);
            $detail['nominal'] = str_replace(',', '', $detail['nominal']??0);
            $customer = Customer::where('customer_code', $general->customer_code)->first();
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

        foreach ($SalesReturnDetails as $detail) {
            $detail['price'] = str_replace(',', '', $detail['price']);
            $detail['qty'] = str_replace(',', '', $detail['qty']);
            $detail['disc_percent'] = str_replace(',', '', $detail['disc_percent']);
            $detail['disc_nominal'] = str_replace(',', '', $detail['disc_nominal']);
            $detail['sales_return_number'] = $general->sales_return_number;
            $detail['company_code'] = $general->company_code;
            $detail['department_code'] = 'DP01';
            $nominal += $detail['qty']*$detail['price']-($detail['disc_percent']/100*$detail['qty']*$detail['price'])-$detail['disc_nominal'];
            $detail['nominal'] = str_replace(',', '', $detail['nominal']??0);
            $detail['created_by'] = Auth::user()->username;
            $detail['updated_by'] = Auth::user()->username;
            $detail['unit']=$detail['unit'];
            $item = ItemDetail::where('department_code','DP01')->where('status',true)->where([
                ['unit_conversion', $detail['unit']],
                ['item_code',$detail['item_id']]
                ])->first();
            $detail['base_qty'] = $item->conversion;
            $detail['qty_left'] = $detail['qty'];
            $detail['base_qty_left'] = $detail['base_qty'];
            $detail['base_unit'] = $item->base_unit;
            $detail['description'] = $detail['notes']??'';


            $customer = Customer::where('customer_code', $general->customer_code)->first();
            $itemTax = Item::where('department_code','DP01')->where('item_code', $detail['item_id'])->first();
            $taxs = TaxMaster::where('tax_code', $general->tax)->first();

            $subtotalBeforeTaxBeforeDisc =0;
            $subtotalBeforeTaxAfterDisc =0;
            $discTotalPerItem=0;

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
            $discTotalPerItem = ($detail['disc_percent']/100 * $subtotalBeforeTaxBeforeDisc) + $detail['disc_header'] + $detail['disc_nominal'];
            $subtotalBeforeTaxAfterDisc = $totalPriceBeforeTaxAfterDiscount - $discTotalPerItem;
            $totalAllItemBeforeTax+=$totalPriceBeforeTaxAfterDiscount;
            $priceUpdate = $detail['price'];
            $detail['disc_header'] = $discPerDetail;
            SalesReturnDetail::create($detail);

            //Adjust Qty Left on PI Detail
            // dd($detail);


            $customers = Customer::where('customer_code', $general->customer_code)->first();
            $itemTax = Item::where('department_code','DP01')->where('item_code', $detail['item_id'])->first();
            $categories = ItemCategory::where('item_category_code', $itemTax->item_category)->first();
            //PI Detail Nominal After discount
            $PODJournal1 = new Journal();
            $PODJournal1->document_number = $detail['sales_return_number'];
            $PODJournal1->document_date = $general->document_date;
            $PODJournal1->account_number = $categories->acc_number_sales_return??'1001';
            $PODJournal1->debet_nominal = $totalPriceBeforeTaxAfterDiscount;
            $PODJournal1->credit_nominal = 0;
            $PODJournal1->notes ='Return '. $itemTax->item_name.' ('. $item->unitConversion->unit_name.') : '.$detail['qty'];
            $PODJournal1->company_code = $general->company_code;
            $PODJournal1->department_code = $detail['department_code'];
            $PODJournal1->created_by = Auth::user()->username;
            $PODJournal1->updated_by = Auth::user()->username;
            $PODJournal1 -> save();


            $totalHPP+=$totalPriceBeforeTaxAfterDiscount;
            $itemNameConcat.=$itemTax->item_name." | ";




            $firstQ = InventoryDetail::where('item_id', $detail['item_id'])->orderBy('id','desc')->first();

            $firstQty = $firstQ->last_qty??0;


            $crAt = Carbon::now('Asia/Jakarta');
            if(!is_null($general->id)){
                $crAt = $general->created_at;
            }
            $item = Item::where('item_code',$detail['item_id'])->first();
            $gd = Warehouse::where("warehouse_code",$item->warehouse_code)->first();
            $cogs = Module::getCogs($general->document_date,$detail['item_id'],$general->company_code,$general->department_code,$gd->id);

            InventoryDetail::insert([
                'document_number'=>$detail['sales_return_number'],
                'document_date'=>$general->document_date,
                'transaction_type'=>'Sales Return',
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
                'total' => $totalPriceBeforeTaxAfterDiscount,
                'cogs' => ($cogs * ($detail['qty'] * $detail['base_qty'])),
                'qty_actual' => $detail['qty']
            ]);


        }

        //SR HPP
        $PODJournal1 = new Journal();
        $PODJournal1->document_number = $general->sales_return_number;
        $PODJournal1->document_date = $general->document_date;
        $PODJournal1->account_number = $categories->acc_cogs??'1001';
        $PODJournal1->debet_nominal = 0;
        $PODJournal1->credit_nominal = $totalHPP;
        $PODJournal1->notes ='HPP '. $itemNameConcat;
        $PODJournal1->company_code = $general->company_code;
        $PODJournal1->department_code = $general->department_code;
        $PODJournal1->created_by = Auth::user()->username;
        $PODJournal1->updated_by = Auth::user()->username;
        $PODJournal1 -> save();


        //SR Persediaan
        $PODJournal1 = new Journal();
        $PODJournal1->document_number = $general->sales_return_number;
        $PODJournal1->document_date = $general->document_date;
        $PODJournal1->account_number = $categories->account_inventory??'1001';
        $PODJournal1->debet_nominal = $totalHPP;
        $PODJournal1->credit_nominal = 0;
        $PODJournal1->notes ='Persediaan '. $itemNameConcat;
        $PODJournal1->company_code = $general->company_code;
        $PODJournal1->department_code = $general->department_code;
        $PODJournal1->created_by = Auth::user()->username;
        $PODJournal1->updated_by = Auth::user()->username;
        $PODJournal1 -> save();




        if ($customer->pkp == 1) {
            $addTax = $taxed* $taxs->tax_base * $taxs->tariff/100;
            $revenueTax = 0; //nanti diubah dengan pilihan dari header
        } else {
            $revenueTax = 0;
            $addTax = 0;
        }

        $general->add_tax = $addTax;
        $general->tax_revenue = $revenueTax;

        $general->subtotal = $totalAllItemBeforeTax+$general->disc_nominal;
        $general->total = $general->subtotal-$general->disc_nominal + $general->add_tax + $general->tax_revenue;
        $general->save();
        // dd($salesReturn);


        //PI Header Total
        $POJournal = new Journal();
        $customers = Customer::where('customer_code', $general->customer_code)->first();
        $itemTax = Item::where('department_code','DP01')->where('item_code', $detail['item_id'])->first();
        $taxes = TaxMaster::where('tax_code', $general->tax)->first();
        // dd($POJournal);
        $POJournal->document_number = $general->sales_return_number;
        $POJournal->document_date = $general->document_date;
        $POJournal->account_number = $customers->account_receivable;
        $POJournal->debet_nominal = 0;
        $POJournal->credit_nominal = $general->total;
        $POJournal->notes = 'Return Sales from '.$general->customer_name.' ('.$general->sales_return_number.')';
        $POJournal->company_code = $general->company_code;
        $POJournal->department_code = $general->department_code;
        $POJournal->created_by = Auth::user()->username;
        $POJournal->updated_by = Auth::user()->username;

        $POJournal -> save();


        //PI Header Add Tax

        $POJournal2 = new Journal();
        $customers = Customer::where('customer_code', $general->customer_code)->first();
        $itemTax = Item::where('department_code','DP01')->where('item_code', $detail['item_id'])->first();
        $POJournal2->document_number = $general->sales_return_number;
        $POJournal2->document_date = $general->document_date;
        $POJournal2->account_number = $customers->account_add_tax;
        $POJournal2->debet_nominal = $general->add_tax;
        $POJournal2->credit_nominal = 0;
        $POJournal2->notes = 'Return Add. tax on sales from '.$general->customer_name.' ('.$general->sales_return_number.')';
        $POJournal2->company_code = $general->company_code;
        $POJournal2->department_code = $general->department_code;
        $POJournal2->created_by = Auth::user()->username;
        $POJournal2->updated_by = Auth::user()->username;

        $POJournal2 -> save();


        Receivable::create([
            'document_number'=>$general->sales_return_number,
            'document_date'=>$general->document_date,
            'due_date'=>$general->due_date,
            'total_debt'=>$general->total*(-1),
            'debt_balance'=>$general->total*(-1),
            'customer_code'=>$general->customer_code,
            'due_date'=>$general->due_date,
            'company_code'=>$general->company_code,
            'department_code'=>$general->department_code,
            'created_by' => Auth::user()->username,
            'updated_by' => Auth::user()->username,
        ]);

    }
}
