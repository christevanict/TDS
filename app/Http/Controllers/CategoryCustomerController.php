<?php

namespace App\Http\Controllers;

use App\Models\CategoryCustomer;
use App\Models\Company; // Import the Company model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryCustomerController extends Controller
{
    /**
     * Display a listing of the category customers and the form for creating or editing.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = CategoryCustomer::orderBy('category_code','asc')->get(); // Retrieve all category customers
        $companies = Company::all();
        return view('master.category_customer', compact('categories', 'companies'));
    }

    /**
     * Store a newly created category customer in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_code' => 'required|string|max:50|unique:category_customers,category_code',
            'category_name' => 'required|string|max:255',
            'company_code'  => 'required|string|max:50|exists:company,company_code', // Validate against companies table
        ]);

        try {
            DB::beginTransaction(); // Start transaction

            // Save category customer
            CategoryCustomer::create([
                'category_code' => $request->category_code,
                'category_name' => $request->category_name,
                'company_code'  => $request->company_code,
                'created_by'    => Auth::id(), // Save the ID of the logged-in user
                'updated_by'    => Auth::id(), // Save the ID of the logged-in user
            ]);

            DB::commit(); // Commit transaction

            return redirect()->route('master.category-customer')->with('success', 'Category Customer created successfully!');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction if error occurs
            Log::error($e->getMessage());
            return redirect()->route('master.category-customer')->with('error', 'Error creating Category Customer: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified category customer in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $category_code
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $category_code)
    {
        $categoryCustomer = CategoryCustomer::where('category_code', $category_code)->firstOrFail();

        $request->validate([
            'category_name' => 'required|string|max:255',
            'company_code'  => 'required|string|max:50|exists:company,company_code', // Validate against companies table
        ]);

        try {
            DB::beginTransaction(); // Start transaction

            // Update category customer
            $categoryCustomer->update([
                'category_name' => $request->category_name,
                'company_code'  => $request->company_code,
                'updated_by'    => Auth::id(), // Save the ID of the user making the update
            ]);

            DB::commit(); // Commit transaction

            return redirect()->route('master.category-customer')->with('success', 'Category Customer updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction if error occurs
            Log::error($e->getMessage());
            return redirect()->route('master.category-customer')->with('error', 'Error updating Category Customer: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified category customer from storage.
     *
     * @param  string  $category_code
     * @return \Illuminate\Http\Response
     */
    public function destroy($category_code)
    {
        $categoryCustomer = CategoryCustomer::where('category_code', $category_code)->firstOrFail();

        try {
            DB::beginTransaction(); // Start transaction

            // Delete category customer
            $categoryCustomer->delete();

            DB::commit(); // Commit transaction

            return redirect()->route('master.category-customer')->with('success', 'Category Customer deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction if error occurs
            Log::error($e->getMessage());
            return redirect()->route('master.category-customer')->with('error', 'Error deleting Category Customer: ' . $e->getMessage());
        }
    }
}
