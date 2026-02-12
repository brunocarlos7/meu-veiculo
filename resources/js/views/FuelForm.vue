<template>
  <div class="form-page animate-in">
    <div class="form-card md-card">
      <div class="form-header">
        <button @click="$router.go(-1)" class="back-btn">
          <span class="material-symbols-rounded">arrow_back</span>
        </button>
        <div>
          <h2 class="form-title">Novo Abastecimento</h2>
          <p class="form-subtitle">Registre os detalhes do abastecimento</p>
        </div>
      </div>

      <form @submit.prevent="handleSubmit" class="form-body">
        <div class="form-grid">
          <div class="md-input-group">
            <label>Data</label>
            <input type="date" v-model="form.date" required class="md-input-outlined">
          </div>
          <div class="md-input-group">
            <label>Posto</label>
            <input type="text" v-model="form.station" class="md-input-outlined" placeholder="Nome do posto">
          </div>
          <div class="md-input-group">
            <label>Combustível</label>
            <select v-model="form.fuel_type" required class="md-select">
              <option value="Gasolina">Gasolina</option>
              <option value="Etanol">Etanol</option>
              <option value="Diesel">Diesel</option>
              <option value="GNV">GNV</option>
            </select>
          </div>
          <div class="md-input-group">
            <label>Odômetro (km)</label>
            <input type="number" v-model="form.odometer" required class="md-input-outlined" placeholder="0">
          </div>
        </div>

        <hr class="md-divider">

        <div class="form-grid">
          <div class="md-input-group">
            <label>Preço por Litro (R$)</label>
            <input type="number" step="0.01" v-model="form.price_per_liter" required @input="calculateTotal" class="md-input-outlined" placeholder="0.00">
          </div>
          <div class="md-input-group">
            <label>Litros</label>
            <input type="number" step="0.01" v-model="form.liters" required @input="calculateTotal" class="md-input-outlined" placeholder="0.00">
          </div>
          <div class="md-input-group">
            <label>Valor Total (R$)</label>
            <input type="number" step="0.01" v-model="form.total_value" required class="md-input-outlined total-field" placeholder="Calculado automaticamente">
          </div>
          <div class="md-input-group checkbox-group">
            <label class="checkbox-label">
              <input type="checkbox" v-model="form.is_full_tank" class="md-checkbox">
              <span class="material-symbols-rounded checkbox-icon">{{ form.is_full_tank ? 'check_box' : 'check_box_outline_blank' }}</span>
              Tanque Cheio
            </label>
          </div>
        </div>

        <div v-if="fuelStore.errors.length" class="form-error animate-in">
          <span class="material-symbols-rounded" style="font-size: 18px;">error</span>
          {{ fuelStore.errors[0] }}
        </div>

        <div class="form-actions">
          <button type="button" @click="$router.go(-1)" class="md-btn-outlined">Cancelar</button>
          <button type="submit" :disabled="fuelStore.loading" class="md-btn-filled">
            <span v-if="fuelStore.loading" class="material-symbols-rounded spinning">progress_activity</span>
            {{ fuelStore.loading ? 'Salvando...' : 'Lançar Abastecimento' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useFuelStore } from '../stores/fuel';
import { useRoute, useRouter } from 'vue-router';

const fuelStore = useFuelStore();
const route = useRoute();
const router = useRouter();
const vehicleId = route.params.id;

const form = ref({
  vehicle_id: vehicleId,
  date: new Date().toISOString().split('T')[0],
  station: '',
  fuel_type: 'Gasolina',
  price_per_liter: '',
  liters: '',
  total_value: '',
  odometer: '',
  is_full_tank: true,
});

const calculateTotal = () => {
  if (form.value.price_per_liter && form.value.liters) {
    form.value.total_value = (parseFloat(form.value.price_per_liter) * parseFloat(form.value.liters)).toFixed(2);
  }
};

const handleSubmit = async () => {
  const success = await fuelStore.createFuelEntry(form.value);
  if (success) router.push(`/vehicles/${vehicleId}/fuel`);
};
</script>

<style scoped>
.form-page { max-width: 640px; margin: 0 auto; width: 100%; }
.form-card { padding: 0; }
.form-header {
  display: flex; align-items: center; gap: 12px; padding: 20px 24px;
  border-bottom: 1px solid var(--md-outline-variant);
}
.back-btn {
  width: 40px; height: 40px; border: none; background: none;
  border-radius: var(--md-shape-full); cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  color: var(--md-on-surface-variant);
}
.back-btn:hover { background: rgba(0,0,0,0.05); }
.form-title { font-size: 20px; font-weight: 700; }
.form-subtitle { font-size: 13px; color: var(--md-on-surface-variant); }
.form-body { padding: 24px; display: flex; flex-direction: column; gap: 24px; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.total-field { background: var(--md-surface-container-low); }
.form-error {
  display: flex; align-items: center; gap: 8px; padding: 12px 16px;
  background: var(--md-error-container); color: var(--md-on-error-container);
  border-radius: var(--md-shape-sm); font-size: 14px;
}
.form-actions { display: flex; justify-content: flex-end; gap: 12px; padding-top: 8px; }

.checkbox-group { display: flex; align-items: flex-end; }
.checkbox-label {
  display: flex; align-items: center; gap: 8px;
  font-size: 14px; cursor: pointer; user-select: none;
  padding: 12px 0;
}
.md-checkbox { display: none; }
.checkbox-icon { color: var(--md-primary); font-size: 24px; }

.spinning { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 600px) {
  .form-grid { grid-template-columns: 1fr; }
  .form-header { padding: 16px; }
  .form-body { padding: 16px; }
}
</style>
