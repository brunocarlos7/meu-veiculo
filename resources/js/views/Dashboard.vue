<template>
  <div class="dashboard">
    <!-- Metrics Grid -->
    <div class="metrics-grid">
      <div class="metric-card animate-in stagger-1">
        <div class="metric-icon" style="background: var(--md-primary-container); color: var(--md-on-primary-container);">
          <span class="material-symbols-rounded">garage</span>
        </div>
        <div class="metric-info">
          <span class="metric-label">Veículos</span>
          <span class="metric-value">{{ metrics.total_vehicles }}</span>
        </div>
      </div>

      <div class="metric-card animate-in stagger-2">
        <div class="metric-icon" style="background: var(--md-secondary-container); color: var(--md-on-secondary-container);">
          <span class="material-symbols-rounded">calendar_month</span>
        </div>
        <div class="metric-info">
          <span class="metric-label">Gasto Mensal</span>
          <span class="metric-value currency">R$ {{ formatNumber(metrics.current_month_total) }}</span>
        </div>
      </div>

      <div class="metric-card animate-in stagger-3">
        <div class="metric-icon" style="background: var(--md-tertiary-container); color: var(--md-on-tertiary-container);">
          <span class="material-symbols-rounded">payments</span>
        </div>
        <div class="metric-info">
          <span class="metric-label">Custo Total</span>
          <span class="metric-value">R$ {{ formatNumber(metrics.total_cost) }}</span>
        </div>
      </div>

      <div class="metric-card animate-in stagger-4">
        <div class="metric-icon" style="background: #DBEAFE; color: #1E40AF;">
          <span class="material-symbols-rounded">speed</span>
        </div>
        <div class="metric-info">
          <span class="metric-label">Média km/L</span>
          <span class="metric-value">{{ metrics.avg_consumption ? metrics.avg_consumption + ' km/L' : '—' }}</span>
        </div>
      </div>

      <div class="metric-card animate-in stagger-4">
        <div class="metric-icon" style="background: #FEF3C7; color: #92400E;">
          <span class="material-symbols-rounded">price_change</span>
        </div>
        <div class="metric-info">
          <span class="metric-label">Custo/km</span>
          <span class="metric-value">{{ metrics.cost_per_km ? 'R$ ' + formatNumber(metrics.cost_per_km) : '—' }}</span>
        </div>
      </div>

      <div class="metric-card animate-in stagger-4" @click="$router.push('/reminders')" style="cursor: pointer;">
        <div class="metric-icon" :style="metrics.pending_reminders > 0 ? 'background: var(--md-error-container); color: var(--md-on-error-container);' : 'background: #D1FAE5; color: #065F46;'">
          <span class="material-symbols-rounded">notifications</span>
        </div>
        <div class="metric-info">
          <span class="metric-label">Lembretes</span>
          <span class="metric-value">{{ metrics.pending_reminders }} pendente(s)</span>
        </div>
      </div>
    </div>

    <!-- Cost Breakdown -->
    <div class="breakdown-grid animate-in" style="animation-delay: 200ms;">
      <div class="breakdown-card md-card">
        <div class="breakdown-header">
          <span class="material-symbols-rounded" style="color: var(--md-primary);">local_gas_station</span>
          <span>Combustível</span>
        </div>
        <span class="breakdown-value">R$ {{ formatNumber(metrics.total_fuel_cost) }}</span>
      </div>
      <div class="breakdown-card md-card">
        <div class="breakdown-header">
          <span class="material-symbols-rounded" style="color: var(--md-tertiary);">build</span>
          <span>Manutenção</span>
        </div>
        <span class="breakdown-value">R$ {{ formatNumber(metrics.total_maintenance_cost) }}</span>
      </div>
      <div class="breakdown-card md-card">
        <div class="breakdown-header">
          <span class="material-symbols-rounded" style="color: var(--md-secondary);">receipt_long</span>
          <span>Despesas</span>
        </div>
        <span class="breakdown-value">R$ {{ formatNumber(metrics.total_expense_cost) }}</span>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="md-card quick-card animate-in" style="animation-delay: 300ms;">
      <div class="quick-content">
        <div class="quick-text">
          <h2>Acesso rápido</h2>
          <p>Gerencie veículos, veja relatórios ou configure lembretes.</p>
        </div>
        <div class="quick-actions">
          <router-link to="/vehicles" class="md-btn-filled">
            <span class="material-symbols-rounded">garage</span>
            Veículos
          </router-link>
          <router-link to="/reports" class="md-btn-outlined">
            <span class="material-symbols-rounded">bar_chart</span>
            Relatórios
          </router-link>
          <router-link to="/reminders" class="md-btn-outlined">
            <span class="material-symbols-rounded">notifications</span>
            Lembretes
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '../axios';

const metrics = ref({
  total_vehicles: 0,
  total_fuel_cost: 0,
  total_maintenance_cost: 0,
  total_expense_cost: 0,
  total_cost: 0,
  current_month_total: 0,
  avg_consumption: 0,
  cost_per_km: 0,
  pending_reminders: 0,
});

const formatNumber = (n) => {
  return parseFloat(n || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

onMounted(async () => {
  try {
    const response = await axios.get('/api/dashboard/metrics');
    metrics.value = response.data;
  } catch (error) {
    console.error('Failed to load metrics', error);
  }
});
</script>

<style scoped>
.dashboard { display: flex; flex-direction: column; gap: 24px; }
.metrics-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}
.metric-card {
  background: var(--md-surface-container-lowest);
  border-radius: var(--md-shape-md);
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: var(--md-elevation-1);
  transition: all var(--md-motion-duration-short) var(--md-motion-standard);
}
.metric-card:hover {
  box-shadow: var(--md-elevation-2);
  transform: translateY(-2px);
}
.metric-icon {
  width: 48px;
  height: 48px;
  border-radius: var(--md-shape-md);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.metric-info {
  display: flex;
  flex-direction: column;
  min-width: 0;
}
.metric-label {
  font-size: 12px;
  font-weight: 500;
  color: var(--md-on-surface-variant);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.metric-value {
  font-size: 22px;
  font-weight: 700;
  color: var(--md-on-surface);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.currency { color: var(--md-secondary); }

/* Breakdown */
.breakdown-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}
.breakdown-card {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.breakdown-header {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 500;
  color: var(--md-on-surface-variant);
}
.breakdown-value {
  font-size: 20px;
  font-weight: 700;
  color: var(--md-on-surface);
}

/* Quick card */
.quick-card { padding: 0; }
.quick-content {
  padding: 28px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  flex-wrap: wrap;
}
.quick-text h2 {
  font-size: 18px;
  font-weight: 600;
  color: var(--md-on-surface);
  margin-bottom: 4px;
}
.quick-text p {
  font-size: 14px;
  color: var(--md-on-surface-variant);
}
.quick-actions { display: flex; gap: 12px; flex-wrap: wrap; }

/* ---- Responsive ---- */
@media (max-width: 1024px) {
  .metrics-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 600px) {
  .metrics-grid { grid-template-columns: 1fr; }
  .breakdown-grid { grid-template-columns: 1fr; }
  .quick-content { flex-direction: column; align-items: flex-start; }
}
</style>
