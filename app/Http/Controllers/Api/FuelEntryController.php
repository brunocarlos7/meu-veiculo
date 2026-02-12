<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FuelEntry;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FuelEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate(['vehicle_id' => 'required|exists:vehicles,id']);

        $vehicle = Vehicle::findOrFail($request->vehicle_id);

        if ($vehicle->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $vehicle->fuelEntries()->orderBy('date', 'desc')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'date' => 'required|date',
            'station' => 'nullable|string',
            'fuel_type' => 'required|string',
            'price_per_liter' => 'required|numeric',
            'liters' => 'required|numeric',
            'total_value' => 'required|numeric',
            'odometer' => 'required|integer',
            'is_full_tank' => 'boolean',
        ]);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);

        if ($vehicle->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $fuelEntry = FuelEntry::create($validated);

        return response()->json($fuelEntry, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(FuelEntry $fuelEntry)
    {
        if ($fuelEntry->vehicle->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return $fuelEntry;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FuelEntry $fuelEntry)
    {
        if ($fuelEntry->vehicle->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'date' => 'sometimes|date',
            'station' => 'nullable|string',
            'fuel_type' => 'sometimes|string',
            'price_per_liter' => 'sometimes|numeric',
            'liters' => 'sometimes|numeric',
            'total_value' => 'sometimes|numeric',
            'odometer' => 'sometimes|integer',
            'is_full_tank' => 'boolean',
        ]);

        $fuelEntry->update($validated);

        return $fuelEntry;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FuelEntry $fuelEntry)
    {
        if ($fuelEntry->vehicle->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $fuelEntry->delete();

        return response()->noContent();
    }
}
