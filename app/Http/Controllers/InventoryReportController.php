<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Warehouse;
use DB;
class InventoryReportController extends Controller
{
    public function index(){
        $warehouses = Warehouse::all();
        return view('transaction.report.inventory_report',compact('warehouses'));
    }

    public function search(Request $req){
        // $hasil = DB::select("select y.item_name,w.warehouse_name,x.nomor,x.document_number, x.transaction_type,
        // case when c.customer_name is null then s.supplier_name else c.customer_name end as from_to,
        // x.item_id, x.warehouse_id, x.document_date,
        // (x.beginning_qty/ max(z.conversion)) beginning_qty, x.beginning_price,
        // x.beginning_cogs,
        // (x.qty_in / max(z.conversion)) qty_in,x.price_in, x.cogs_in,
        // (x.qty_out / max(z.conversion)) qty_out,x.price_out, x.cogs_out,
        // (x.total_qty / max(z.conversion)) total_qty,x.total_price,
        // x.total_cogs,
        // x.urutan,x.created_at,x.department_code
        // from func_inventory_report(cast(? as date), cast(? as date)) x
        // join item y on x.item_id = y.item_code
        // join item_details z on z.item_code = x.item_id
        // left join warehouse w on w.id = x.warehouse_id
        // left join customer c on c.customer_code = x.from_to
        // left join supplier s on s.supplier_code = x.from_to
        // where x.department_code='DP01' and x.warehouse_id = ?
        // group by y.item_name,w.warehouse_name,x.nomor,x.document_number, x.transaction_type,x.item_id, x.warehouse_id, x.document_date,
        // x.beginning_qty, x.beginning_price,
        // x.beginning_cogs,
        // x.qty_in,x.price_in, x.cogs_in,
        // x.qty_out,x.price_out, x.cogs_out,
        // x.total_qty,x.total_price,
        // x.total_cogs,
        // x.urutan,x.created_at,x.department_code,c.customer_name,s.supplier_name
        // order by x.item_id,x.nomor",
        // [$req->date_from,$req->date_to,$req->warehouse]);

        $hasil = DB::select("select y.item_name,w.warehouse_name,x.document_number, x.transaction_type,
        case when c.customer_name is null then s.supplier_name else c.customer_name end as from_to,
        x.item_id, x.warehouse_id, x.document_date,
        (x.beginning_qty/ max(z.conversion)) beginning_qty, x.beginning_price,
        x.beginning_cogs,
        (x.qty_in / max(z.conversion)) qty_in,x.price_in, x.cogs_in,
        (x.qty_out / max(z.conversion)) qty_out,x.price_out, x.cogs_out,
        (x.total_qty / max(z.conversion)) total_qty,x.total_price,
        x.total_cogs,
        x.urutan,x.created_at,x.department_code
        from func_inventory_report(cast(? as date), cast(? as date)) x
        join item y on x.item_id = y.item_code
        join item_details z on z.item_code = x.item_id
        left join warehouse w on w.id = x.warehouse_id
        left join customer c on c.customer_code = x.from_to
        left join supplier s on s.supplier_code = x.from_to
        where x.department_code='DP01' and y.department_code = 'DP01' and z.department_code='DP01'
        group by y.item_name,w.warehouse_name,x.document_number, x.transaction_type,x.item_id, x.warehouse_id, x.document_date,
        x.beginning_qty, x.beginning_price,
        x.beginning_cogs,
        x.qty_in,x.price_in, x.cogs_in,
        x.qty_out,x.price_out, x.cogs_out,
        x.total_qty,x.total_price,
        x.total_cogs,
        x.urutan,x.created_at,x.department_code,c.customer_name,s.supplier_name
        order by x.item_id",
        [$req->date_from,$req->date_to]);
        foreach($hasil as $h){
            $h->beginning_qty = round($h->beginning_qty,4);
            $h->beginning_price = round($h->beginning_price,4);
            $h->beginning_cogs = round($h->beginning_cogs,4);
            $h->qty_in = round($h->qty_in,4);
            $h->price_in = round($h->price_in,4);
            $h->cogs_in = round($h->cogs_in,4);
            $h->qty_out = round($h->qty_out,4);
            $h->price_out = round($h->price_out,4);
            $h->cogs_out = round($h->cogs_out,4);
            $h->total_qty = round($h->total_qty,4);
            $h->total_price = round($h->total_price,4);
            $h->total_cogs = round($h->total_cogs,4);
        }
        return $hasil;
    }
}
