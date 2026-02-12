<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['vehicle_id' => 'required|exists:vehicles,id']);

        $vehicle = Vehicle::findOrFail($request->vehicle_id);

        if ($vehicle->user_id !== Auth::id()) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        return $vehicle->maintenances()->orderBy('date', 'desc')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'type'       => 'required|string',
            'date'       => 'required|date',
            'cost'       => 'required|numeric|min:0',
            'odometer'   => 'required|integer|min:0',
            'notes'      => 'nullable|string|max:1000',
        ]);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);

        if ($vehicle->user_id !== Auth::id()) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $maintenance = Maintenance::create($validated);

        return response()->json($maintenance, 201);
    }

    public function show(Maintenance $maintenance)
    {
        if ($maintenance->vehicle->user_id !== Auth::id()) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }
        return $maintenance;
    }

    public function update(Request $request, Maintenance $maintenance)
    {
        if ($maintenance->vehicle->user_id !== Auth::id()) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $validated = $request->validate([
            'type'     => 'sometimes|string',
            'date'     => 'sometimes|date',
            'cost'     => 'sometimes|numeric|min:0',
            'odometer' => 'sometimes|integer|min:0',
            'notes'    => 'nullable|string|max:1000',
        ]);

        $maintenance->update($validated);

        return $maintenance;
    }

    public function destroy(Maintenance $maintenance)
    {
        if ($maintenance->vehicle->user_id !== Auth::id()) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $maintenance->delete();

        return response()->noContent();
    }
}
