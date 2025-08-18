<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::orderBy('location_code', 'asc')->get();
        $companies = Company::orderBy('id', 'asc')->get();
        $departments = Department::orderBy('id', 'asc')->get();
        return view('master.location', [
            'locations' => $locations,
            'companies' => $companies,
            'departments' => $departments,
        ]);
    }

    public function insert(Request $request)
    {
        // Uncomment the validation if needed
        $request->validate([
            'location_code' => 'required',
            'location_name' => 'required',
            'company_code' => 'required',
            'department_code' => 'required',
        ]);
        DB::beginTransaction();  // Begin the transaction
        try {
            if (Location::where('location_code', $request->location_code)->count() < 1) {
                Location::create([
                    'location_code' => $request->location_code,
                    'location_name' => $request->location_name,
                    'company_code' => $request->company_code,
                    'department_code' => $request->department_code,
                    'created_by' => Auth::user()->username,
                    'updated_by' => Auth::user()->username,
                ]);
                DB::commit();
                return redirect()->back()->with('success', 'Location added successfully!');
            } else {
                return redirect()->back()->with('error', 'Location code must not be the same');
            }

        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }


    }

    public function update(Request $request, $id)
    {
        // Uncomment the validation if needed
        $request->validate([
            'location_code' => 'required',
            'location_name' => 'required',
            'company_code' => 'required',
            'department_code' => 'required',
        ]);
        DB::beginTransaction();  // Begin the transaction
        try {
            $location = Location::where('location_code', $id)->update([
                'location_code' => $request->location_code,
                'location_name' => $request->location_name,
                'company_code' => $request->company_code,
                'department_code' => $request->department_code,
                'created_by' => Auth::user()->username,
                'updated_by' => Auth::user()->username,
            ]);
                DB::commit();

                return redirect()->back()->with('success', 'Location updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }


    }

    public function delete($id)
    {
        DB::beginTransaction();  // Begin the transaction
        try {
            $location = Location::where('location_code',$id);
            $location->delete();
                DB::commit();

                return redirect()->back()->with('success', 'Location deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();  // Roll back the transaction on error
            Log::error($e->getMessage());
            return redirect()->back()->with('error', 'Failed to save');
        }

    }
}
