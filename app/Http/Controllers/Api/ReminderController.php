<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reminder;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReminderController extends Controller
{
    public function index(Request $request)
    {
        $vehicleIds = Auth::user()->vehicles()->pluck('id');

        $query = Reminder::whereIn('vehicle_id', $vehicleIds)
            ->with('vehicle:id,brand,model')
            ->orderBy('is_completed')
            ->orderBy('due_date');

        if ($request->has('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'type' => 'required|string',
            'title' => 'required|string|max:255',
            'due_date' => 'nullable|date',
            'due_odometer' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);

        if ($vehicle->user_id !== Auth::id()) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $reminder = Reminder::create($validated);

        return response()->json($reminder->load('vehicle:id,brand,model'), 201);
    }

    public function update(Request $request, Reminder $reminder)
    {
        if ($reminder->vehicle->user_id !== Auth::id()) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $validated = $request->validate([
            'type' => 'sometimes|string',
            'title' => 'sometimes|string|max:255',
            'due_date' => 'nullable|date',
            'due_odometer' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:1000',
            'is_completed' => 'sometimes|boolean',
        ]);

        $reminder->update($validated);

        return $reminder->load('vehicle:id,brand,model');
    }

    public function destroy(Reminder $reminder)
    {
        if ($reminder->vehicle->user_id !== Auth::id()) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $reminder->delete();

        return response()->noContent();
    }
}
