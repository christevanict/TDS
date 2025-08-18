<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Currency;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CurrencyController extends Controller
{
    public function index()
    {
        $currencies = Currency::orderBy('currency_code','asc')->get();
        return view('master.currency',[
            'currencies' => $currencies
        ]);
    }

    public function insert(Request $request){
        DB::beginTransaction();  // Begin the transaction
        try {
            $request->validate([
                'currency_code' => 'required',
                'currency_name' => 'required',
            ]);
            if(Currency::where('currency_code',$request->currency_code)->count()<1){
                Currency::create([
                    'currency_code'=>$request->currency_code,
                    'currency_name'=>$request->currency_name,
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
                ]);
                DB::commit();
                return redirect()->back()->with('success', 'Currency added successfully!');
            }else{
                return redirect()->route('type-companys.index')->with('error', 'Currency code  must not be same');
            }
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }

    public function update(Request $request,$currency_code)
    {
        DB::beginTransaction();  // Begin the transaction
        try {
            $request->validate([
                'currency_code' => 'required',
                'currency_name' => 'required',
            ]);
            $currencies = Currency::where('currency_code',$currency_code)->update([
            'currency_code'=>$request->currency_code,
            'currency_name'=>$request->currency_name,
            'updated_by'=>Auth::user()->username]);

                DB::commit();

                return redirect()->back()->with('success', 'Currency updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }

    public function delete($id) {
        DB::beginTransaction();  // Begin the transaction
        try {
            $currencies = Currency::where('currency_code',$id);
            $currencies->delete();
            DB::commit();

            return redirect()->back()->with('success', 'Currency deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }
}
