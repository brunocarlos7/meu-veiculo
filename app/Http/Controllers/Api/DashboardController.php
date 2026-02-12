<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        $vehicles = $user->vehicles;

        $totalVehicles = $vehicles->count();

        // Total costs across all categories
        $totalFuelCost = 0;
        $totalMaintenanceCost = 0;
        $totalExpenseCost = 0;
        $currentMonthFuelCost = 0;
        $currentMonthTotal = 0;

        // Average consumption (using full-tank entries only, as per prompt spec)
        $totalKm = 0;
        $totalLiters = 0;

        foreach ($vehicles as $vehicle) {
            // Fuel costs
            $totalFuelCost += $vehicle->fuelEntries()->sum('total_value');
            $monthFuel = $vehicle->fuelEntries()
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->sum('total_value');
            $currentMonthFuelCost += $monthFuel;

            // Maintenance costs
            $totalMaintenanceCost += $vehicle->maintenances()->sum('cost');
            $currentMonthTotal += $vehicle->maintenances()
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->sum('cost');

            // Expense costs
            $totalExpenseCost += $vehicle->expenses()->sum('cost');
            $currentMonthTotal += $vehicle->expenses()
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->sum('cost');

            // Consumption: only between consecutive full-tank entries
            $entries = $vehicle->fuelEntries()
                ->where('is_full_tank', true)
                ->orderBy('odometer', 'asc')
                ->get();

            for ($i = 1; $i < $entries->count(); $i++) {
                $kmDriven = $entries[$i]->odometer - $entries[$i - 1]->odometer;
                $litersUsed = $entries[$i]->liters;

                if ($kmDriven > 0 && $litersUsed > 0) {
                    $totalKm += $kmDriven;
                    $totalLiters += $litersUsed;
                }
            }
        }

        $currentMonthTotal += $currentMonthFuelCost;
        $avgConsumption = $totalLiters > 0 ? round($totalKm / $totalLiters, 2) : 0;
        $totalCost = $totalFuelCost + $totalMaintenanceCost + $totalExpenseCost;
        $costPerKm = $totalKm > 0 ? round($totalCost / $totalKm, 2) : 0;

        // Pending reminders
        $pendingReminders = $user->vehicles()
            ->with('reminders')
            ->get()
            ->pluck('reminders')
            ->flatten()
            ->where('is_completed', false)
            ->count();

        return response()->json([
            'total_vehicles' => $totalVehicles,
            'total_fuel_cost' => round($totalFuelCost, 2),
            'total_maintenance_cost' => round($totalMaintenanceCost, 2),
            'total_expense_cost' => round($totalExpenseCost, 2),
            'total_cost' => round($totalCost, 2),
            'current_month_total' => round($currentMonthTotal, 2),
            'avg_consumption' => $avgConsumption,
            'cost_per_km' => $costPerKm,
            'pending_reminders' => $pendingReminders,
        ]);
    }
}
