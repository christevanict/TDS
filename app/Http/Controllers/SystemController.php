<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryDetail;
use App\Http\Controllers\Module;
use App\Models\ItemDetail;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SystemController extends Controller
{
    public function calculate($startDate,$endDate) {
        //prep data
        $awal = new DateTime($startDate);
        $awal->setTimezone(new DateTimeZone('Asia/Jakarta'));
        $akhir = new DateTime($endDate);
        $akhir->setTimezone(new DateTimeZone('Asia/Jakarta'));
        $diff = $awal->diff($akhir);
        $sorting = DB::table("sorting")->orderBy("number","asc")->get();

        DB::beginTransaction();
        try{
            if($diff->d >= 0){
                for($xCtr = 0;$xCtr <= $diff->d;$xCtr++){
                    $itemId = InventoryDetail::whereBetween("document_date",[$awal->format("Y-m-d"),$awal->format("Y-m-d")])->groupBy("item_id")->pluck("item_id");
                    if(count($itemId)>0){
                        $noBukti = InventoryDetail::whereBetween("document_date",[$awal->format("Y-m-d"),$awal->format("Y-m-d")])
                        ->orderBy("created_at","asc")->get();

                        foreach($itemId as $itemku){
                            $typeDone = array();
                            $noBuktiku = $noBukti->where("item_id",$itemku);
                            foreach($sorting as $sr){
                                $hLoop = $noBuktiku->filter(function ($item) use ($sr) {
                                    return strtolower(trim($item["transaction_type"], " ")) == strtolower(trim($sr->transaction_type," "));
                                });
                                if(count($hLoop)>0){
                                    $arr = $hLoop->pluck("document_number")->toArray();
                                    $fir = "";
                                    if(count($arr) > 1){
                                        if($sr->transaction_type !== "Purchase"){
                                            while(count($arr) > 1){
                                                $fir = $arr[key($arr)];
                                                unset($arr[key($arr)]);
                                                $ss = join(', ', $arr);
                                                $ini = $hLoop->where("document_number",$fir)->where("item_id",$itemku)->first();
                                                $cogs = Module::getCogs($awal->format("Y-m-d"),$itemku,$ini->company_code,$ini->department_code,"",$ss);
                                                InventoryDetail::where("id",$ini->id)->update(["cogs" => $cogs * $sr->times]);
                                            }

                                            $fir = $arr[key($arr)];
                                            $ini = $hLoop->where("document_number",$fir)->where("item_id",$itemku)->first();
                                            $cogs = Module::getCogs($awal->format("Y-m-d"),$itemku,$ini->company_code,$ini->department_code,"",$fir);
                                            InventoryDetail::where("id",$ini->id)->update(["cogs" => $cogs * $sr->times]);
                                        }else{
                                            while(count($arr) > 1){
                                                $fir = $arr[key($arr)];
                                                unset($arr[key($arr)]);
                                                $ini = $hLoop->where("document_number",$fir)->where("item_id",$itemku)->first();
                                                InventoryDetail::where("id",$ini->id)->update(["cogs" => $ini->total * $sr->times]);
                                            }

                                            $fir = $arr[key($arr)];
                                            $ini = $hLoop->where("document_number",$fir)->where("item_id",$itemku)->first();
                                            InventoryDetail::where("id",$ini->id)->update(["cogs" => $ini->total * $sr->times]);
                                        }

                                    }elseif(count($arr) == 1){
                                        if($sr->transaction_type !== "Purchase"){
                                            $fir = $arr[key($arr)];
                                            $ini = $hLoop->where("document_number",$fir)->where("item_id",$itemku)->first();
                                            $cogs = Module::getCogs($awal->format("Y-m-d"),$itemku,$ini->company_code,$ini->department_code,"",$fir);
                                            InventoryDetail::where("id",$ini->id)->update(["cogs" => $cogs * $sr->times]);
                                        }else{
                                            $fir = $arr[key($arr)];
                                            $ini = $hLoop->where("document_number",$fir)->where("item_id",$itemku)->first();
                                            InventoryDetail::where("id",$ini->id)->update(["cogs" => $ini->total * $sr->times]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $awal = $awal->modify('+1 day');
                }
            }
            DB::commit();
            return "Success";
        }catch(\Exception $e){
            DB::rollBack();
            Log::error($e->getMessage());
            return $e->getMessage();
        }
    }

    public function hpp(Request $request) {
        $awal = new DateTime($request->document_date);
        $awal->setTimezone(new DateTimeZone('Asia/Jakarta'));
        $ret = array();
        foreach($request->details as $dr){
            $hasil = Module::getCogs($awal->format("Y-m-d"),$dr["item_id"],$request->company_code,$request->department_code,"","");
            $dr['price'] = str_replace(',', '', $dr['price']);
            $dr['disc_percent'] = str_replace(',', '', $dr['disc_percent']??0);
            $dr['disc_nominal'] = str_replace(',', '', $dr['disc_nominal']??0);
            $dr['nominal'] = $dr['qty']*$dr['price']-($dr['disc_percent']/100*$dr['qty']*$dr['price'])-$dr['disc_nominal'];
            if(($hasil * 1) > $dr["nominal"]){
                array_push($ret,["item_name" => $dr["item_name"]]);
            }
        }
        return $ret;
    }

    public function getStockByDate(Request $request) {
        $awal = new DateTime($request->document_date);
        $awal->setTimezone(new DateTimeZone('Asia/Jakarta'));
        $ret = array();
        $gdAll = DB::table("warehouse")->get();

        foreach ($request->details as $dr) {
            // Check unit status
            $unitCheck = ItemDetail::where('item_code', $dr['item_id'])
                ->where('unit_conversion', $dr['unit'])
                ->where('status', true)
                ->first();

            if (!$unitCheck) {
                array_push($ret, [
                    "item_name" => $dr["item_name"],
                    "error_type" => "invalid_unit",
                    "message" => "Unit tidak aktif, harap diganti"
                ]);
                continue; // Skip stock check if unit is invalid
            }

            // Check stock availability
            $gd = $gdAll->where("warehouse_code", $dr["warehouse_code"])->first();
            $hasil = Module::getStockByDate(
                $dr["item_id"],
                $dr["unit"],
                $dr["base_qty"],
                $gd->id,
                $awal->format("Y-m-d")
            );

            if (($hasil * 1) < ($dr["qty"] * $dr["base_qty"])) {
                array_push($ret, [
                    "item_name" => $dr["item_name"],
                    "error_type" => "insufficient_stock",
                    "message" => "Stok tidak mencukupi, Stok tersedia: " . ($hasil / $dr["base_qty"])
                ]);
            }
        }

        return $ret;
    }

    public function getStockByDatePerItem(Request $request) {
        $awal = new DateTime($request->document_date);
        $awal->setTimezone(new DateTimeZone('Asia/Jakarta'));
        $ret = array();
        $gdAll = DB::table("warehouse")->get();

        foreach(json_decode($request->details,true) as $dr){
            $gd = $gdAll->where("warehouse_code",$dr["warehouse_code"])->first();
            $hasil = Module::getStockByDate($dr["item_id"],$dr["unit"],$dr["base_qty"],$gd->id,$awal->format("Y-m-d"));
            array_push($ret,[
                "item_id" => $dr["item_id"],
                "stock"=>($hasil / $dr["base_qty"])
            ]);
        }
        return $ret;
    }

    public function checkUnit(Request $request)
    {
        foreach($request->details as $item)
        {
            $ret =  array();
            $exist = ItemDetail::where('department_code','DP01')->where('item_code',$item['item_id'])->where('unit_conversion',$item['unit'])->where('status',true)->first();
            if(!$exist){
                array_push($ret,[
                    "item_name" => $item['item_name'],
                ]);

            }
        }
        return $ret;
    }

    public function checkDateToPeriode(Request $request){
        $date = $request->input('date');
        if(new DateTime($date) < new DateTime('2024-10-01')){
            return false;
        }
        $periode = DB::table("periode")->whereRaw('? BETWEEN periode_start AND periode_end', [$date])->first();
        if($periode&&$periode->periode_active=='closed'){
            return false;
        }
        return true;
    }
}

