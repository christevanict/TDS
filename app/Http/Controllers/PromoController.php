<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promo;

class PromoController extends Controller
{
    public function index()
    {
        $promos = Promo::all();
        return view('master.promo', compact('promos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'promo_type' => 'required|string',
            'value' => 'required|numeric',
            'item_limit' => 'nullable|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|string|in:active,inactive',
        ]);

        Promo::create($request->all());

        return redirect()->route('promo.index')->with('success', 'Promo successfully added.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'promo_type' => 'required|string',
            'value' => 'required|numeric',
            'item_limit' => 'nullable|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|string|in:active,inactive',
        ]);

        $promo = Promo::findOrFail($id);
        $promo->update($request->all());

        return redirect()->route('promo.index')->with('success', 'Promo successfully updated.');
    }

    public function destroy($id)
    {
        $promo = Promo::findOrFail($id);
        $promo->delete();

        return redirect()->route('promo.index')->with('success', 'Promo successfully deleted.');
    }
}
