<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Journal;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceDetail;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceDetail;
use App\Models\Reimburse;
use App\Models\ReimburseDetail;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Coa;
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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReimburseController extends Controller
{
    public function index()
    {
        $reimburses = Reimburse::orderBy('reimburse_number', 'asc')->get();

        return view('transaction.reimburse.reimburse_list', [
            'reimburses' => $reimburses,
        ]);
    }


    public function create(Request $request)
    {
        // Fetch sales orders with status 'Not' or 'Not Ready'
        $salesOrders = SalesOrder::where(function ($query) {
            $query->where('status_reimburse', 'Not');
        })
        ->orderBy('sales_order_number', 'asc')
        ->get();

        // Set the selected sales order number
        $selectedSalesOrderNumber = $request->input('sales_order_number');

        // Fetch related data needed for the view
        $customers = Customer::orderBy('customer_code', 'asc')->get();
        $currencies = Currency::orderBy(
            'currency_code',
            'asc'
        )->get();
        $departments = Department::orderBy('department_code', 'asc')->get();

        $items = Item::where('department_code','DP01')->get();
        $coas = Coa::orderBy('account_number','asc')->get();
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
        return view('transaction.reimburse.reimburse_input', compact(
            'salesOrders',
            'customers',
            'currencies',
            'departments',
            'items',
            'itemDetails',
            'itemUnits',
            'prices',
            'coas',
            'company',
            // 'reimburseNumber',
            'taxs',
            'selectedSalesOrderNumber' // Pass the selected sales order number to the view
        ));
    }

    public function fetchItems(Request $request)
    {
        $salesOrderNumber = $request->query('sales_order_number');
        $items = PurchaseOrderDetail::whereHas('purchaseOrder', function ($query) use ($salesOrderNumber) {
            $query->where('sales_order_number', $salesOrderNumber);
        })->get();
        $so = SalesOrder::where('sales_order_number',$salesOrderNumber)->with('customers')->first();

        return response()->json(
            ['item'=>$items,
            'so'=>$so,
        ]);
    }

    public function store(Request $request)
    {
        $salesInv = SalesInvoice::where('sales_order_number', $request->sales_order_number)->first();
        // Generate a new purchase order number
        $reimburseNumber  = $salesInv->sales_invoice_number. '/R';
        DB::beginTransaction();
        try {
            // Generate a new Reimburse instance
            $reimburse = new Reimburse();
            $reimburse->reimburse_number = $reimburseNumber;
            $reimburse->contract_document_number = $request->sales_order_number;
            $reimburse->document_date = $request->document_date;
            $reimburse->due_date = $request->due_date;
            $reimburse->total = $request->total;
            $reimburse->created_by = Auth::user()->username;
            $reimburse->updated_by = Auth::user()->username;

            $reimburse->save();

            Receivable::create([
                'document_number'=>$reimburseNumber,
                'document_date'=>$request->document_date,
                'due_date'=>Carbon::parse($request->due_date)->format('Y-m-d'),
                'total_debt'=>$request->total,
                'debt_balance'=>$request->total,
                'customer_code'=>$request->customer_code,
                'due_date'=>$request->due_date,
                'company_code'=>$salesInv->company_code,
                'department_code'=>$salesInv->department_code,
                'created_by' => Auth::user()->username,
                'updated_by' => Auth::user()->username,
            ]);
            if (isset($request->details) && is_array($request->details)) {
                foreach ($request->details as $detail) {
                    $reimburseDetail = new ReimburseDetail();
                    $reimburseDetail->reimburse_number = $reimburseNumber;
                    $reimburseDetail->sales_invoice_vendor = $detail['sales_invoice_number'];
                    $reimburseDetail->item_description = $detail['item_description'];
                    $reimburseDetail->account_number = $detail['account_number'];
                    $reimburseDetail->price = $detail['price'];
                    $reimburseDetail->created_by = Auth::user()->username;
                    $reimburseDetail->updated_by = Auth::user()->username;
                    $reimburseDetail->save();

                    $journal = new Journal();
                    $journal->document_number = $reimburseNumber;
                    $journal->document_date = $request->document_date;
                    $journal->account_number = $detail['account_number'];
                    $journal->debet_nominal = 0;
                    $journal->credit_nominal = $detail['price'];
                    $journal->notes = $detail['item_description'];
                    $journal->company_code = $request->company_code;
                    $journal->department_code = $request->department_code;
                    $journal->company_code=$salesInv->company_code;
                    $journal->department_code=$salesInv->department_code;
                    $journal->created_by = Auth::user()->username;
                    $journal->updated_by = Auth::user()->username;
                    $journal -> save();

                }
            }

            $journal1 = new Journal();
            $journal1->document_number = $reimburseNumber;
            $journal1->document_date = $request->document_date;
            $journal1->account_number = $detail['account_number'];
            $journal1->debet_nominal = $request->total;
            $journal1->credit_nominal = 0;
            $journal1->notes = 'Reimbursement for '.$request->sales_order_number;
            $journal1->company_code = $request->company_code;
            $journal1->department_code = $request->department_code;
            $journal1->company_code=$salesInv->company_code;
            $journal1->department_code=$salesInv->department_code;
            $journal1->created_by = Auth::user()->username;
            $journal1->updated_by = Auth::user()->username;
            $journal1 -> save();

            SalesOrder::where('sales_order_number', $request->sales_order_number)->update(['status_reimburse' => 'Ready']);
            SalesInvoice::where('sales_order_number', $request->sales_order_number)->update(['reimburse_status' => 'Ready']);

            DB::commit();
            return redirect()->route('transaction.reimburse')->with('success', 'Reimbursement submitted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            // Log the error for further analysis
            Log::error('Error creating Purchase Order: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create Purchase Order: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        // Fetch the existing purchase order
        $reimburse = Reimburse::findOrFail($id);
        $reimburseNumber = $reimburse->reimburse_number; // Get the existing order number for the edit view

        // Format document_date and delivery_date using Carbon
        $reimburse->document_date = Carbon::parse($reimburse->document_date)->format('Y-m-d'); // or any format you prefer
        $reimburse->delivery_date = Carbon::parse($reimburse->delivery_date)->format('Y-m-d'); // or any format you prefer
        $reimburse->due_date = Carbon::parse($reimburse->due_date)->format('Y-m-d');

        // Fetch related sales orders


        // Fetch purchase order details
        $reimburseDetails = ReimburseDetail::where('reimburse_number', $reimburseNumber)->get();
        $salesOrderDetail = SalesOrderDetail::where('sales_order_number',$reimburse->sales_order_number)->get();
        $salesOrder = SalesOrder::where('sales_order_number',$reimburse->contract_document_number)->first();
        // dd($salesOrder);

        $coas = Coa::orderBy('account_number','asc')->get();
        // Fetch other related data needed for the view
        $customers = Customer::where('customer_code', $salesOrder->customer_code)->first();

        // Return the view with the existing purchase order and related data
        return view('transaction.reimburse.reimburse_edit', compact(
            'salesOrder',
            'reimburse',
            'reimburseDetails',
            'salesOrderDetail',
            'customers',
            'reimburseNumber',
            'coas',
            // 'formattedDocumentDate', // Pass formatted document date to the view
            // 'formattedDeliveryDate' // Pass formatted delivery date to the view
        ));
    }


    public function update(Request $request, $id)
    {
        $salesInv = SalesInvoice::where('sales_order_number', $request->sales_order_number)->first();
        DB::beginTransaction(); // Start the transaction
        try {
            // Retrieve the Reimburse record by its ID
            $reimburse = Reimburse::findOrFail($id);
            $reimburseNumber = $reimburse->reimburse_number;
            $reimburse->due_date = Carbon::createFromFormat('Y-m-d', $request->due_date);

            $reimburse->updated_by = Auth::user()->username;

            $reimburse->save();

            // Clear existing purchase order details
            ReimburseDetail::where('reimburse_number', $reimburse->reimburse_number)->delete();
            Journal::where('document_number', $reimburseNumber)->delete();
            if (isset($request->details) && is_array($request->details)) {
                foreach ($request->details as $detail) {
                    $reimburseDetail = new ReimburseDetail();
                    $reimburseDetail->reimburse_number = $reimburseNumber;
                    $reimburseDetail->sales_invoice_vendor = $detail['sales_invoice_number'];
                    $reimburseDetail->item_description = $detail['item_description'];
                    $reimburseDetail->account_number = $detail['account_number'];
                    $reimburseDetail->price = $detail['price'];
                    $reimburseDetail->created_by = Auth::user()->username;
                    $reimburseDetail->updated_by = Auth::user()->username;
                    $reimburseDetail->save();

                    $journal = new Journal();
                    $journal->document_number = $reimburseNumber;
                    $journal->document_date = $request->document_date;
                    $journal->account_number = $detail['account_number'];
                    $journal->debet_nominal = 0;
                    $journal->credit_nominal = $detail['price'];
                    $journal->notes = $detail['item_description'];
                    $journal->company_code = $request->company_code;
                    $journal->department_code = $request->department_code;
                    $journal->company_code=$salesInv->company_code;
                    $journal->department_code=$salesInv->department_code;
                    $journal->created_by = Auth::user()->username;
                    $journal->updated_by = Auth::user()->username;
                    $journal -> save();

                }
            }

            $journal1 = new Journal();
            $journal1->document_number = $reimburseNumber;
            $journal1->document_date = $request->document_date;
            $journal1->account_number = $detail['account_number'];
            $journal1->debet_nominal = $request->total;
            $journal1->credit_nominal = 0;
            $journal1->notes = 'Reimbursement for '.$request->sales_order_number;
            $journal1->company_code = $request->company_code;
            $journal1->department_code = $request->department_code;
            $journal1->company_code=$salesInv->company_code;
            $journal1->department_code=$salesInv->department_code;
            $journal1->created_by = Auth::user()->username;
            $journal1->updated_by = Auth::user()->username;
            $journal1 -> save();

            Receivable::where('document_number', $reimburseNumber)->update(['due_date'=>$reimburse->due_date]);

            DB::commit(); // Commit the transaction
            return redirect()->route('transaction.reimburse')->with('success', 'Purchase Order updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            // dd($e);
            Log::error('Failed to update Purchase Order: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update Purchase Order: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $general = Reimburse::findOrFail($id);
            Reimburse::where('id', $id)->delete();
            ReimburseDetail::where('reimburse_number', $general->reimburse_number)->delete();
            Journal::where('document_number',$general->reimburse_number)->delete();
            Receivable::where('document_number',$general->reimburse_number)->delete();

            SalesOrder::where('sales_order_number', $general->contract_document_number)->update(['status_reimburse' => 'Not']);
            SalesInvoice::where('sales_order_number', $general->contract_document_number)->update(['reimburse_status' => 'Ready']);
            $general->delete();

            return redirect()->route('transaction.reimburse')->with('success', 'Purchase Order deleted successfully.');
        } catch (\Exception $e) {
            dd($e);
            \Log::error('Failed to delete Purchase Order: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete Purchase Order: ' . $e->getMessage());
        }
    }

    public function printPDF($reimburse_number)
    {
        $reimburse = Reimburse::with([
            'sos',
            'details',
        ])->where('id', $reimburse_number)->firstOrFail();
        // dd($reimburse);
            $imagePath = storage_path('app/images/ttd.png');
            $imageData = file_get_contents($imagePath);
            $totalHuruf = $this->numberToWords($reimburse->total);
            // Generate and return PDF
            $pdf = \PDF::loadView('transaction.reimburse.reimburse_pdf', compact('reimburse','totalHuruf','imageData'));
            $nameFile = Str::replace("/", "", $reimburse->reimburse_number);
            return $pdf->stream("Reimburse_Invoice_{$nameFile}.pdf");

    }
    function numberToWords($number) {
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
