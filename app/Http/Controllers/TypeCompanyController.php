<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TypeCompany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TypeCompanyController extends Controller
{
    public function index()
    {
        $typeCompany = TypeCompany::orderBy('id','asc')->get();
        return view('master.type-company',[
            'typeCompany' => $typeCompany
        ]);
    }

    public function insert(Request $request){
        DB::beginTransaction();  // Begin the transaction
        try {
            $request->validate([
                'type_company' => 'required',
            ]);

            if(TypeCompany::where('type_company',$request->type_company)->count()<1){
                TypeCompany::create([
                    'type_company'=>$request->type_company,
                    'created_by'=>Auth::user()->username,
                    'updated_by'=>Auth::user()->username,
                ]);



                DB::commit();  // Commit the transaction
                return redirect()->back()->with('success', 'Type Company added successfully!');
            }else{
                return redirect()->back()->with('error', 'Type Company code  must not be same');
            }

        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }

    public function update(Request $request,$id)
    {
        DB::beginTransaction();  // Begin the transaction
        try {
            $request->validate([
                'type_company' => 'required',
            ]);

            TypeCompany::where('id',$id)->update(['type_company'=>$request->type_company]);

            DB::commit();  // Commit the transaction
            return redirect()->back()->with('success', 'Type Company updated successfully!');


        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }

    public function delete($id) {
        DB::beginTransaction();  // Begin the transaction
        try {
            $typeCompany = TypeCompany::findOrFail($id);
            $typeCompany->delete();

            DB::commit();  // Commit the transaction
            return redirect()->back()->with('success', 'Type Company deleted successfully!');


        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            // dd($e);
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }
}
