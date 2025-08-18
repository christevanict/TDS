<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Salesman;
use App\Models\Receivable;
use App\Models\ReceivableList;
use App\Models\ReceivableListDetail;
use App\Models\Users;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Department;
use App\Models\DeleteLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReceivableListController extends Controller
{
    public function index()
    {
        $receivableList = ReceivableList::where('department_code', 'DP01')->orderBy('receivable_list_date','asc')->get();
        $salesman = Salesman::where("is_active",1)->get();
        $user = Users::where('username',Auth::user()->username)->with('roles')->first();
        $privileges = $user->roles->privileges['master_salesman'];
        return view('transaction.receivable_list.receivable_list_list',[
            'receivableLists' => $receivableList,
            'salesmans' => $salesman,
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
        $department_TDS = 'DP01';
        $department_TDSn = Department::where('department_code', $department_TDS)->first();

        $documentRL = ReceivableListDetail::pluck('document_number')->all();

        $receivable = Receivable::with([
            'department'
            ])
        ->orderBy('id', 'asc')
        ->where('department_code', 'DP01')
        ->whereRaw("debt_balance > 0")
        ->whereRelation('salesInvoice','status','Delivered')
        ->whereNotIn('document_number',$documentRL)
        ->get();
        $privileges = Auth::user()->roles->privileges['receivable_list'];

        return view('transaction.receivable_list.receivable_list_input', compact('companies', 'customers', 'department_TDS', 'department_TDSn','receivable','privileges'));
    }

    public function insert(Request $request){
        DB::beginTransaction();  // Begin the transaction
        try {
            $general = new ReceivableList();
            $general->receivable_list_number = $this->generateNumber();
            $general->receivable_list_date = $request->document_date;
            $general->customer_code = $request->customer_code;
            $general->periode = $request->periode;
            $general->total = 0;
            $general->company_code = $request->company_code;
            $general->department_code = $request->department_code;
            $general->created_by = Auth::user()->username;
            $general->updated_by = Auth::user()->username;
            $general->save();
            $this->saveDetail($request->details,$general);

            DB::commit();
            return redirect()->route('transaction.receivable_list')->with('success', 'Tanda Terima created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }

    private function saveDetail($details,$general){
        $total = 0;
        foreach ($details as $detail) {
            $detail['nominal'] = str_replace(',', '', $detail['nominal']??0);

            $rldet = new ReceivableListDetail();
            $rldet->receivable_list_number = $general->receivable_list_number;
            $rldet->document_number = $detail["document_number"];
            $rldet->document_date = $detail["document_date"];
            $rldet->customer_code_document = $detail["customer_code"];
            $rldet->nominal = $detail["nominal"];
            $rldet->company_code = $general->company_code;
            $rldet->department_code = $general->department_code;
            $rldet->created_by = $general->created_by;
            $rldet->updated_by = $general->updated_by;
            $rldet->save();
            $total += $detail['nominal'];
        }

        $general->total = $total;
        $general->save();
    }

    public function edit($id){
        try {
            $companies = Company::all();

            $department_TDS = 'DP01';
            $department_TDSn = Department::where('department_code', $department_TDS)->first();

            $receivableList = ReceivableList::findOrFail($id);

            $receivableListDetails = ReceivableListDetail::where('receivable_list_number', $receivableList->receivable_list_number)->get();

            $documentRL = ReceivableListDetail::where('receivable_list_number','<>', $receivableList->receivable_list_number)->pluck('document_number')->all();

            $customerRL = Customer::where('customer_code', $receivableList->customer_code)->first();

            $customers = Customer::where(function ($query) use($receivableList,$customerRL) {
                $query->where('customer_code', $receivableList->customer_code)
                    ->orWhere('group_customer', $customerRL->group_customer);
            })->get();

            $customersPL = Customer::where(function ($query) use($receivableList,$customerRL) {
                $query->where('customer_code', $receivableList->customer_code)
                    ->orWhere('group_customer', $customerRL->group_customer);
            })->pluck('customer_code')->all();

            $receivable = Receivable::with([
                'department'
                ])
            ->orderBy('id', 'asc')
            ->where('department_code', 'DP01')
            ->whereRaw("debt_balance > 0")
            ->whereIn('customer_code',$customersPL)
            ->whereNotIn('document_number',$documentRL)
            ->get();
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
            $privileges = Auth::user()->roles->privileges['receivable_list'];

            return view('transaction.receivable_list.receivable_list_edit', compact('receivable','receivableList','receivableListDetails','companies','customers', 'department_TDS', 'department_TDSn', 'editable','privileges'));
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to load edit form: ' . $e->getMessage());
        }
    }

    public function update(Request $request,$id)
    {
        DB::beginTransaction();  // Begin the transaction
        try {
            $general = ReceivableList::findOrFail($id);
            $general->receivable_list_date = $request->document_date;
            $general->customer_code = $request->customer_code;
            $general->periode = $request->periode;
            $general->total = 0;
            $general->company_code = $request->company_code;
            $general->department_code = $request->department_code;
            $general->updated_by = Auth::user()->username;
            $general->save();

            ReceivableListDetail::where('receivable_list_number', $general->receivable_list_number)->delete();
            $this->saveDetail($request->details,$general);

            DB::commit();
            return redirect()->route('transaction.receivable_list')->with('success', 'Tanda Terima updated successfully!');
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

            $rl = ReceivableList::findOrFail($id);
            ReceivableListDetail::where('receivable_list_number', $rl->receivable_list_number)->delete();

            $reason = $request->reason;

            DeleteLog::create([
                'document_number' => $rl->receivable_list_number,
                'document_date' => $rl->receivable_list_date,
                'delete_notes' => $reason,
                'type' => 'RL',
                'company_code' => $rl->company_code,
                'department_code' => $rl->department_code,
                'deleted_by' => Auth::user()->username,
            ]);

            $rl->delete();
            DB::commit();
            return redirect()->route('transaction.receivable_list')->with('success', 'Tanda Terima deleted successfully!');
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
        $lastReceivableList = ReceivableList::whereYear('created_at', $today->year)
            ->whereMonth('created_at', $month)
            ->where('department_code','DP01')
            ->orderBy('receivable_list_number', 'desc')
            ->first();

        // Determine the new invoice number
        if ($lastReceivableList) {
            // Extract the last number from the last invoice number
            $lastNumber = (int)substr($lastReceivableList->receivable_list_number, strrpos($lastReceivableList->receivable_list_number, '-') + 1);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            // Reset counter to 00001 if no invoices found for the current month
            $newNumber = '00001';
        }

        // Return the new invoice number in the desired format
        return "TDS/RL/{$romanMonth}/{$year}-{$newNumber}";
    }

    public function printPDF($nofaktur)
    {
        $receivableList = ReceivableList::with([
            'details',
            'customers',
        ])->where('id', $nofaktur)->firstOrFail();

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
        $imageData = file_get_contents($imagePath);
        //$cutomPaper = array(0,0,595.276,396.85);
        $pdf = \PDF::loadView('transaction.receivable_list.receivable_list_pdf', compact('receivableList','imageData','norek','namacv'))
        ->setPaper('A5', 'landscape');
        $nameFile = Str::replace("/", "", $receivableList->receivable_list_number);
        return $pdf->stream("Receivable_List_{$nameFile}.pdf");
    }
}
