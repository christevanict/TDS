<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Warehouse;
use DB;
class RecapInventoryReportController extends Controller
{
    public function index(){
        $warehouses = Warehouse::all();
        return view('transaction.report.recap_inventory_report',compact('warehouses'));
    }

    public function search(Request $req){
        // $hasil = DB::select("select i.item_code,i.item_name,y.warehouse_name,
        // case when COALESCE(sum(qty_actual * base_quantity),0) = 0 then 0 else COALESCE(sum(qty_actual * base_quantity),0)/max(z.conversion) end as total
        // from inventory_detail x
        // join item i on i.item_code = x.item_id
        // join item_details z on z.item_code = x.item_id
        // left join warehouse y on y.id = x.warehouse_id
        // where x.department_code='DP01' and x.warehouse_id = ? and x.document_date <= ?
        // group by i.item_code,i.item_name,y.warehouse_name",
        // [$req->warehouse,$req->date_from]);

        $hasil = DB::select("SELECT
                i.item_code,
                i.item_name,
                y.warehouse_name,
                CASE
                    WHEN COALESCE(SUM(qty_actual * base_quantity), 0) = 0
                    THEN 0
                    ELSE COALESCE(SUM(qty_actual * base_quantity), 0) / z.conversion
                END AS total,
                z.unit_conversion AS unit
            FROM inventory_detail x
            JOIN item i ON i.item_code = x.item_id
            JOIN (
                SELECT z1.item_code, z1.conversion, z1.unit_conversion
                FROM item_details z1
                WHERE z1.id = (
                    SELECT MAX(z2.id)
                    FROM item_details z2
                    WHERE z2.item_code = z1.item_code
                    AND z2.department_code = 'DP01'
                )
            ) z ON z.item_code = x.item_id
            LEFT JOIN warehouse y ON y.id = x.warehouse_id
            WHERE x.department_code = 'DP01' and i.department_code = 'DP01' and x.document_date <= ?
            GROUP BY i.item_code, i.item_name, y.warehouse_name, z.unit_conversion, z.conversion",
        [$req->date_from]);

        foreach($hasil as $h){
            $h->total = round($h->total,4);
        }

        return $hasil;
    }
}
