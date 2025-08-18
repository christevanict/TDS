<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;
use App\Models\Zone;
use App\Models\ZoneDetail;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ZoneController extends Controller
{
    public function index()
    {
        $zone = Zone::orderBy('zone_code','asc')->get();
        $user = Users::where('username',Auth::user()->username)->with('roles')->first();
        $privileges = $user->roles->privileges['master_zone'];
        return view('master.zone',[
            'zones' => $zone,
            'privileges'=>$privileges
        ]);
    }

    public function inputForm()
    {
        $citys = City::select("city_code","city_name")->get();

        return view('master.zone.zone_input', compact('citys'));
    }

    public function insert(Request $request){
        DB::beginTransaction();  // Begin the transaction
        try {
            if(Zone::where('zone_code',$request->zone_code)->count()<1){
                $general = new Zone();
                $general->zone_code = $request->zone_code;
                $general->zone_name = $request->zone_name;
                $general->is_active = $request->is_active;
                $general->created_by = Auth::user()->username;
                $general->updated_by = Auth::user()->username;
                $general->save();

                $this->saveDetail($request->zone_details,$general);

                DB::commit();
                return redirect()->route('zone.index')->with('success', 'Zone added successfully!');
            }else{
                return redirect()->back()->with('error', 'Zone code must not be same');
            }
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }
    public function edit($id)
    {
        $zone = Zone::where('id', $id)->firstOrFail();
        $zone_details = ZoneDetail::where('zone_code', $zone->zone_code)->get();
        $citys = City::select("city_code","city_name")->get();
        return view('master.zone.zone_edit', compact('zone', 'zone_details', 'citys'));
    }

    public function inactive($id){
        DB::beginTransaction();
        try {
            $zone  = Zone::where('zone_code',$id)->first();
            $zone->is_active = $zone->is_active==1?0:1;
            $zone->save();
            DB::commit();
            return redirect()->back()->with('success', 'Zone edited successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Please try again');
        }
    }

    public function update(Request $request,$id)
    {
        DB::beginTransaction();  // Begin the transaction
        try {
            $oldZone = Zone::find($id);
            $zone = Zone::where('id',$id)->update([
                    'zone_code'=>$request->zone_code,
                    'zone_name'=>$request->zone_name,
                    'is_active'=>$request->is_active,
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
            ]);
            $newZone = Zone::find($id);
            ZoneDetail::where("zone_code",$oldZone->zone_code)->delete();
            $this->saveDetail($request->zone_details,$newZone);

            DB::commit();
            return redirect()->route('zone.index')->with('success', 'Zone updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }

    public function delete($id) {
        DB::beginTransaction();  // Begin the transaction
        try {
            $salesman = Zone::where('zone_code',$id);
            ZoneDetail::where('zone_code',$id)->delete();
            $salesman->delete();
            DB::commit();

            return redirect()->route('zone.index')->with('success', 'Zone deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }
    }

    private function saveDetail($details,$header){
        foreach($details as $detail){
            $det = new ZoneDetail();
            $det->zone_code = $header->zone_code;
            $det->city_code = $detail["city_code"];
            $det->created_by = Auth::user()->username;
            $det->updated_by = Auth::user()->username;
            $det->save();
        }
    }
}
