<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class Module extends Controller
{
    public static function getCogs($date,$itemId,$company,$department,$warehouse = "",$skipNumber = "") {
		// $hasil = DB::select('select COALESCE(case when sum(qty) <= 0 then 0 else sum(cogs) / sum(qty) end,0) as "cogs" from (
		// 	select
		// 	COALESCE(case when cogs > 0 and (qty_actual * base_quantity) < 0 then 0 else (qty_actual * base_quantity) end,0) qty,
		// 	COALESCE(case when cogs > 0 and (qty_actual * base_quantity) < 0 then 0 else cogs end,0) cogs
		// 	from inventory_detail
		// 	where item_id = ? and company_code = ? and department_code = ? and
		// 	document_date <= ?
		// 	) x',[$itemId,$company,$department,$date]);

		// if(count($hasil)>0){
		// 	return $hasil[0]->cogs;
		// }else{
		// 	return 0;
		// }

//dd([$date,$company,$department,$itemId,$date,$date,$company,$department,$itemId]);
	if ($skipNumber == ""){
		$hasil = DB::select('
select case when x.total_qty <> 0 then x.total_cogs / x.total_qty else 0 end as "cogs",
	ROW_NUMBER() over(ORDER BY item_id, urutan, document_date,created_at, document_number, warehouse_id)
from (
	select document_number, item_id, warehouse_id, document_date,
		sum(beginning_qty) beginning_qty, sum(beginning_cogs) beginning_cogs, sum(qty_in) qty_in, sum(cogs_in) cogs_in, sum(qty_out) qty_out, sum(cogs_out) cogs_out,
		sum(sum(beginning_qty) + sum(qty_in) - sum(qty_out)) over (PARTITION BY item_id, warehouse_id order by item_id, urutan, warehouse_id, document_date, document_number, warehouse_id, created_at) total_qty,
		sum(sum(beginning_cogs) + sum(cogs_in) - sum(cogs_out)) over (PARTITION BY item_id, warehouse_id order by item_id, urutan, warehouse_id, document_date, document_number, warehouse_id, created_at) total_cogs
		,urutan,created_at
	from (
		select cast(null as varchar(50)) document_number, item_id,
			warehouse_id,
			cast(null as date) document_date,
			sum(qty_actual * base_quantity) beginning_qty, sum(cogs) beginning_cogs, cast(0 as decimal(19,4)) qty_in, cast(0 as decimal(19,4)) cogs_in,
			cast(0 as decimal(19,4)) qty_out, cast(0 as decimal(19,4)) cogs_out,0 urutan,max(created_at) created_at
		from inventory_detail
		where document_date < ? and company_code = ? and department_code = ? and item_id = ?
		group by item_id, warehouse_id
		-- beginning balance || sawal
		union all
		select document_number, item_id,
			warehouse_id,
			document_date,
			cast(0 as decimal(18,2)) beginning_qty, cast(0 as decimal(18,2)) beginning_cogs,
			sum(case when qty_actual > 0 then qty_actual * base_quantity else 0 end) qty_in, sum(case when cogs > 0 then cogs else 0 end) cogs_in,
			sum(case when qty_actual < 0 then (qty_actual * base_quantity)*-1 else 0 end) qty_out, sum(case when cogs < 0 then cogs*-1 else 0 end) cogs_out,
			case when sum(qty_actual) > 0 then
				case lower(max(transaction_type)) when \'system\' then 1 when \'purchase\' then 2 when \'production\' then 3 else 4 end
			else 5 end
			urutan,max(created_at) created_at
		from inventory_detail
		where document_date between ? and ? and company_code = ? and department_code = ? and item_id = ? and warehouse_id = ?
		group by item_id,
			warehouse_id,
			document_date,
			document_number
	) x group by item_id, warehouse_id, document_date, document_number, urutan,created_at
) x order by ROW_NUMBER() over(ORDER BY item_id, urutan, document_date, created_at, document_number, warehouse_id) desc
limit 1',[$date,$company,$department,$itemId,$date,$date,$company,$department,$itemId,$warehouse]);
		if(count($hasil)>0){
			return $hasil[0]->cogs;
		}else{
			return 0;
		}
	}else{
		$hasil = DB::select('
select case when x.total_qty <> 0 then x.total_cogs / x.total_qty else 0 end as "cogs",
	ROW_NUMBER() over(ORDER BY item_id, urutan, document_date,created_at, document_number, warehouse_id)
from (
	select document_number, item_id, warehouse_id, document_date,
		sum(beginning_qty) beginning_qty, sum(beginning_cogs) beginning_cogs, sum(qty_in) qty_in, sum(cogs_in) cogs_in, sum(qty_out) qty_out, sum(cogs_out) cogs_out,
		sum(sum(beginning_qty) + sum(qty_in) - sum(qty_out)) over (PARTITION BY item_id, warehouse_id order by item_id, urutan, warehouse_id, document_date, document_number, warehouse_id, created_at) total_qty,
		sum(sum(beginning_cogs) + sum(cogs_in) - sum(cogs_out)) over (PARTITION BY item_id, warehouse_id order by item_id, urutan, warehouse_id, document_date, document_number, warehouse_id, created_at) total_cogs
		,urutan,created_at
	from (
		select cast(null as varchar(50)) document_number, item_id,
			warehouse_id,
			cast(null as date) document_date,
			sum(qty_actual * base_quantity) beginning_qty, sum(cogs) beginning_cogs, cast(0 as decimal(19,4)) qty_in, cast(0 as decimal(19,4)) cogs_in,
			cast(0 as decimal(19,4)) qty_out, cast(0 as decimal(19,4)) cogs_out,0 urutan,max(created_at) created_at
		from inventory_detail
		where document_date < ? and company_code = ? and department_code = ? and item_id = ?
		group by item_id, warehouse_id
		-- beginning balance || sawal
		union all
		select document_number, item_id,
			warehouse_id,
			document_date,
			cast(0 as decimal(18,2)) beginning_qty, cast(0 as decimal(18,2)) beginning_cogs,
			sum(case when qty_actual > 0 then qty_actual * base_quantity else 0 end) qty_in, sum(case when cogs > 0 then cogs else 0 end) cogs_in,
			sum(case when qty_actual < 0 then (qty_actual * base_quantity)*-1 else 0 end) qty_out, sum(case when cogs < 0 then cogs*-1 else 0 end) cogs_out,
			case when sum(qty_actual) > 0 then
				case lower(max(transaction_type)) when \'system\' then 1 when \'purchase\' then 2 when \'production\' then 3 else 4 end
			else 5 end
			urutan,max(created_at) created_at
		from inventory_detail
		where document_date between ? and ? and company_code = ? and department_code = ? and item_id = ? and warehouse_id = ? and document_number not in (?)
		group by item_id,
			warehouse_id,
			document_date,
			document_number
	) x group by item_id, warehouse_id, document_date, document_number, urutan,created_at
) x order by ROW_NUMBER() over(ORDER BY item_id, urutan, document_date, created_at, document_number, warehouse_id) desc
limit 1',[$date,$company,$department,$itemId,$date,$date,$company,$department,$itemId,$warehouse,$skipNumber]);
		if(count($hasil)>0){
			return $hasil[0]->cogs;
		}else{
			return 0;
		}
	}
    }

	public static function getStockByDate($itemId,$unitId,$convertion,$warehouse,$date) {
		$hasil = DB::table("inventory_detail")->where("item_id",$itemId)
		->where("document_date","<=",$date)->where("warehouse_id",$warehouse)->where('department_code','DP01')
		->select(DB::raw("coalesce(sum(qty_actual * base_quantity),0) qty_actual"))->first();
		return $hasil->qty_actual;
	}
}
