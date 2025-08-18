<?php

namespace App\Http\Controllers;

use App\Models\DeleteLog;
use Illuminate\Http\Request;
use DB;
class SystemLogController extends Controller
{
    public function index(){
        //delete log
        $deleteLogs = DeleteLog::where('department_code','DP01')->orderBy('id','desc')->get();
        return view('transaction.report.delete_log',compact('deleteLogs'));
    }

    public function cancel_log(){
        //cancel log
        $so = DB::table("sales_order as x")->select("x.cancel_notes","x.sales_order_number as document_number","y.customer_name as name","x.document_date",DB::raw("'Sales Order' as Type"))
        ->join("customer as y","x.customer_code","=","y.customer_code")
        ->whereNotNull("x.cancel_notes");
        $gr = DB::table("good_receipt as x")->select("x.cancel_notes","x.good_receipt_number as document_number","y.supplier_name as name","x.document_date",DB::raw("'Good Receipt' as Type"))
        ->join("supplier as y","x.supplier_code","=","y.supplier_code")
        ->whereNotNull("x.cancel_notes");
        $po = DB::table("purchase_order as x")->select("cancel_notes","purchase_order_number as document_number","y.supplier_name as name","x.document_date",DB::raw("'Purchase Order' as Type"))
        ->join("supplier as y","x.supplier_code","=","y.supplier_code")
        ->whereNotNull("x.cancel_notes")
        ->union($so)->union($gr)->get();
        return view('transaction.report.cancel_log',compact('po'));
    }
}
