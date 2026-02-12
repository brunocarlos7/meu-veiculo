<template>
  <div class="form-page animate-in">
    <div class="form-card md-card">
      <div class="form-header">
        <button @click="$router.go(-1)" class="back-btn">
          <span class="material-symbols-rounded">arrow_back</span>
        </button>
        <div>
          <h2 class="form-title">Novo Veículo</h2>
          <p class="form-subtitle">Preencha os dados do seu veículo</p>
        </div>
      </div>

      <form @submit.prevent="handleSubmit" class="form-body">
        <div class="form-grid">
          <div class="md-input-group">
            <label>Marca</label>
            <input type="text" v-model="form.brand" required class="md-input-outlined" placeholder="Ex: Honda">
          </div>
          <div class="md-input-group">
            <label>Modelo</label>
            <input type="text" v-model="form.model" required class="md-input-outlined" placeholder="Ex: CB250F Twister">
          </div>
          <div class="md-input-group">
            <label>Ano</label>
            <input type="number" v-model="form.year" required class="md-input-outlined" placeholder="2024">
          </div>
          <div class="md-input-group">
            <label>Placa</label>
            <input type="text" v-model="form.plate" required class="md-input-outlined" placeholder="ABC1D23">
          </div>
          <div class="md-input-group full-width">
            <label>Tipo de Combustível</label>
            <select v-model="form.fuel_type" required class="md-select">
              <option value="Gasolina">Gasolina</option>
              <option value="Etanol">Etanol</option>
              <option value="Flex">Flex</option>
              <option value="Diesel">Diesel</option>
            </select>
          </div>
        </div>

        <div v-if="vehicleStore.errors.length" class="form-error animate-in">
          <span class="material-symbols-rounded" style="font-size: 18px;">error</span>
          {{ vehicleStore.errors[0] }}
        </div>

        <div class="form-actions">
          <button type="button" @click="$router.go(-1)" class="md-btn-outlined">Cancelar</button>
          <button type="submit" :disabled="vehicleStore.loading" class="md-btn-filled">
            <span v-if="vehicleStore.loading" class="material-symbols-rounded spinning">progress_activity</span>
            {{ vehicleStore.loading ? 'Salvando...' : 'Salvar Veículo' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useVehicleStore } from '../stores/vehicle';
import { useRouter } from 'vue-router';

const vehicleStore = useVehicleStore();
const router = useRouter();
const form = ref({ brand: '', model: '', year: new Date().getFullYear(), plate: '', fuel_type: 'Flex' });

const handleSubmit = async () => {
  const success = await vehicleStore.createVehicle(form.value);
  if (success) router.push('/vehicles');
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
  color: var(--md-on-surface-variant); transition: background 200ms;
}
.back-btn:hover { background: rgba(0,0,0,0.05); }
.form-title { font-size: 20px; font-weight: 700; }
.form-subtitle { font-size: 13px; color: var(--md-on-surface-variant); }
.form-body { padding: 24px; display: flex; flex-direction: column; gap: 24px; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.full-width { grid-column: 1 / -1; }
.form-error {
  display: flex; align-items: center; gap: 8px; padding: 12px 16px;
  background: var(--md-error-container); color: var(--md-on-error-container);
  border-radius: var(--md-shape-sm); font-size: 14px;
}
.form-actions { display: flex; justify-content: flex-end; gap: 12px; padding-top: 8px; }
.spinning { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 600px) {
  .form-grid { grid-template-columns: 1fr; }
  .form-header { padding: 16px; }
  .form-body { padding: 16px; }
}
</style>
