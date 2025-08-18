<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\PointOfSale;
use App\Models\PointOfSaleDetail;
use App\Models\PaymentMethod;
use App\Models\Customer;
use App\Models\Department;
use App\Models\HoldOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class POSController extends Controller
{
    public function showCustomerPage()
    {
        return view('pos.customer');
    }

    /**
     * Display the POS interface with items and payment methods.
     */
    public function index()
    {
        // Get items along with their sales prices
        $items = Item::with(['itemSalesPrices' => function ($query) {
            $query->select('id', 'item_code', 'barcode', 'sales_price');
        }])->get();

        // Get all payment methods
        $paymentMethods = PaymentMethod::all();

        // Get the first Point of Sale record (if needed)
        $pointOfSale = PointOfSale::first();

        // Get total_amount sum grouped by payment_method and ordered by payment_method
        $totalAmountByPaymentMethod = PaymentMethod::leftJoin('point_of_sales', 'payment_method.payment_method_code', '=', 'point_of_sales.payment_method')
        ->select('payment_method.payment_method_code', 'payment_method.payment_name')
        ->selectRaw('COALESCE(SUM(point_of_sales.total_amount), 0) AS total_amount')
        ->groupBy(
            'payment_method.payment_method_code',
            'payment_method.payment_name'
        )
        ->orderBy('payment_method.payment_name')
        ->get();

        // Get customer details
        $customers = Customer::select(
            'customer_code',
            'customer_name',
            'address',
            'warehouse_address',
            'phone_number',
            'pkp',
            'include',
            'account_receivable',
            'account_dp',
            'account_add_tax',
            'account_add_tax_bonded_zone',
            'company_code'
        )->get();

        // Get hold orders data
        $holdOrders = HoldOrder::select(
            'id',
            'created_at',
            'reference_id'
        )->orderBy(
            'created_at',
            'desc'
        )->get();

        // Calculate total sales, refund, and payment amounts
        $totalSales = PointOfSale::sum('total_amount'); // Replace with correct calculation logic for total sales
        $totalRefund = PointOfSale::sum('total_amount');
        $totalPayment = PointOfSale::sum('total_amount'); // Replace with correct calculation logic for total payments

        // Pass the variables to the view
        return view('pos.index', compact(
            'paymentMethods',
            'totalAmountByPaymentMethod',
            'items',
            'customers',
            'pointOfSale',
            'holdOrders',
            'totalSales',
            'totalRefund',
            'totalPayment'
        ));
    }



    /**
     * Store a new POS transaction.
     */
    public function store(Request $request)
    {
        // Validasi data yang diterima
        $request->validate([
            'items' => 'required|array',
            'total_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'final_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'cash_received' => 'nullable|numeric|min:0',
            'change' => 'nullable|numeric|min:0',
            'fullname' => 'required|string',
            'notes' => 'nullable|string',
            'department_code' => 'required|string',
            'customer_id' => 'required|string|exists:customer,customer_code',
        ]);

        try {
            DB::beginTransaction();

            // Buat data transaksi baru di tabel PointOfSale
            $pointOfSale = PointOfSale::create([
                'pos_number' => $this->generatePOSNumber(),
                'transaction_date' => Carbon::now(),
                'total_amount' => $request->total_amount,
                'discount' => $request->discount ?? 0,
                'final_amount' => $request->final_amount,
                'payment_method' => $request->payment_method,
                'cash_received' => $request->cash_received ?? 0,
                'change' => $request->change ?? 0,
                'notes' => $request->notes ?? null,
                'created_by' => $request->fullname,
                'customer_id' => $request->customer_id, // Simpan customer_id di sini
            ]);

            // Menyimpan detail item yang dibeli
            foreach ($request->items as $item) {
                PointOfSaleDetail::create([
                    'point_of_sale_id' => $pointOfSale->id,
                    'item_id' => $item['id'],
                    'item_name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $item['qty'],
                    'subtotal' => $item['price'] * $item['qty'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Transaction saved successfully!',
                'pos_number' => $pointOfSale->pos_number,
                'posId' => $pointOfSale->id,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return response()->json([
                'message' => 'Failed to save transaction.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Fungsi untuk menampilkan daftar POS
    public function postList()
    {
        // Ambil semua data POS dengan 'transaction_date' dan 'total_amount'
        $posList = PointOfSale::all();

        return view('pos.pos_list', compact('posList'));
    }

    public function posReceipt($posId)
    {
        // Ensure the posId is treated as a string
        $posId = (string) $posId;

        // Retrieve the specific Point of Sale data by ID with details
        $pointOfSale = PointOfSale::with('details')->find($posId);

        // Check if the POS exists
        if (!$pointOfSale) {
            return abort(404, 'POS not found');
        }

        $department = Department::where('department_code', Auth::user()->department)->first();
        // dd($department);
        // Load the HTML view with the POS data
        $view = view('pos.receipt', compact('pointOfSale', 'department'))->render();

        // Sanitize POS number to avoid / and \ characters
        $posNumberSanitized = preg_replace('/[\/\\\]/', '-', $pointOfSale->pos_number);

        // Generate PDF using DomPDF
        $pdf = Pdf::loadHTML($view);

        // Output the PDF to the browser with sanitized POS number
        return $pdf->stream("POS_Receipt_{$posNumberSanitized}.pdf");
    }

    public function printReceipt($id)
    {
        // Cek apakah data ada di database dengan ID transaksi yang benar
        $pointOfSale = PointOfSale::with('details')->find($id);

        // Jika data tidak ditemukan, kembalikan pesan error atau halaman 404
        if (!$pointOfSale) {
            // return response()->json(['error' => 'Data not found'], 404);
        }

        // Ambil data departemen dari tabel departments (asumsi hanya ada satu departemen terkait)
        $department = Department::where('department_code', Auth::user()->department)->first();

        // Jika data departemen tidak ditemukan
        if (!$department) {
            // return response()->json(['error' => 'Department data not found'], 404);
        }

        // Jika data ditemukan, lanjutkan ke view
        return view('pos.print_receipt', compact('pointOfSale', 'department'));
    }



    /**
     * Generate a unique POS number.
     */
    private function generatePOSNumber()
    {
        $today = Carbon::now();
        $month = $today->format('n');
        $year = $today->format('y');

        $romanMonths = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];
        $romanMonth = $romanMonths[$month];

        // Get the last POS number for the current month
        $lastPOS = PointOfSale::whereYear('created_at', $today->year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        // Generate a new sequential number
        $newNumber = $lastPOS
            ? str_pad((int)substr($lastPOS->pos_number, -5) + 1, 5, '0', STR_PAD_LEFT)
            : '00001';

        return "VM/INV/{$romanMonth}/{$year}-{$newNumber}/POS";
    }
}
