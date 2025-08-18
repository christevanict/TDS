<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Coa;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\CategoryCustomer;
use App\Models\Item;
use App\Models\Journal;
use App\Models\DeliveryOrder;
use App\Models\ItemUnit;
use App\Models\InventoryDetail;
use App\Models\DeliveryOrderDetail;
use App\Models\ItemDetail;
use App\Models\DeleteLog;
use App\Models\ItemCategory;
use App\Models\Department;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceDetail;
use App\Models\ItemSalesPrice;
use App\Models\SalesOrder;
use App\Models\ReceivablePaymentDetail;
use App\Models\SalesDebtCreditNote;
use App\Models\TaxMaster;
use App\Models\Receivable;
use App\Models\SalesOrderDetail;
use App\Models\Warehouse;
use App\Http\Controllers\Module;
use App\Models\InventoryDetailRecap;
use App\Models\PayablePaymentDetail;
use App\Models\ReceivableListDetail;
use App\Models\ReceivableListSalesmanDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use TCPDF;

class SalesInvoiceRecapController extends Controller
{
    public function index() {
        $salesInvoices = SalesInvoice::where('recap', 'yes')->orderBy('id','desc')->get();
        $privileges = Auth::user()->roles->privileges['sales_invoice'];

        return view('transaction.sales-invoice-recap.sales_invoice_list', compact('salesInvoices','privileges'));
    }

    public function summary(Request $request)
    {
        // Initialize query for fetching sales invoices
        $query = SalesInvoice::query();

        // Apply date filtering if 'from_date' and 'to_date' are present in the request
        if ($request->filled('from_date') && $request->filled('to_date')) {
            // Convert dates to Carbon instances for proper formatting and range filtering
            $fromDate = Carbon::parse($request->input('from_date'))->startOfDay();
            $toDate = Carbon::parse($request->input('to_date'))->endOfDay();

            // Apply the date range filter on the 'document_date' column
            $query->whereBetween('document_date', [$fromDate, $toDate]);
        }

        // Retrieve filtered sales invoices
        $salesInvoices = $query->where('recap','yes')->orderBy('id','asc')->get();

        // Calculate the total amount from all filtered sales invoices
        $totalAmount = $salesInvoices->sum('total');

        // Return the view with the sales invoices data and total amount
        return view('transaction.sales-invoice-recap.sales_invoice_summary', compact('salesInvoices', 'totalAmount'))
        ->with([
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date')
        ]);
    }

    public function summaryDetail(Request $request)
    {
        // Initialize query for fetching sales invoices
        $query = SalesInvoice::query();

        // Apply date filtering if 'from_date' and 'to_date' are present in the request
        if ($request->filled('from_date') && $request->filled('to_date')) {
            // Convert dates to Carbon instances for proper formatting and range filtering
            $fromDate = Carbon::parse($request->input('from_date'))->startOfDay();
            $toDate = Carbon::parse($request->input('to_date'))->endOfDay();

            // Apply the date range filter on the 'document_date' column
            $query->whereBetween('document_date', [$fromDate, $toDate]);
        }

        // Retrieve filtered sales invoices
        $salesInvoices = $query->where('recap','yes')->with('details')->orderBy('id','asc')->get();

        // Calculate the total amount from all filtered sales invoices
        $totalAmount = $salesInvoices->sum('total');

        // Return the view with the sales invoices data and total amount
        return view('transaction.sales-invoice-recap.sales_invoice_summary_detail', compact('salesInvoices', 'totalAmount'))
        ->with([
            'from_date' => $request->input('from_date'),
            'to_date' => $request->input('to_date')
        ]);
    }



    public function showImage($filename)
    {
        $path = storage_path('app/images/' . $filename);

        if (!file_exists($path)) {
            abort(404); // If the file doesn't exist, return a 404 error
        }

        // Optionally, you can add authorization checks here to control access
        // dd('a');
        return response()->file($path);
    }

    public function printSalesInvoiceAll($sales_invoice_number)
    {
        // Retrieve the sales invoice with related data
        $salesInvoice = SalesInvoice::with([
            'company',
            'department',
            'customers',
            'details' => function ($query) {
                $query->orderBy('id', 'asc');
            },
            'details.items.itemDetails.unitConversion', // Nested relationships still load
        ])->findOrFail($sales_invoice_number);
        // dd($salesInvoice);
        $totalDiscount= 0;
        foreach ($salesInvoice->details as $key => $value) {
            $subtotal = $value->qty*$value->base_qty*$value->price;
            $totalDiscount+= ($subtotal *$value->disc_percent/100) +$value->disc_nominal;
        }

        $soNumber = explode(",",$salesInvoice->details[0]->sales_order_number)[0];
        if( SalesOrder::where('sales_order_number',$soNumber)->first()){
            $customerOrigin = SalesOrder::where('sales_order_number',$soNumber)->first()->customers;
        }else{
            $customerOrigin = Customer::where('customer_code',$salesInvoice->customers->group_customer)->first();
        }

        // Group the invoice details by item_id (if needed)

        // Generate and return the PDF
        $totalHuruf = ucfirst($this->numberToWords($salesInvoice->total)).' rupiah';
        $tax = TaxMaster::where('tax_code','PPN')->first();
        return view('transaction.sales-invoice-recap.sales_invoice_print_all', compact('salesInvoice','totalHuruf','tax','customerOrigin','totalDiscount'));

        // Create a safe file name by removing any unwanted characters
        $nameFile = Str::replace("/", "", $salesInvoice->sales_invoice_number); // Ensure you have a valid 'sales_invoice_number' property

        // Stream the PDF to the browser
        return $pdf->stream("Sales_Invoice_{$nameFile}.pdf");
    }
    public function printSalesInvoicePDF($sales_invoice_number)
    {
        // Retrieve the sales invoice with related data
        $salesInvoice = SalesInvoice::with([
            'company',
            'department',
            'customers',
            'details' => function ($query) {
                $query->orderBy('id', 'asc');
            },
            'details.items.itemDetails.unitConversion', // Nested relationships still load
        ])->findOrFail($sales_invoice_number);
        $totalDiscount= 0;
        foreach ($salesInvoice->details as $key => $value) {
            $subtotal = $value->qty*$value->base_qty*$value->price;
            $totalDiscount+= ($subtotal *$value->disc_percent/100) +$value->disc_nominal;
        }
        // dd($salesInvoice);

        // Group the invoice details by item_id (if needed)
        $groupedDetails = $salesInvoice->details->groupBy('item_id');

        // Generate and return the PDF
        $totalHuruf = ucfirst($this->numberToWords($salesInvoice->total)).' rupiah';
        $tax = TaxMaster::where('tax_code','PPN')->first();

        //return view('transaction.sales-invoice-recap.sales_invoice_print_webservice', compact('salesInvoice'));
        return view('transaction.sales-invoice-recap.sales_invoice_pdf', compact('salesInvoice', 'groupedDetails','totalHuruf','tax','totalDiscount'));

        // Create a safe file name by removing any unwanted characters
        $nameFile = Str::replace("/", "", $salesInvoice->sales_invoice_number); // Ensure you have a valid 'sales_invoice_number' property

        // Stream the PDF to the browser
        return $pdf->stream("Sales_Invoice_{$nameFile}.pdf");
    }
    public function printSalesInvoicePDFDo($sales_invoice_number)
    {
        // Retrieve the sales invoice with related data
        $salesInvoice = SalesInvoice::with([
            'company',
            'department',
            'customers',
            'details' => function ($query) {
                $query->orderBy('id', 'asc');
            },
            'details.items.itemDetails.unitConversion', // Nested relationships still load
        ])->findOrFail($sales_invoice_number);
        // dd($salesInvoice);
        $soNumber = explode(",",$salesInvoice->details[0]->sales_order_number)[0];
        if( SalesOrder::where('sales_order_number',$soNumber)->first()){
            $customerOrigin = SalesOrder::where('sales_order_number',$soNumber)->first()->customers;
        }else{
            $customerOrigin = Customer::where('customer_code',$salesInvoice->customers->group_customer)->first();
        }

        // Group the invoice details by item_id (if needed)
        $groupedDetails = $salesInvoice->details->groupBy('item_id');

        // Generate and return the PDF
        $totalHuruf = ucfirst($this->numberToWords($salesInvoice->total)).' rupiah';
        // return view('transaction.sales-invoice-recap.sales_invoice_do_print_webservice', compact('salesInvoice'));
        return view('transaction.sales-invoice-recap.sales_invoice_do_pdf', compact('salesInvoice', 'groupedDetails','totalHuruf','customerOrigin'));

        // Create a safe file name by removing any unwanted characters
        $nameFile = Str::replace("/", "", $salesInvoice->sales_invoice_number); // Ensure you have a valid 'sales_invoice_number' property

        // Stream the PDF to the browser
        return $pdf->stream("Sales_Delivery_Order_{$nameFile}.pdf");
    }

    public function printSalesInvoicePDFNetto($sales_invoice_number)
    {
        // Retrieve the sales invoice with related data
        $salesInvoice = SalesInvoice::with([
            'company',
            'department',
            'customers',
            'details' => function ($query) {
                $query->orderBy('id', 'asc');
            },
            'details.items.itemDetails.unitConversion', // Nested relationships still load
        ])->findOrFail($sales_invoice_number);
        $totalDiscount= 0;
        foreach ($salesInvoice->details as $key => $value) {
            $subtotal = $value->qty*$value->base_qty*$value->price;
            $totalDiscount+= ($subtotal *$value->disc_percent/100) +$value->disc_nominal;
        }
        // dd($salesInvoice);
        $soNumber = explode(",",$salesInvoice->details[0]->sales_order_number)[0];
        if( SalesOrder::where('sales_order_number',$soNumber)->first()){
            $customerOrigin = SalesOrder::where('sales_order_number',$soNumber)->first()->customers;
        }else{
            $customerOrigin = Customer::where('customer_code',$salesInvoice->customers->group_customer)->first();
            if(!$customerOrigin){
                $customerOrigin = $salesInvoice->customers;
            }
        }

        // Group the invoice details by item_id (if needed)
        $groupedDetails = $salesInvoice->details->groupBy('item_id');
        $tax = TaxMaster::where('tax_code','PPN')->first();

        $totalHuruf = ucfirst($this->numberToWords($salesInvoice->total)).' rupiah';

        // Generate and return the PDF
        //return view('transaction.sales-invoice-recap.sales_invoice_netto_print_webservice', compact('salesInvoice'));
        return view('transaction.sales-invoice-recap.sales_invoice_netto_pdf', compact('salesInvoice', 'groupedDetails','tax','totalHuruf','customerOrigin','totalDiscount'));

        // Create a safe file name by removing any unwanted characters
        $nameFile = Str::replace("/", "", $salesInvoice->sales_invoice_number); // Ensure you have a valid 'sales_invoice_number' property

        // Stream the PDF to the browser
        return $pdf->stream("Sales_Invoice_{$nameFile}.pdf");
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
            $salesInvoice = SalesInvoice::with('details')->findOrFail($id);
            $departments = Department::where('department_code', $salesInvoice->department_code)->first();
            $coas = Coa::all();
            $items = ItemSalesPrice::where('department_code',$salesInvoice->department_code)->whereHas('items')->orderBy('item_code')->distinct('item_code')->with('items', 'unitn','itemDetails','items.warehouses')->get();
            $itemUnits = ItemUnit::all();
            $itemDetails = ItemDetail::where('department_code',$salesInvoice->department_code)->get();
            $taxs = TaxMaster::all();
            $warehouses = Warehouse::all();


            // dd($SalesInvoice);
            $department_TDS = $salesInvoice->department_code;
            $department_TDSn = Department::where('department_code', $department_TDS)->first();
            $salesInvoiceDetails = SalesInvoiceDetail::where('sales_invoice_number', $salesInvoice->sales_invoice_number)->with(['items','units'])->get();

            //jika warehouse_code kosong ambil dari master item
            foreach($salesInvoiceDetails as $sd){
                if(is_null($sd->warehouse_code)){
                    $gd = $items->where("item_code",$sd->item_id)->first();
                    $sd->warehouse_code = $gd->warehouse_code??null;
                }
            }

            // Format dates for display
            $salesInvoice->document_date = Carbon::parse($salesInvoice->document_date)->format('Y-m-d');
            $salesInvoice->delivery_date = Carbon::parse($salesInvoice->delivery_date)->format('Y-m-d');
            $salesInvoice->due_date = Carbon::parse($salesInvoice->due_date)->format('Y-m-d');

            $editable = true;
            $payable = ReceivablePaymentDetail::where('document_number',$salesInvoice->sales_invoice_number)->get();
            // $returns = SalesReturn::where('sales_invoice_number',$salesInvoice->sales_invoice_number)->get();
            $note = SalesDebtCreditNote::where('invoice_number',$salesInvoice->sales_invoice_number)->get();
            $editable = count($payable)>0 ? false:true;
            // $editable = count($returns)>0 ? false:true;
            $editable = count($note)>0 ? false:true;
            $privileges = Auth::user()->roles->privileges['sales_invoice'];

            return view('transaction.sales-invoice-recap.sales_invoice_edit', compact('salesInvoice', 'salesInvoiceDetails', 'companies', 'departments', 'coas', 'items', 'itemUnits', 'itemDetails', 'taxs', 'department_TDS', 'department_TDSn', 'editable','warehouses','privileges'));
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
            // Retrieve the SalesInvoice record by sales_invoice_number

            $general = SalesInvoice::where('id', $id)->firstOrFail();
            $sales_invoice_number = $general->sales_invoice_number;
            $general->manual_number = $request->manual_number;
            $general->tax_revenue_tariff = $request->tax_revenue;
            $general->document_date = $request->document_date;
            $general->delivery_date = $request->delivery_date;
            $general->due_date = $request->due_date;
            $general->recap = 'yes';
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
            if($request->tax_revenue!=0){
                $tax_revenue_tariffs = TaxMaster::where('tax_code',$request->tax_revenue)->first();
                $tax_revenue_tariff = $tax_revenue_tariffs->tariff;
            }

            $oldDetail  = SalesInvoiceDetail::where('sales_invoice_number', $sales_invoice_number)->get();

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
            InventoryDetailRecap::where('document_number', $general->sales_invoice_number)->delete();
            Receivable::where('document_number', $sales_invoice_number)->delete();
            Journal::where('document_number', $general->sales_invoice_number)->delete();
            SalesInvoiceDetail::where('sales_invoice_number', $general->sales_invoice_number)->delete();

            // Save the details
            $this->saveSalesInvoiceDetails($request->details, $sales_invoice_number, $request->company_code, $request->department_code,$request->customer_code,$general, $tax_revenue_tariff);
            // Parse and assign date fields
            DB::commit(); // Commit the transaction
            return redirect()->route('transaction.sales_invoice_recap')->with('success', 'Sales Invoice updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            // dd($e);
            Log::error($e);
            return redirect()->back()->with('error', 'Failed to update Sales Invoice: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id) {
        DB::beginTransaction();
        try {
            $general = SalesInvoice::findOrFail($id);
            SalesInvoice::where('sales_invoice_number', $general->sales_invoice_number)->delete();
            $siDetails = SalesInvoiceDetail::where('sales_invoice_number', $general->sales_invoice_number)->get();
            $soNumber = '';
            foreach ($siDetails as $key => $value) {
                if($value->sales_order_number&&$value->so_id){
                    $poDetail = SalesOrderDetail::where('id',$value->so_id)->first();
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
            }
            SalesInvoiceDetail::where('sales_invoice_number', $general->sales_invoice_number)->delete();
            $general->delete();
            InventoryDetailRecap::where('document_number', $general->sales_invoice_number)->delete();

            Journal::where('document_number',$general->sales_invoice_number)->delete();
            Receivable::where('document_number',$general->sales_invoice_number)->delete();

            $reason = $request->input('reason');

            DeleteLog::create([
                'document_number' => $general->sales_invoice_number,
                'document_date' => $general->document_date,
                'delete_notes' => $reason,
                'type' => 'SI',
                'company_code' => $general->company_code,
                'department_code' => $general->department_code,
                'deleted_by' => Auth::user()->username,
            ]);

            DB::commit(); // Commit transaction
            return redirect()->route('transaction.sales_invoice_recap')->with('success', 'Sales Invoice deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            // dd($e);
            Log::error($e);
            return redirect()->route('transaction.sales_invoice_recap')->with('error', 'Error deleting: ' . $e->getMessage());
        }
    }


    private function generateSalesInvoiceNumber($date,$department) {
        // Get today's date components
        $today = Carbon::parse($date);
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
            ->where('department_code',$department)
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
        if($department=='DP02'){
            return "WIL/INV/{$romanMonth}/{$year}-{$newNumber}";
        }else if($department=='DP03'){
            return "DRE/INV/{$romanMonth}/{$year}-{$newNumber}";
        }else{
            return "TDS/INV/{$romanMonth}/{$year}-{$newNumber}";
        }
    }

    public function changeDepartment(Request $request)
    {
        $department = $request->department_code;
        if($department=='DP01'){
            $customers = Customer::whereNot(function ($query) {
                $query->where('customer_code', 'like', 'DP02%')
                    ->orWhere('customer_code', 'like', 'DP03%');
            })->get();
        }else{
            $customers = Customer::where('customer_code', 'like', $department.'%')
                    ->get();
        }
        $items = ItemSalesPrice::where('department_code',$department)->whereHas('items')->orderBy('item_code')->distinct('item_code')->with('items', 'unitn','itemDetails','items.warehouses')->get();
        $itemDetails = ItemDetail::where('department_code',$department)->get();
        return response()->json([
            'customers' => $customers,
            'items' => $items,
            'itemDetails' => $itemDetails
        ]);
    }

    public function create() {
        $companies = Company::all();
        $customers = Customer::whereNot(function ($query) {
            $query->where('customer_code', 'like', 'DP02%')
                ->orWhere('customer_code', 'like', 'DP03%');
        })->get();
        $items = ItemSalesPrice::where('department_code','DP01')->whereHas('items')->orderBy('item_code')->distinct('item_code')->with('items', 'unitn','itemDetails','items.warehouses')->get();
        $itemDetails = ItemDetail::where('department_code','DP01')->get();
        $itemUnits = ItemUnit::all();
        $taxs = TaxMaster::all();
        $department_TDS = 'DP01';
        $department_TDSn = Department::where('department_code', $department_TDS)->first();
        $token = str()->random(16);

        $salesOrder = SalesOrder::with([
            'department',
            'details' => function ($query) {
                $query->where('qty_left', '>', 0)
                    ->with(['items','items.warehouses', 'units']);
            }
        ])
        ->orderBy('id', 'asc')
        ->where('status', '!=', 'Closed')
        ->where('department_code', 'DP01')
        ->get();
        $privileges = Auth::user()->roles->privileges['sales_invoice'];
        // $salesOrderD = SalesOrderDetail::where('qty_left','>',0)->orderBy('id', 'asc')->with(['items','units','items.warehouses'])->get();
        // $itemMb1 = DB::connection('mb1')->table('items')->where('qty','>',0)->select('item_code')->get();

        // $itemMb1 = DB::connection('mb1')
        //     ->table('items')
        //     ->where('qty', '>', 0)
        //     ->pluck('item_code');

        // $salesOrderD = SalesOrderDetail::where('qty_left', '>', 0)
        //     // ->whereIn('item_id', $itemMb1)
        //     ->orderBy('id', 'asc')
        //     ->with(['items', 'units', 'items.warehouses'])
        //     ->get();



        return view('transaction.sales-invoice-recap.sales_invoice_input', compact('companies', 'items', 'customers', 'customers', 'taxs','department_TDS', 'department_TDSn','salesOrder','privileges','items','itemDetails','itemUnits','token'));
    }



    public function store(Request $request) {
        // dd($request->all());

        $exist = SalesInvoice::where('token',$request->token)->where('department_code',$request->department_code)->whereDate('created_at',Carbon::today())->first();
        if($exist){
            $id = SalesInvoice::where('created_by',Auth::user()->username)->orderBy('id','desc')->select('id')->first()->id;
            return redirect()->route('transaction.sales_invoice_recap.create')->with('success', 'Sales Invoice created successfully.')->with('id',$id);
        }

        DB::beginTransaction(); // Begin transaction to ensure atomicity
        try {
            $sales_invoice_number = $this->generateSalesInvoiceNumber($request->document_date,$request->department_code);

            if(SalesInvoice::where('sales_invoice_number', $request->sales_invoice_number)->count() < 1) {

            $general = new SalesInvoice();
            $general->sales_invoice_number = $sales_invoice_number;
            $general->manual_number = $request->manual_number;
            $general->document_date = $request->document_date;
            $general->delivery_date = $request->delivery_date;
            $general->due_date = $request->due_date;
            $general->customer_code = $request->customer_code;
            $general->token = $request->token;
            $general->tax = 'PPN';
            $general->recap = 'yes';
            $general->status = 'Open';
            $general->disc_nominal = str_replace(',', '', $request->disc_nominal??0);
            // $general->con_invoice = $request->con_invoice;
            // $general->status = 'Open';


            $general->notes = $request->notes;
            $general->company_code = $request->company_code;
            $general->department_code = $request->department_code;
            $general->created_by = Auth::user()->username;
            $general->updated_by = Auth::user()->username;

            // Save the main cash in entry
            $tax_revenue_tariff=0;
            if($request->tax_revenue!=0){
                $tax_revenue_tariffs = TaxMaster::where('tax_code',$request->tax_revenue)->first();
                $tax_revenue_tariff = $tax_revenue_tariffs->tariff;
            }
            $general->tax_revenue_tariff = $request->tax_revenue;

            // Save the details
            $this->saveSalesInvoiceDetails($request->details, $sales_invoice_number, $request->company_code, $request->department_code,$request->customer_code,$general, $tax_revenue_tariff);

            $id = SalesInvoice::where('sales_invoice_number',$sales_invoice_number)->select('id')->first()->id;

            DB::commit(); // Commit transaction
            return redirect()->route('transaction.sales_invoice_recap.create')->with('success', 'Sales Invoice created successfully.')->with('id',$id);

            } else {
                return redirect()->back()->with('error', 'Sales Invoice Number must not be the same');
            };


        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction on error
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to create Sales Invoice: ' . $e->getMessage());
        }
    }

    private function saveSalesInvoiceDetails(array $SalesInvoiceDetails, $sales_invoice_number, $company_code, $department_code,$customer_code,$general, $tax_revenue_tariff) {
        $nominal = 0;
        $revenueTax = 0;
        $addTax = 0;
        $services = 0;
        $taxed = 0;
        $totalAllAfterDiscountBeforeTax = 0;
        $totalHPP = 0;
        $itemNameConcat = "";
        foreach ($SalesInvoiceDetails as $detail) {
            $detail['price'] = str_replace(',', '', $detail['price']);
            $detail['disc_percent'] = str_replace(',', '', $detail['disc_percent']??0);
            $detail['disc_nominal'] = str_replace(',', '', $detail['disc_nominal']??0);
            $detail['nominal'] = str_replace(',', '', $detail['nominal']??0);
            $customer = Customer::where('customer_code', $customer_code)->first();
            $itemTax = Item::where('department_code',$general->department_code)->where('item_code', $detail['item_id'])->first();
            $taxs = TaxMaster::where('tax_code', $general->tax)->first();

            if($customer->pkp == 1 && strtolower($itemTax->category->item_category_name) != 'service') {
                if($customer->include == 1) {
                    if ($itemTax->additional_tax == 1 ) {
                        $totalAllAfterDiscountBeforeTax += (($detail['qty']*$detail['price']*$detail['base_qty']) / (1 + $taxs->tax_base* $taxs->tariff / 100)) - ($detail['disc_percent']/100*(($detail['qty']*$detail['price']) / (1 + $taxs->tax_base * $taxs->tariff / 100)))-$detail['disc_nominal'];
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
        foreach ($SalesInvoiceDetails as $index => $detail) {
            $detail['price'] = str_replace(',', '', $detail['price']);
            $detail['disc_percent'] = str_replace(',', '', $detail['disc_percent']??0);
            $detail['disc_nominal'] = str_replace(',', '', $detail['disc_nominal']??0);
            $detail['nominal'] = str_replace(',', '', $detail['nominal']??0);

            // Ensure index is the same as the row number from the form input
            $detail['sales_invoice_number'] = $sales_invoice_number;
            $detail['number_row'] = $index + 1; // Correctly assign row number
            $detail['company_code'] = $company_code;
            $detail['department_code'] = $department_code;
            $nominal += $detail['qty']*$detail['price']-($detail['disc_percent']/100*$detail['qty']*$detail['price'])-$detail['disc_nominal'];
            $detail['created_by'] = Auth::user()->username;
            $detail['updated_by'] = Auth::user()->username;

            $item = ItemDetail::where('department_code',$general->department_code)->where([
                ['unit_conversion', $detail['unit']],
                ['item_code',$detail['item_id']]
                ])->first();
            $detail['base_qty'] = $item->conversion;
            $detail['base_qty_left'] = $detail['base_qty']*$detail['qty'];
            $detail['base_unit'] = $item->base_unit;
            $detail['status'] = 'Not';
            $detail['qty_left'] = $detail['qty'];


            $customer = Customer::where('customer_code', $customer_code)->first();
            $itemTax = Item::where('department_code',$general->department_code)->where('item_code', $detail['item_id'])->first();
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
            SalesInvoiceDetail::create($detail);
            // dd($detail);


            $itemUnit = ItemSalesPrice::where('department_code',$general->department_code)->where('item_code', $detail['item_id'])->first();

            $customers = Customer::where('customer_code', $customer_code)->first();
            $itemTax = Item::where('department_code',$general->department_code)->where('item_code', $detail['item_id'])->first();
            $categories;
            if($itemTax){
                $categories = ItemCategory::where('item_category_code', $itemTax->item_category)->first();
            }

            //PI Detail Value
            $SIJournalD = new Journal();
            $SIJournalD->document_number = $detail['sales_invoice_number'];
            $SIJournalD->document_date = $general->document_date;
            $SIJournalD->account_number = $categories->acc_number_sales??'1001';

            $SIJournalD->debet_nominal = 0;
            $SIJournalD->credit_nominal = $totalPriceBeforeTaxAfterDiscount+$totalDiscountPerDetail;
            $SIJournalD->notes = 'Sales '.$itemTax->item_name.' : '.$detail['qty'].' '.$itemUnit->units->unit_name ;
            $SIJournalD->company_code = $general->company_code;
            $SIJournalD->department_code = $general->department_code;
            $SIJournalD->created_by = Auth::user()->username;
            $SIJournalD->updated_by = Auth::user()->username;
            $SIJournalD -> save();


            //PI Discount Total(Discount per detail + Discount Allocation from header)
            $SIJournalDd = new Journal();
            $SIJournalDd->document_number = $detail['sales_invoice_number'];
            $SIJournalDd->document_date = $general->document_date;
            $SIJournalDd->account_number = $categories->acc_number_sales_discount??'1001';
            $SIJournalDd->debet_nominal = $totalDiscountPerDetail;
            $SIJournalDd->credit_nominal = 0;
            $SIJournalDd->notes = 'Discount on sales '.$itemTax->item_name.' : '.$detail['qty'].' '.$itemUnit->units->unit_name ;
            $SIJournalDd->company_code = $general->company_code;
            $SIJournalDd->department_code = $general->department_code;
            $SIJournalDd->created_by = Auth::user()->username;
            $SIJournalDd->updated_by = Auth::user()->username;
            $SIJournalDd -> save();


            $totalHPP+=$totalPriceBeforeTaxAfterDiscount;
            $itemNameConcat.=$itemTax->item_name." | ";



            $firstQ = InventoryDetail::where('item_id', $detail['item_id'])->orderBy('id','desc')->first();

            $firstQty = $firstQ->last_qty??0;


            $crAt = Carbon::now('Asia/Jakarta');
            if(!is_null($general->id)){
                $crAt = $general->created_at;
            }

            $gd = Warehouse::where("warehouse_code",$detail["warehouse_code"])->first();
            $cogs = Module::getCogs($general->document_date,$detail['item_id'],$general->company_code,$general->department_code,$gd->id);

            InventoryDetailRecap::insert([
                'document_number'=>$detail['sales_invoice_number'],
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
            }
        }

        //SI HPP
        $SIJournalDd = new Journal();
        $SIJournalDd->document_number = $general->sales_invoice_number;
        $SIJournalDd->document_date = $general->document_date;
        $SIJournalDd->account_number = $categories->acc_cogs??'1001';
        $SIJournalDd->debet_nominal = $totalHPP;
        $SIJournalDd->credit_nominal = 0;
        $SIJournalDd->notes = 'HPP '.$itemNameConcat;
        $SIJournalDd->company_code = $general->company_code;
        $SIJournalDd->department_code = $general->department_code;
        $SIJournalDd->created_by = Auth::user()->username;
        $SIJournalDd->updated_by = Auth::user()->username;
        $SIJournalDd -> save();

        //SI Persediaan
        $SIJournalDd = new Journal();
        $SIJournalDd->document_number = $general->sales_invoice_number;
        $SIJournalDd->document_date = $general->document_date;
        $SIJournalDd->account_number = $categories->account_inventory??'1001';
        $SIJournalDd->debet_nominal = 0;
        $SIJournalDd->credit_nominal = $totalHPP;
        $SIJournalDd->notes = 'Inventory '.$itemNameConcat ;
        $SIJournalDd->company_code = $general->company_code;
        $SIJournalDd->department_code = $general->department_code;
        $SIJournalDd->created_by = Auth::user()->username;
        $SIJournalDd->updated_by = Auth::user()->username;
        $SIJournalDd -> save();


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

            //PI Header Total Purchase
            $PIJournal = new Journal();
            $PIJournal->document_number = $general->sales_invoice_number;
            $PIJournal->document_date = $general->document_date;
            $PIJournal->account_number = $customers->account_receivable??'1001';
            $PIJournal->debet_nominal = $general->total;
            $PIJournal->credit_nominal = 0;
            $PIJournal->notes = 'Sales from '.$customers->customer_name.' ('.$general->sales_invoice_number.')';
            $PIJournal->company_code = $general->company_code;
            $PIJournal->department_code = $general->department_code;
            $PIJournal->created_by = Auth::user()->username;
            $PIJournal->updated_by = Auth::user()->username;
            $PIJournal -> save();

            //PI Header Discount
            if($general->disc_nominal>0){
                $itemTax = Item::where('department_code',$general->department_code)->where('item_code', $detail['item_id'])->first();
                $categories;
                if($itemTax){
                    $categories = ItemCategory::where('item_category_code', $itemTax->item_category)->first();
                }
                //
                $PIJournald = new Journal();
                $PIJournald->document_number = $general->sales_invoice_number;
                $PIJournald->document_date = $general->document_date;
                $PIJournald->account_number = $categories->acc_number_sales_discount??'1001';
                $PIJournald->debet_nominal = $general->disc_nominal;
                $PIJournald->credit_nominal = 0;
                $PIJournald->notes = 'Discount on sales for '.$customers->customer_name.' ('.$general->sales_invoice_number.')';
                $PIJournald->company_code = $general->company_code;
                $PIJournald->department_code = $general->department_code;
                $PIJournald->created_by = Auth::user()->username;
                $PIJournald->updated_by = Auth::user()->username;
                // $PIJournald -> save();
            }

            //PI Header Add Tax
            $PIJournala = new Journal();
            $PIJournala->document_number = $general->sales_invoice_number;
            $PIJournala->document_date = $general->document_date;
            $PIJournala->account_number = $customers->account_add_tax??'1001';
            $PIJournala->debet_nominal = 0;
            $PIJournala->credit_nominal = $general->add_tax;
            $PIJournala->notes = 'Add. Tax on sales for '.$customers->customer_name.' ('.$general->sales_invoice_number.')';
            $PIJournala->company_code = $general->company_code;
            $PIJournala->department_code = $general->department_code;
            $PIJournala->created_by = Auth::user()->username;
            $PIJournala->updated_by = Auth::user()->username;
            $PIJournala -> save();

            //PI Header Total Discount
            $PIJournala = new Journal();
            $PIJournala->document_number = $general->sales_invoice_number;
            $PIJournala->document_date = $general->document_date;
            $PIJournala->account_number = $customers->account_add_tax??'1001';
            $PIJournala->debet_nominal = $totalAllDiscountDetail;
            $PIJournala->credit_nominal = 0;
            $PIJournala->notes = 'Discount Items on sales for '.$customers->customer_name.' ('.$general->sales_invoice_number.')';
            $PIJournala->company_code = $general->company_code;
            $PIJournala->department_code = $general->department_code;
            $PIJournala->created_by = Auth::user()->username;
            $PIJournala->updated_by = Auth::user()->username;
            // $PIJournala -> save();

            //PI Header Revenue Tax
            if($taxes){
                $PIJournala = new Journal();
                $PIJournala->document_number = $general->sales_invoice_number;
                $PIJournala->document_date = $general->document_date;
                $PIJournala->account_number = $taxes->account_number??'1001';
                $PIJournala->debet_nominal = 0;
                $PIJournala->credit_nominal = $general->tax_revenue;
                $PIJournala->notes = 'Revenue Tax on sales for '.$customers->customer_name.' ('.$general->sales_invoice_number.')';
                $PIJournala->company_code = $general->company_code;
                $PIJournala->department_code = $general->department_code;
                $PIJournala->created_by = Auth::user()->username;
                $PIJournala->updated_by = Auth::user()->username;
                $PIJournala -> save();

            }

            Receivable::create([
                'document_number'=>$general->sales_invoice_number,
                'document_date'=>$general->document_date,
                'due_date'=>$general->due_date,
                'total_debt'=>$general->total,
                'debt_balance'=>$general->total,
                'customer_code'=>$general->customer_code,
                'due_date'=>$general->due_date,
                'company_code'=>$general->company_code,
                'department_code'=>$general->department_code,
                'created_by' => Auth::user()->username,
                'updated_by' => Auth::user()->username,
            ]);

    }

}
