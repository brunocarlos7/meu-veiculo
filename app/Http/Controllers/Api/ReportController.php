<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $vehicleId = $request->input('vehicle_id');
        $from = $request->input('from', Carbon::now()->subMonths(6)->toDateString());
        $to = $request->input('to', Carbon::now()->toDateString());

        $vehiclesQuery = $user->vehicles();
        if ($vehicleId) {
            $vehiclesQuery->where('id', $vehicleId);
        }
        $vehicleIds = $vehiclesQuery->pluck('id');

        // Monthly spending by category
        $fuelMonthly = DB::table('fuel_entries')
            ->whereIn('vehicle_id', $vehicleIds)
            ->whereBetween('date', [$from, $to])
            ->selectRaw("strftime('%Y-%m', date) as month, SUM(total_value) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $maintenanceMonthly = DB::table('maintenances')
            ->whereIn('vehicle_id', $vehicleIds)
            ->whereBetween('date', [$from, $to])
            ->selectRaw("strftime('%Y-%m', date) as month, SUM(cost) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $expenseMonthly = DB::table('expenses')
            ->whereIn('vehicle_id', $vehicleIds)
            ->whereBetween('date', [$from, $to])
            ->selectRaw("strftime('%Y-%m', date) as month, SUM(cost) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        // Generate all months in range
        $start = Carbon::parse($from)->startOfMonth();
        $end = Carbon::parse($to)->endOfMonth();
        $months = [];
        while ($start <= $end) {
            $key = $start->format('Y-m');
            $months[$key] = [
                'month' => $key,
                'label' => $start->translatedFormat('M/Y'),
                'fuel' => round((float) ($fuelMonthly[$key] ?? 0), 2),
                'maintenance' => round((float) ($maintenanceMonthly[$key] ?? 0), 2),
                'expense' => round((float) ($expenseMonthly[$key] ?? 0), 2),
            ];
            $start->addMonth();
        }

        // Consumption evolution per vehicle
        $consumptionByVehicle = [];
        $vehicles = $user->vehicles()->when($vehicleId, fn($q) => $q->where('id', $vehicleId))->get();

        foreach ($vehicles as $vehicle) {
            $entries = $vehicle->fuelEntries()
                ->where('is_full_tank', true)
                ->whereBetween('date', [$from, $to])
                ->orderBy('odometer')
                ->get();

            $dataPoints = [];
            for ($i = 1; $i < $entries->count(); $i++) {
                $km = $entries[$i]->odometer - $entries[$i - 1]->odometer;
                $liters = $entries[$i]->liters;
                if ($km > 0 && $liters > 0) {
                    $dataPoints[] = [
                        'date' => $entries[$i]->date->format('Y-m-d'),
                        'km_per_liter' => round($km / $liters, 2),
                    ];
                }
            }

            if (count($dataPoints)) {
                $consumptionByVehicle[] = [
                    'vehicle' => $vehicle->brand . ' ' . $vehicle->model,
                    'vehicle_id' => $vehicle->id,
                    'data' => $dataPoints,
                ];
            }
        }

        // Cost per km
        $totalKm = 0;
        $totalCost = 0;
        foreach ($vehicles as $vehicle) {
            $entries = $vehicle->fuelEntries()
                ->whereBetween('date', [$from, $to])
                ->orderBy('odometer')
                ->get();

            if ($entries->count() >= 2) {
                $totalKm += $entries->last()->odometer - $entries->first()->odometer;
            }

            $totalCost += $entries->sum('total_value');
            $totalCost += $vehicle->maintenances()->whereBetween('date', [$from, $to])->sum('cost');
            $totalCost += $vehicle->expenses()->whereBetween('date', [$from, $to])->sum('cost');
        }

        $costPerKm = $totalKm > 0 ? round($totalCost / $totalKm, 2) : 0;

        return response()->json([
            'monthly_spending' => array_values($months),
            'consumption_evolution' => $consumptionByVehicle,
            'cost_per_km' => $costPerKm,
            'total_km' => $totalKm,
            'total_cost' => round($totalCost, 2),
            'period' => ['from' => $from, 'to' => $to],
        ]);
    }

    public function export(Request $request, $type)
    {
        $user = Auth::user();
        $vehicleId = $request->input('vehicle_id');
        $from = $request->input('from', Carbon::now()->subYear()->toDateString());
        $to = $request->input('to', Carbon::now()->toDateString());

        $vehiclesQuery = $user->vehicles();
        if ($vehicleId) {
            $vehiclesQuery->where('id', $vehicleId);
        }
        $vehicleIds = $vehiclesQuery->pluck('id');

        // Gather all data
        $fuelEntries = DB::table('fuel_entries')
            ->join('vehicles', 'vehicles.id', '=', 'fuel_entries.vehicle_id')
            ->whereIn('fuel_entries.vehicle_id', $vehicleIds)
            ->whereBetween('fuel_entries.date', [$from, $to])
            ->select('fuel_entries.*', DB::raw("vehicles.brand || ' ' || vehicles.model as vehicle_name"))
            ->orderBy('fuel_entries.date', 'desc')
            ->get();

        $maintenances = DB::table('maintenances')
            ->join('vehicles', 'vehicles.id', '=', 'maintenances.vehicle_id')
            ->whereIn('maintenances.vehicle_id', $vehicleIds)
            ->whereBetween('maintenances.date', [$from, $to])
            ->select('maintenances.*', DB::raw("vehicles.brand || ' ' || vehicles.model as vehicle_name"))
            ->orderBy('maintenances.date', 'desc')
            ->get();

        $expenses = DB::table('expenses')
            ->join('vehicles', 'vehicles.id', '=', 'expenses.vehicle_id')
            ->whereIn('expenses.vehicle_id', $vehicleIds)
            ->whereBetween('expenses.date', [$from, $to])
            ->select('expenses.*', DB::raw("vehicles.brand || ' ' || vehicles.model as vehicle_name"))
            ->orderBy('expenses.date', 'desc')
            ->get();

        if ($type === 'csv') {
            return $this->exportCsv($fuelEntries, $maintenances, $expenses);
        }

        return response()->json(['message' => 'Formato não suportado. Use: csv'], 400);
    }

    private function exportCsv($fuelEntries, $maintenances, $expenses)
    {
        $lines = [];
        $lines[] = 'Categoria,Veículo,Data,Tipo,Valor (R$),Litros,Preço/L,Odômetro (km),Observações';

        foreach ($fuelEntries as $e) {
            $lines[] = implode(',', [
                'Abastecimento',
                '"' . $e->vehicle_name . '"',
                $e->date,
                '"' . ($e->fuel_type ?? '') . '"',
                $e->total_value,
                $e->liters,
                $e->price_per_liter,
                $e->odometer,
                '"' . ($e->station ?? '') . '"',
            ]);
        }

        foreach ($maintenances as $m) {
            $lines[] = implode(',', [
                'Manutenção',
                '"' . $m->vehicle_name . '"',
                $m->date,
                '"' . $m->type . '"',
                $m->cost,
                '',
                '',
                $m->odometer,
                '"' . str_replace('"', '""', $m->notes ?? '') . '"',
            ]);
        }

        foreach ($expenses as $e) {
            $lines[] = implode(',', [
                'Despesa',
                '"' . $e->vehicle_name . '"',
                $e->date,
                '"' . $e->type . '"',
                $e->cost,
                '',
                '',
                '',
                '"' . str_replace('"', '""', $e->notes ?? '') . '"',
            ]);
        }

        $csv = implode("\n", $lines);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="meu-veiculo-export.csv"',
        ]);
    }
}
