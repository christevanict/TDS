<?php

namespace App\Http\Controllers;

use App\Models\CogsMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CogsMethodController extends Controller
{
    /**
     * Display a listing of the COGS methods and the form for creating or editing.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cogsMethods = CogsMethod::all(); // Retrieve all COGS methods
        return view('master.cogs_method', compact('cogsMethods'));
    }

    /**
     * Store a newly created COGS method in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'cogs_method' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction(); // Start transaction
            if (CogsMethod::where('cogs_method', $request->cogs_method)->count() < 1) {
                CogsMethod::create([
                    'cogs_method' => $request->cogs_method,
                    'created_by' => Auth::id(), // Save the ID of the logged-in user
                    'updated_by' => Auth::id(), // Save the ID of the logged-in user
                ]);
                DB::commit();
                return redirect()->back()->with('success', 'COGS Method added successfully!');
            } else {
                return redirect()->back()->with('error', 'COGS Method must not be the same');
            }
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction if error occurs
            Log::error($e->getMessage());
            return redirect()->route('master.cogs-method')->with('error', 'Error creating COGS Method: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified COGS method in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $cogsMethod = CogsMethod::findOrFail($id);

        $request->validate([
            'cogs_method' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction(); // Start transaction

            // Update COGS method
            $cogsMethod->update([
                'cogs_method' => $request->cogs_method,
                'updated_by' => Auth::id(), // Save the ID of the user making the update
            ]);

            DB::commit(); // Commit transaction

            return redirect()->route('master.cogs-method')->with('success', 'COGS Method updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction if error occurs
            Log::error($e->getMessage());
            return redirect()->route('master.cogs-method')->with('error', 'Error updating COGS Method: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified COGS method from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cogsMethod = CogsMethod::findOrFail($id);

        try {
            DB::beginTransaction(); // Start transaction

            // Delete COGS method
            $cogsMethod->delete();

            DB::commit(); // Commit transaction

            return redirect()->route('master.cogs-method')->with('success', 'COGS Method deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction if error occurs
            Log::error($e->getMessage());
            return redirect()->route('master.cogs-method')->with('error', 'Error deleting COGS Method: ' . $e->getMessage());
        }
    }
}
