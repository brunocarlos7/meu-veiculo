<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['vehicle_id' => 'required|exists:vehicles,id']);

        $vehicle = Vehicle::findOrFail($request->vehicle_id);

        if ($vehicle->user_id !== Auth::id()) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        return $vehicle->expenses()->orderBy('date', 'desc')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'type'       => 'required|string',
            'date'       => 'required|date',
            'cost'       => 'required|numeric|min:0',
            'notes'      => 'nullable|string|max:1000',
        ]);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);

        if ($vehicle->user_id !== Auth::id()) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $expense = Expense::create($validated);

        return response()->json($expense, 201);
    }

    public function show(Expense $expense)
    {
        if ($expense->vehicle->user_id !== Auth::id()) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }
        return $expense;
    }

    public function update(Request $request, Expense $expense)
    {
        if ($expense->vehicle->user_id !== Auth::id()) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $validated = $request->validate([
            'type'  => 'sometimes|string',
            'date'  => 'sometimes|date',
            'cost'  => 'sometimes|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $expense->update($validated);

        return $expense;
    }

    public function destroy(Expense $expense)
    {
        if ($expense->vehicle->user_id !== Auth::id()) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $expense->delete();

        return response()->noContent();
    }
}
