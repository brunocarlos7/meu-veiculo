<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\FuelEntry;
use App\Models\Maintenance;
use App\Models\Expense;
use App\Models\Reminder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name' => 'Usuário Teste',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // ───── Veículo 1: Honda Civic 2022 ─────
        $civic = Vehicle::create([
            'user_id' => $user->id,
            'brand' => 'Honda',
            'model' => 'Civic',
            'year' => 2022,
            'plate' => 'BRA2E19',
            'fuel_type' => 'Flex',
        ]);

        // ───── Veículo 2: Fiat Argo 2024 ─────
        $argo = Vehicle::create([
            'user_id' => $user->id,
            'brand' => 'Fiat',
            'model' => 'Argo',
            'year' => 2024,
            'plate' => 'RIO4F56',
            'fuel_type' => 'Flex',
        ]);

        // ───── Abastecimentos: Civic ─────
        foreach ([
            ['date' => '2025-11-10', 'station' => 'Posto Shell Centro', 'fuel_type' => 'Gasolina', 'price_per_liter' => 5.79, 'liters' => 42, 'total_value' => 243.18, 'odometer' => 14200, 'is_full_tank' => true],
            ['date' => '2025-11-28', 'station' => 'Posto Ipiranga', 'fuel_type' => 'Gasolina', 'price_per_liter' => 5.85, 'liters' => 40, 'total_value' => 234.00, 'odometer' => 14720, 'is_full_tank' => true],
            ['date' => '2025-12-15', 'station' => 'Auto Posto Avenida', 'fuel_type' => 'Etanol', 'price_per_liter' => 3.99, 'liters' => 45, 'total_value' => 179.55, 'odometer' => 15230, 'is_full_tank' => true],
            ['date' => '2026-01-05', 'station' => 'Posto BR Rodovia', 'fuel_type' => 'Gasolina', 'price_per_liter' => 5.95, 'liters' => 38, 'total_value' => 226.10, 'odometer' => 15780, 'is_full_tank' => true],
            ['date' => '2026-01-22', 'station' => 'Posto Shell Centro', 'fuel_type' => 'Gasolina', 'price_per_liter' => 6.09, 'liters' => 36, 'total_value' => 219.24, 'odometer' => 16250, 'is_full_tank' => true],
            ['date' => '2026-02-08', 'station' => 'Posto Ipiranga', 'fuel_type' => 'Gasolina', 'price_per_liter' => 6.15, 'liters' => 34, 'total_value' => 209.10, 'odometer' => 16700, 'is_full_tank' => true],
        ] as $entry) {
            FuelEntry::create(array_merge($entry, ['vehicle_id' => $civic->id]));
        }

        // ───── Abastecimentos: Argo ─────
        foreach ([
            ['date' => '2025-12-01', 'station' => 'Posto BR Barra', 'fuel_type' => 'Gasolina', 'price_per_liter' => 5.99, 'liters' => 44, 'total_value' => 263.56, 'odometer' => 5200, 'is_full_tank' => true],
            ['date' => '2025-12-28', 'station' => 'Posto Shell Zona Sul', 'fuel_type' => 'Etanol', 'price_per_liter' => 4.09, 'liters' => 42, 'total_value' => 171.78, 'odometer' => 5750, 'is_full_tank' => true],
            ['date' => '2026-01-18', 'station' => 'Posto Ipiranga', 'fuel_type' => 'Gasolina', 'price_per_liter' => 6.05, 'liters' => 40, 'total_value' => 242.00, 'odometer' => 6280, 'is_full_tank' => true],
            ['date' => '2026-02-05', 'station' => 'Posto BR Barra', 'fuel_type' => 'Gasolina', 'price_per_liter' => 6.19, 'liters' => 38, 'total_value' => 235.22, 'odometer' => 6800, 'is_full_tank' => true],
        ] as $entry) {
            FuelEntry::create(array_merge($entry, ['vehicle_id' => $argo->id]));
        }

        // ───── Manutenções ─────
        Maintenance::create(['vehicle_id' => $civic->id, 'type' => 'Troca de Óleo', 'date' => '2025-11-20', 'cost' => 289.90, 'odometer' => 14500, 'notes' => 'Óleo Mobil 5W30 sintético + filtro']);
        Maintenance::create(['vehicle_id' => $civic->id, 'type' => 'Revisão', 'date' => '2026-01-12', 'cost' => 720.00, 'odometer' => 15800, 'notes' => 'Revisão 15.000 km na concessionária']);
        Maintenance::create(['vehicle_id' => $civic->id, 'type' => 'Freios', 'date' => '2026-02-01', 'cost' => 450.00, 'odometer' => 16500, 'notes' => 'Pastilhas dianteiras Cobreq']);
        Maintenance::create(['vehicle_id' => $argo->id, 'type' => 'Troca de Óleo', 'date' => '2025-12-10', 'cost' => 185.00, 'odometer' => 5400, 'notes' => 'Óleo Lubrax 5W30 + filtro']);
        Maintenance::create(['vehicle_id' => $argo->id, 'type' => 'Pneus', 'date' => '2026-01-25', 'cost' => 1560.00, 'odometer' => 6400, 'notes' => 'Troca dos 4 pneus Pirelli Cinturato']);

        // ───── Despesas ─────
        Expense::create(['vehicle_id' => $civic->id, 'type' => 'IPVA', 'date' => '2026-01-10', 'cost' => 1850.00, 'notes' => 'IPVA 2026 — parcela única com desconto']);
        Expense::create(['vehicle_id' => $civic->id, 'type' => 'Seguro', 'date' => '2025-11-01', 'cost' => 2680.00, 'notes' => 'Seguro anual Porto Seguro']);
        Expense::create(['vehicle_id' => $civic->id, 'type' => 'Estacionamento', 'date' => '2026-02-01', 'cost' => 180.00, 'notes' => 'Mensalidade fev/2026']);
        Expense::create(['vehicle_id' => $argo->id, 'type' => 'IPVA', 'date' => '2026-01-15', 'cost' => 1420.00, 'notes' => 'IPVA 2026 — 3 parcelas']);
        Expense::create(['vehicle_id' => $argo->id, 'type' => 'Lavagem', 'date' => '2026-02-03', 'cost' => 75.00, 'notes' => 'Lavagem completa + cera']);

        // ───── Lembretes ─────
        Reminder::create(['vehicle_id' => $civic->id, 'type' => 'oil_change', 'title' => 'Próxima troca de óleo', 'due_date' => '2026-05-20', 'due_odometer' => 20800, 'notes' => 'Usar óleo 5W30 sintético']);
        Reminder::create(['vehicle_id' => $civic->id, 'type' => 'licensing', 'title' => 'Licenciamento 2026', 'due_date' => '2026-04-30', 'notes' => 'Verificar débitos no Detran']);
        Reminder::create(['vehicle_id' => $argo->id, 'type' => 'revision', 'title' => 'Revisão 10.000 km', 'due_odometer' => 10000, 'notes' => 'Agendar na concessionária Fiat']);
        Reminder::create(['vehicle_id' => $argo->id, 'type' => 'insurance', 'title' => 'Renovar seguro', 'due_date' => '2026-06-15', 'notes' => 'Cotar ao menos 3 seguradoras']);
    }
}
