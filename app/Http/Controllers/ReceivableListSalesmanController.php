<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Receivable;
use App\Models\ReceivableListDetail;
use App\Models\ReceivableListSalesman;
use App\Models\ReceivableListSalesmanDetail;
use App\Models\Users;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Department;
use App\Models\City;
use App\Models\DeleteLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReceivableListSalesmanController extends Controller
{
    public function index()
    {
        $receivableList = ReceivableListSalesman::where('department_code', 'DP01')->orderBy('receivable_list_salesman_date','asc')->get();
        $user = Users::where('username',Auth::user()->username)->with('roles')->first();
        $privileges = $user->roles->privileges['receivable_list_salesman'];
        return view('transaction.receivable_list_salesman.receivable_list_salesman_list',[
            'receivableLists' => $receivableList,
            'privileges'=>$privileges
        ]);
    }

    public function create() {
        $companies = Company::all();
        $customers = Customer::whereNot(function ($query) {
            $query->where('customer_code', 'like', 'DP02%')
                ->orWhere('customer_code', 'like', 'DP03%');
        })->get();
        //->whereRaw("customer_code <> group_customer")

        $citys = City::all();

        $department_TDS = 'DP01';
        $department_TDSn = Department::where('department_code', $department_TDS)->first();

        $documentRL = ReceivableListDetail::pluck('document_number')->all();

        $receivable = Receivable::with([
            'department','customer'
            ])
        ->orderBy('id', 'asc')
        ->where('department_code', 'DP01')
        ->whereRaw("debt_balance > 0")
        ->whereRelation('salesInvoice','status','Delivered')
        ->whereIn('document_number',$documentRL)
        ->get();
        $privileges = Auth::user()->roles->privileges['receivable_list_salesman'];

        return view('transaction.receivable_list_salesman.receivable_list_salesman_input', compact('companies','citys', 'customers', 'department_TDS', 'department_TDSn','receivable','privileges'));
    }

    public function insert(Request $request){
        DB::beginTransaction();  // Begin the transaction
        try {
            $general = new ReceivableListSalesman();
            $general->receivable_list_salesman_number = $this->generateNumber();
            $general->receivable_list_salesman_date = $request->document_date;
            $general->city_code = $request->city_code;
            $general->company_code = $request->company_code;
            $general->department_code = $request->department_code;
            $general->created_by = Auth::user()->username;
            $general->updated_by = Auth::user()->username;
            $general->save();
            $this->saveDetail($request->details,$general);

            DB::commit();
            return redirect()->route('transaction.receivable_list_salesman')->with('success', 'Tanda Terima created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }

    private function saveDetail($details,$general){
        foreach ($details as $detail) {
            $detail['nominal'] = str_replace(',', '', $detail['nominal']??0);
            $detail['paid'] = str_replace(',', '', $detail['paid']??0);
            $detail['nominal_left'] = str_replace(',', '', $detail['nominal_left']??0);

            $rldet = new ReceivableListSalesmanDetail();
            $rldet->receivable_list_salesman_number = $general->receivable_list_salesman_number;
            $rldet->document_number = $detail["document_number"];
            $rldet->document_date = $detail["document_date"];
            $rldet->customer_code_document = $detail["customer_code"];
            $rldet->nominal = $detail["nominal"];
            $rldet->paid = $detail["paid"];
            $rldet->nominal_left = $detail["nominal_left"];
            $rldet->company_code = $general->company_code;
            $rldet->department_code = $general->department_code;
            $rldet->created_by = $general->created_by;
            $rldet->updated_by = $general->updated_by;
            $rldet->save();
        }
    }

    public function edit($id){
        try {
            $companies = Company::all();

            $department_TDS = 'DP01';
            $department_TDSn = Department::where('department_code', $department_TDS)->first();

            $receivableList = ReceivableListSalesman::findOrFail($id);

            $receivableListDetails = ReceivableListSalesmanDetail::where('receivable_list_salesman_number', $receivableList->receivable_list_salesman_number)->get();

            $customerRL = Customer::where('customer_code', $receivableList->customer_code)->first();

            $customers = Customer::where(function ($query) {
                $query->where('customer_code', 'like', 'DP02%')
                    ->orWhere('customer_code', 'like', 'DP03%');
            })->get();

            // Format dates for display
            $receivableList->document_date = Carbon::parse($receivableList->document_date)->format('Y-m-d');
            $receivableList->periode = Carbon::parse($receivableList->periode)->format('Y-m-d');

            $editable = true;
            //Pengecekan boleh diedit / tidak
            /*
            $payable = ReceivablePaymentDetail::where('document_number',$salesInvoice->sales_invoice_number)->get();
            $note = SalesDebtCreditNote::where('invoice_number',$salesInvoice->sales_invoice_number)->get();
            $editable = count($payable)>0 ? false:true;
            $editable = count($note)>0 ? false:true;
            */
            $privileges = Auth::user()->roles->privileges['receivable_list_salesman'];

            return view('transaction.receivable_list_salesman.receivable_list_salesman_edit', compact('receivableList','receivableListDetails','companies','customers', 'department_TDS', 'department_TDSn', 'editable','privileges'));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to load edit form: ' . $e->getMessage());
        }
    }

    public function update(Request $request,$id)
    {
        DB::beginTransaction();  // Begin the transaction
        try {
            $general = ReceivableListSalesman::findOrFail($id);
            $general->receivable_list_salesman_date = $request->document_date;
            $general->city_code = $request->city_code;
            $general->company_code = $request->company_code;
            $general->department_code = $request->department_code;
            $general->updated_by = Auth::user()->username;
            $general->save();

            ReceivableListSalesmanDetail::where('receivable_list_salesman_number', $general->receivable_list_salesman_number)->delete();
            $this->saveDetail($request->details,$general);

            DB::commit();
            return redirect()->route('transaction.receivable_list_salesman')->with('success', 'Daftar Tagihan updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }
    }

    public function delete(Request $request,$id) {
        DB::beginTransaction();  // Begin the transaction
        try {

            //Pengecekan boleh dihapus / tidak
            /*
            $payable = ReceivablePaymentDetail::where('document_number',$salesInvoice->sales_invoice_number)->get();
            $note = SalesDebtCreditNote::where('invoice_number',$salesInvoice->sales_invoice_number)->get();
            $editable = count($payable)>0 ? false:true;
            $editable = count($note)>0 ? false:true;
            */

            $rl = ReceivableListSalesman::findOrFail($id);
            ReceivableListSalesmanDetail::where('receivable_list_salesman_number', $rl->receivable_list_salesman_number)->delete();

            $reason = $request->reason;

            DeleteLog::create([
                'document_number' => $rl->receivable_list_salesman_number,
                'document_date' => $rl->receivable_list_salesman_date,
                'delete_notes' => $reason,
                'type' => 'RL',
                'company_code' => $rl->company_code,
                'department_code' => $rl->department_code,
                'deleted_by' => Auth::user()->username,
            ]);

            $rl->delete();
            DB::commit();
            return redirect()->route('transaction.receivable_list_salesman')->with('success', 'Daftar Tagihan deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }
    }

    private function generateNumber() {
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

        // Fetch the last sales invoice created
        $lastReceivableList = ReceivableListSalesman::whereYear('created_at', $today->year)
            ->whereMonth('created_at', $month)
            ->where('department_code','DP01')
            ->orderBy('receivable_list_salesman_number', 'desc')
            ->first();

        // Determine the new invoice number
        if ($lastReceivableList) {
            // Extract the last number from the last invoice number
            $lastNumber = (int)substr($lastReceivableList->receivable_list_salesman_number, strrpos($lastReceivableList->receivable_list_salesman_number, '-') + 1);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            // Reset counter to 00001 if no invoices found for the current month
            $newNumber = '00001';
        }

        // Return the new invoice number in the desired format
        return "TDS/DT/{$romanMonth}/{$year}-{$newNumber}";
    }

    public function printPDF($nofaktur)
    {
        $receivableList = ReceivableListSalesman::where('id', $nofaktur)->firstOrFail();
        $receivableListDetail = DB::table("receivable_list_salesman_detail as x")
        ->join("customer as y","y.customer_code","=","x.customer_code_document")
        ->select("x.*","y.customer_name","y.group_customer")
        ->orderBy("y.group_customer")->get();
        // $receivableListDetail = ReceivableListSalesmanDetail::where('receivable_list_salesman_number', $nofaktur)
        // ->orderBy('customer')->get();
        $customers = Customer::all();

        $norek = "";$namacv = "";$imagePath = "";
        switch ($receivableList->department_code) {
            case "DP02" || "DP03":
                $norek = "BCA a/n Honggo Wijoyo 088 7374 698";
                $namacv = "DAMAI";
                $imagePath = storage_path('app/images/Logo DAMAI.jpeg');
            default:
                $norek = "BCA a/n CV TDS 4700 36 8080";
                $namacv = "TDS";
                $imagePath = storage_path('app/images/Logo TDS.jpeg');
        }
        //$imageData = file_get_contents($imagePath);
        $city_code = "";
        foreach(explode("|",$receivableList->city_code) as $val){
            if($city_code == ""){
                $city_code .= $val;
            }else{
                $city_code .= ", ".$val;
            }
        }
        $receivableList->city_code = $city_code;
        $pdf = \PDF::loadView('transaction.receivable_list_salesman.receivable_list_salesman_pdf', compact('receivableList','receivableListDetail','norek','namacv','customers'))
        ->setPaper('A5', 'landscape');
        $nameFile = Str::replace("/", "", $receivableList->receivable_list_salesman_number);
        return $pdf->stream("Receivable_List_{$nameFile}.pdf");
    }
}
