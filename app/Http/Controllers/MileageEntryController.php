<?php

namespace App\Http\Controllers;

use App\Models\MileageEntry;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class MileageEntryController extends Controller
{
    public function index(Vehicle $vehicle)
    {
        return $vehicle->mileageEntries()->orderBy('date', 'desc')->get();
    }

    public function store(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'odometer' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $entry = $vehicle->mileageEntries()->create($validated);

        return response()->json($entry, 201);
    }

    public function update(Request $request, MileageEntry $mileageEntry)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'odometer' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $mileageEntry->update($validated);

        return response()->json($mileageEntry);
    }

    public function destroy(MileageEntry $mileageEntry)
    {
        $mileageEntry->delete();
        return response()->noContent();
    }
}
