<template>
  <div class="reports-page">
    <div class="page-header animate-in">
      <div>
        <h1 class="page-title">Relatórios</h1>
        <p class="page-subtitle">Análise detalhada dos seus gastos</p>
      </div>
      <button @click="exportCsv" class="md-btn-outlined">
        <span class="material-symbols-rounded">download</span>
        Exportar CSV
      </button>
    </div>

    <!-- Filters -->
    <div class="filters-row animate-in" style="animation-delay: 50ms;">
      <div class="md-input-group">
        <label>Veículo</label>
        <select v-model="filters.vehicle_id" @change="loadReport" class="md-select">
          <option value="">Todos</option>
          <option v-for="v in vehicles" :key="v.id" :value="v.id">{{ v.brand }} {{ v.model }}</option>
        </select>
      </div>
      <div class="md-input-group">
        <label>De</label>
        <input type="date" v-model="filters.from" @change="loadReport" class="md-input-outlined">
      </div>
      <div class="md-input-group">
        <label>Até</label>
        <input type="date" v-model="filters.to" @change="loadReport" class="md-input-outlined">
      </div>
      <div class="filter-presets">
        <button v-for="p in presets" :key="p.label" @click="applyPreset(p)"
                class="md-btn-text preset-btn" :class="{ active: activePreset === p.label }">
          {{ p.label }}
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <span class="material-symbols-rounded spinning" style="font-size: 40px; color: var(--md-primary);">progress_activity</span>
    </div>

    <template v-else-if="report">
      <!-- Summary Cards -->
      <div class="summary-grid animate-in" style="animation-delay: 100ms;">
        <div class="summary-card">
          <span class="material-symbols-rounded" style="color: var(--md-primary);">payments</span>
          <div>
            <span class="summary-label">Custo Total</span>
            <span class="summary-value">R$ {{ fmt(report.total_cost) }}</span>
          </div>
        </div>
        <div class="summary-card">
          <span class="material-symbols-rounded" style="color: var(--md-secondary);">route</span>
          <div>
            <span class="summary-label">Km Rodados</span>
            <span class="summary-value">{{ report.total_km.toLocaleString('pt-BR') }} km</span>
          </div>
        </div>
        <div class="summary-card">
          <span class="material-symbols-rounded" style="color: var(--md-tertiary);">price_change</span>
          <div>
            <span class="summary-label">Custo por km</span>
            <span class="summary-value">R$ {{ fmt(report.cost_per_km) }}</span>
          </div>
        </div>
      </div>

      <!-- Monthly Spending Chart -->
      <div class="md-card chart-card animate-in" style="animation-delay: 150ms;">
        <h3 class="chart-title">Gastos Mensais por Categoria</h3>
        <div class="chart-container">
          <canvas ref="monthlyChartRef"></canvas>
        </div>
      </div>

      <!-- Consumption Evolution Chart -->
      <div class="md-card chart-card animate-in" style="animation-delay: 200ms;" v-if="report.consumption_evolution.length">
        <h3 class="chart-title">Evolução do Consumo (km/L)</h3>
        <div class="chart-container">
          <canvas ref="consumptionChartRef"></canvas>
        </div>
      </div>

      <!-- Monthly Table -->
      <div class="md-card table-card animate-in" style="animation-delay: 250ms;">
        <h3 class="chart-title">Detalhamento Mensal</h3>
        <div class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th>Mês</th>
                <th>Combustível</th>
                <th>Manutenção</th>
                <th>Despesas</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in report.monthly_spending" :key="row.month">
                <td>{{ row.label }}</td>
                <td>R$ {{ fmt(row.fuel) }}</td>
                <td>R$ {{ fmt(row.maintenance) }}</td>
                <td>R$ {{ fmt(row.expense) }}</td>
                <td class="total-cell">R$ {{ fmt(row.fuel + row.maintenance + row.expense) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick, watch } from 'vue';
import { useVehicleStore } from '../stores/vehicle';
import axios from '../axios';
import Chart from 'chart.js/auto';

const vehicleStore = useVehicleStore();
const vehicles = ref([]);
const loading = ref(false);
const report = ref(null);
const activePreset = ref('6 meses');

const monthlyChartRef = ref(null);
const consumptionChartRef = ref(null);
let monthlyChart = null;
let consumptionChart = null;

const now = new Date();
const filters = reactive({
  vehicle_id: '',
  from: new Date(now.getFullYear(), now.getMonth() - 5, 1).toISOString().split('T')[0],
  to: now.toISOString().split('T')[0],
});

const presets = [
  { label: '30 dias', months: 1 },
  { label: 'Mês atual', months: 0 },
  { label: '6 meses', months: 6 },
  { label: 'Ano atual', months: -1 },
];

const fmt = (n) => parseFloat(n || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const applyPreset = (preset) => {
  activePreset.value = preset.label;
  const today = new Date();
  if (preset.months === 0) {
    filters.from = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
  } else if (preset.months === -1) {
    filters.from = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
  } else {
    filters.from = new Date(today.getFullYear(), today.getMonth() - preset.months + 1, 1).toISOString().split('T')[0];
  }
  filters.to = today.toISOString().split('T')[0];
  loadReport();
};

const loadReport = async () => {
  loading.value = true;
  try {
    const params = { from: filters.from, to: filters.to };
    if (filters.vehicle_id) params.vehicle_id = filters.vehicle_id;
    const response = await axios.get('/api/reports', { params });
    report.value = response.data;
    await nextTick();
    renderCharts();
  } catch (error) {
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const chartColors = {
  fuel: { bg: 'rgba(79, 70, 229, 0.15)', border: '#4F46E5' },
  maintenance: { bg: 'rgba(245, 158, 11, 0.15)', border: '#F59E0B' },
  expense: { bg: 'rgba(5, 150, 105, 0.15)', border: '#059669' },
};

const renderCharts = () => {
  if (!report.value) return;

  // Monthly spending
  if (monthlyChart) monthlyChart.destroy();
  if (monthlyChartRef.value) {
    const labels = report.value.monthly_spending.map(m => m.label);
    monthlyChart = new Chart(monthlyChartRef.value, {
      type: 'bar',
      data: {
        labels,
        datasets: [
          { label: 'Combustível', data: report.value.monthly_spending.map(m => m.fuel), backgroundColor: chartColors.fuel.bg, borderColor: chartColors.fuel.border, borderWidth: 2, borderRadius: 6 },
          { label: 'Manutenção', data: report.value.monthly_spending.map(m => m.maintenance), backgroundColor: chartColors.maintenance.bg, borderColor: chartColors.maintenance.border, borderWidth: 2, borderRadius: 6 },
          { label: 'Despesas', data: report.value.monthly_spending.map(m => m.expense), backgroundColor: chartColors.expense.bg, borderColor: chartColors.expense.border, borderWidth: 2, borderRadius: 6 },
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16, font: { family: 'Inter', size: 12 } } } },
        scales: {
          y: { beginAtZero: true, ticks: { callback: v => 'R$ ' + v, font: { family: 'Inter', size: 11 } }, grid: { color: 'rgba(0,0,0,0.06)' } },
          x: { ticks: { font: { family: 'Inter', size: 11 } }, grid: { display: false } }
        }
      }
    });
  }

  // Consumption
  if (consumptionChart) consumptionChart.destroy();
  if (consumptionChartRef.value && report.value.consumption_evolution.length) {
    const colors = ['#4F46E5', '#059669', '#F59E0B', '#DC2626'];
    const datasets = report.value.consumption_evolution.map((v, i) => ({
      label: v.vehicle,
      data: v.data.map(d => ({ x: d.date, y: d.km_per_liter })),
      borderColor: colors[i % colors.length],
      backgroundColor: colors[i % colors.length] + '20',
      fill: true,
      tension: 0.4,
      pointRadius: 4,
      pointHoverRadius: 6,
    }));
    consumptionChart = new Chart(consumptionChartRef.value, {
      type: 'line',
      data: { datasets },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16, font: { family: 'Inter', size: 12 } } } },
        scales: {
          y: { beginAtZero: false, ticks: { callback: v => v + ' km/L', font: { family: 'Inter', size: 11 } }, grid: { color: 'rgba(0,0,0,0.06)' } },
          x: { type: 'category', ticks: { font: { family: 'Inter', size: 11 } }, grid: { display: false } }
        }
      }
    });
  }
};

const exportCsv = async () => {
  try {
    const params = { from: filters.from, to: filters.to };
    if (filters.vehicle_id) params.vehicle_id = filters.vehicle_id;
    const response = await axios.get('/api/export/csv', { params, responseType: 'blob' });
    const url = URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.download = 'meu-veiculo-export.csv';
    link.click();
    URL.revokeObjectURL(url);
  } catch (error) {
    console.error(error);
  }
};

onMounted(async () => {
  await vehicleStore.fetchVehicles();
  vehicles.value = vehicleStore.vehicles;
  loadReport();
});
</script>

<style scoped>
.reports-page { display: flex; flex-direction: column; gap: 24px; }
.page-header {
  display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;
}
.page-title { font-size: 28px; font-weight: 700; }
.page-subtitle { font-size: 14px; color: var(--md-on-surface-variant); margin-top: 2px; }

.filters-row {
  display: flex; align-items: flex-end; gap: 16px; flex-wrap: wrap;
  padding: 20px; background: var(--md-surface-container-lowest);
  border-radius: var(--md-shape-md); box-shadow: var(--md-elevation-1);
}
.filter-presets { display: flex; gap: 4px; flex-wrap: wrap; }
.preset-btn { font-size: 12px; padding: 6px 12px; }
.preset-btn.active { background: var(--md-primary-container); color: var(--md-on-primary-container); }

.loading-state { display: flex; justify-content: center; padding: 48px; }

.summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.summary-card {
  display: flex; align-items: center; gap: 16px; padding: 20px;
  background: var(--md-surface-container-lowest); border-radius: var(--md-shape-md);
  box-shadow: var(--md-elevation-1);
}
.summary-label { display: block; font-size: 12px; font-weight: 500; color: var(--md-on-surface-variant); text-transform: uppercase; letter-spacing: 0.5px; }
.summary-value { display: block; font-size: 20px; font-weight: 700; color: var(--md-on-surface); }

.chart-card { padding: 24px; }
.chart-title { font-size: 16px; font-weight: 600; margin-bottom: 16px; color: var(--md-on-surface); }
.chart-container { position: relative; height: 320px; }

.table-card { padding: 24px; }
.table-wrapper { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.data-table th {
  padding: 12px 16px; text-align: left; font-weight: 600; font-size: 12px;
  text-transform: uppercase; letter-spacing: 0.5px; color: var(--md-on-surface-variant);
  border-bottom: 2px solid var(--md-outline-variant);
}
.data-table td { padding: 12px 16px; border-bottom: 1px solid var(--md-outline-variant); }
.total-cell { font-weight: 700; color: var(--md-primary); }

.spinning { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 768px) {
  .summary-grid { grid-template-columns: 1fr; }
  .filters-row { flex-direction: column; align-items: stretch; }
  .chart-container { height: 260px; }
}
</style>
