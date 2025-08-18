<?php

namespace App\Http\Controllers;

use App\Models\HoldOrder;
use App\Models\HoldOrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HoldOrderController extends Controller
{
    /**
     * Show all held orders.
     */
    public function index()
    {
        $holdOrders = HoldOrder::with('details')->get();

        return response()->json([
            'success' => true,
            'data' => $holdOrders,
        ], 200);
    }

    /**
     * Store a new hold order.
     */
    public function store(Request $request)
    {
        // Validate the incoming data
        $validatedData = $request->validate([
            'reference_id' => 'required|string|unique:hold_orders,reference_id',
            'items' => 'required|array|min:1',
            'items.*.item' => 'required|string|max:255',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.total' => 'required|numeric|min:0',
        ]);

        try {
            // Start a database transaction
            DB::beginTransaction();

            // Initialize total amount
            $totalAmount = 0;

            // Create the hold order
            $holdOrder = HoldOrder::create([
                'reference_id' => $validatedData['reference_id'],
                'total_amount' => $totalAmount, // Placeholder for total amount
            ]);

            // Loop through items and insert into hold_orders_detail
            foreach ($validatedData['items'] as $item) {
                // Ensure the price is treated as a raw numeric value (no changes)
                $price = $this->removeCurrencyFormatting($item['price']);
                $total = $price * $item['quantity']; // Calculate total for the item
                $totalAmount += $total; // Add to the total amount

                // Store the item in the order details table
                HoldOrderDetail::create([
                    'hold_order_id' => $holdOrder->id,
                    'item' => $item['item'],  // Store item name
                    'price' => $price, // Store price (raw number)
                    'quantity' => $item['quantity'],
                    'total' => $total, // Store the total for the item
                ]);
            }

            // Update the total amount in the hold order
            $holdOrder->total_amount = $totalAmount;
            $holdOrder->save();

            // Commit the transaction
            DB::commit();

            // Return the response
            return response()->json([
                'success' => true,
                'message' => 'Hold order created successfully.',
                'data' => $holdOrder->load('details'),
            ], 201);
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();
            Log::error('Error occurred while creating hold order.', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create hold order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper function to handle parsing currency format to raw number.
     */
    private function removeCurrencyFormatting($value)
    {
        // Remove non-numeric characters (such as currency symbols, commas)
        $rawValue = preg_replace('/[^\d.-]/', '', $value);
        return (float)$rawValue; // Return as float for precision
    }

    /**
     * Retrieve a specific hold order by ID.
     */
    public function show($id)
    {
        $holdOrder = HoldOrder::with('details')->find($id);

        if (!$holdOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Hold order not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $holdOrder,
        ], 200);
    }

    /**
     * Update a hold order and its details.
     */
    public function update(Request $request, $id)
    {
        $holdOrder = HoldOrder::find($id);

        if (!$holdOrder) {
            return response()->json([
                'success' => false,
                'message' => 'Hold order not found.',
            ], 404);
        }

        // Validate the incoming data
        $validatedData = $request->validate([
            'reference_id' => 'required|string|max:255|unique:hold_orders,reference_id,' . $id,
            'items' => 'required|array|min:1',
            'items.*.item' => 'required|string|max:255',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.total' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Update the hold order reference_id
            $holdOrder->reference_id = $validatedData['reference_id'];

            // Remove existing details
            HoldOrderDetail::where('hold_order_id', $holdOrder->id)->delete();

            // Recalculate total amount
            $totalAmount = 0;

            // Add the updated items
            foreach ($validatedData['items'] as $item) {
                $price = $this->removeCurrencyFormatting($item['price']); // Handle price formatting
                $total = $price * $item['quantity']; // Calculate total for the item
                $totalAmount += $total; // Add to the total amount

                // Store the item in the order details table
                HoldOrderDetail::create([
                    'hold_order_id' => $holdOrder->id,
                    'item' => $item['item'],  // Store item name
                    'price' => $price, // Store price (raw number)
                    'quantity' => $item['quantity'],
                    'total' => $total, // Store the total for the item
                ]);
            }

            // Update total_amount and save the updated hold order
            $holdOrder->total_amount = $totalAmount;
            $holdOrder->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hold order updated successfully.',
                'data' => $holdOrder->load('details'),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update hold order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a hold order and its details.
     */
    public function destroy($id)
    {
        try {
            // Find the HoldOrder by ID
            $holdOrder = HoldOrder::find($id); // Use find() instead of findOrFail()

            // Check if the hold order exists
            if (!$holdOrder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hold order not found.',
                ], 404);
            }

            // Proceed with deletion
            $holdOrder->details()->delete(); // Delete associated details
            $holdOrder->delete(); // Delete the hold order itself

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Hold order deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            // Catch unexpected errors
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete hold order: ' . $e->getMessage(),
            ], 500);
        }
    }
}
