<template>
  <div class="fuel-page">
    <div class="page-header animate-in">
      <div class="page-header-left">
        <button @click="$router.push('/vehicles')" class="back-btn">
          <span class="material-symbols-rounded">arrow_back</span>
        </button>
        <div>
          <h1 class="page-title">Abastecimentos</h1>
          <p class="page-subtitle">{{ fuelStore.fuelEntries.length }} registro(s)</p>
        </div>
      </div>
      <button @click="openModal()" class="md-btn-filled">
        <span class="material-symbols-rounded">add</span>
        Novo Lançamento
      </button>
    </div>

    <!-- Loading -->
    <div v-if="fuelStore.loading && !fuelStore.fuelEntries.length" class="loading-state">
      <span class="material-symbols-rounded spinning" style="font-size: 40px; color: var(--md-primary);">progress_activity</span>
    </div>

    <!-- Empty -->
    <div v-else-if="fuelStore.fuelEntries.length === 0" class="empty-state animate-in">
      <div class="empty-icon">
        <span class="material-symbols-rounded" style="font-size: 64px;">local_gas_station</span>
      </div>
      <h2>Nenhum abastecimento</h2>
      <p>Registre seu primeiro abastecimento para acompanhar o consumo.</p>
      <button @click="openModal()" class="md-btn-filled" style="margin-top: 16px;">
        <span class="material-symbols-rounded">add</span>
        Lançar Abastecimento
      </button>
    </div>

    <!-- Entries List -->
    <div v-else class="fuel-list">
      <div v-for="(entry, index) in fuelStore.fuelEntries" :key="entry.id"
           class="fuel-entry md-card animate-in" :style="{ animationDelay: (index * 50) + 'ms' }">
        <div class="fuel-entry-main">
          <div class="fuel-entry-icon">
            <span class="material-symbols-rounded">local_gas_station</span>
          </div>
          <div class="fuel-entry-info">
            <span class="fuel-entry-station">{{ entry.station || 'Posto não informado' }}</span>
            <span class="fuel-entry-date">{{ new Date(entry.date).toLocaleDateString('pt-BR') }}</span>
          </div>
          <div class="fuel-entry-cost">
            <span class="fuel-entry-total">R$ {{ parseFloat(entry.total_value).toFixed(2) }}</span>
            <span class="fuel-entry-detail">{{ parseFloat(entry.liters).toFixed(1) }}L @ R${{ parseFloat(entry.price_per_liter).toFixed(2) }}</span>
          </div>
        </div>
        <div class="fuel-entry-footer">
          <div class="fuel-entry-tags">
            <span class="md-chip md-chip-secondary">{{ entry.fuel_type }}</span>
            <span class="md-chip md-chip-tertiary">{{ entry.odometer }} km</span>
          </div>
          <div class="entry-actions">
            <button @click="openEditModal(entry)" class="md-btn-text" style="color: var(--md-on-surface-variant); padding: 6px 12px;" title="Editar">
              <span class="material-symbols-rounded" style="font-size: 20px;">edit</span>
            </button>
            <button @click="fuelStore.deleteFuelEntry(entry.id)" class="md-btn-text" style="color: var(--md-error); padding: 6px 12px;" title="Excluir">
              <span class="material-symbols-rounded" style="font-size: 20px;">delete</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- FAB -->
    <button @click="openModal()" class="md-fab mobile-only">
      <span class="material-symbols-rounded">add</span>
    </button>

    <!-- Fuel Modal (Create/Edit) -->
    <ModalDialog :show="showModal" :title="editingEntryId ? 'Editar Abastecimento' : 'Novo Abastecimento'" @close="showModal = false">
      <form @submit.prevent="handleSubmit" class="modal-form">
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
            <input type="number" v-model="form.odometer" required class="md-input-outlined" placeholder="Km atual">
          </div>
          <div class="md-input-group">
            <label>Preço/Litro (R$)</label>
            <input type="number" step="0.01" v-model="form.price_per_liter" required @input="calcTotal" class="md-input-outlined" placeholder="0.00">
          </div>
          <div class="md-input-group">
            <label>Litros</label>
            <input type="number" step="0.01" v-model="form.liters" required @input="calcTotal" class="md-input-outlined" placeholder="0.00">
          </div>
        </div>
        <div class="fuel-total" v-if="form.total_value">
          <span class="material-symbols-rounded">payments</span>
          <strong>R$ {{ parseFloat(form.total_value).toFixed(2) }}</strong>
        </div>
        <div class="checkbox-row">
          <label class="checkbox-label">
            <input type="checkbox" v-model="form.is_full_tank" class="md-checkbox">
            <span class="material-symbols-rounded checkbox-icon">{{ form.is_full_tank ? 'check_box' : 'check_box_outline_blank' }}</span>
            Tanque Cheio
          </label>
        </div>
        <div v-if="fuelStore.errors.length" class="form-error animate-in" style="margin-top: 16px;">
          <span class="material-symbols-rounded" style="font-size: 18px;">error</span>
          {{ fuelStore.errors[0] }}
        </div>
        <div class="modal-actions">
          <button type="button" @click="showModal = false" class="md-btn-outlined">Cancelar</button>
          <button type="submit" :disabled="fuelStore.loading" class="md-btn-filled">
            <span v-if="fuelStore.loading" class="material-symbols-rounded spinning">progress_activity</span>
            {{ fuelStore.loading ? 'Salvando...' : (editingEntryId ? 'Atualizar' : 'Lançar') }}
          </button>
        </div>
      </form>
    </ModalDialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useFuelStore } from '../stores/fuel';
import ModalDialog from '../components/ModalDialog.vue';

const route = useRoute();
const fuelStore = useFuelStore();
const vehicleId = route.params.id;
const showModal = ref(false);
const editingEntryId = ref(null);

const defaultForm = () => ({
  vehicle_id: vehicleId,
  date: new Date().toISOString().split('T')[0],
  station: '', fuel_type: 'Gasolina', price_per_liter: '', liters: '',
  total_value: '', odometer: '', is_full_tank: true,
});
const form = ref(defaultForm());

const openModal = () => {
  editingEntryId.value = null;
  form.value = defaultForm();
  fuelStore.errors = [];
  showModal.value = true;
};

const openEditModal = (entry) => {
  editingEntryId.value = entry.id;
  form.value = {
    vehicle_id: vehicleId,
    date: entry.date?.split('T')[0] || entry.date,
    station: entry.station || '',
    fuel_type: entry.fuel_type,
    price_per_liter: entry.price_per_liter,
    liters: entry.liters,
    total_value: entry.total_value,
    odometer: entry.odometer,
    is_full_tank: !!entry.is_full_tank,
  };
  fuelStore.errors = [];
  showModal.value = true;
};

const calcTotal = () => {
  if (form.value.price_per_liter && form.value.liters) {
    form.value.total_value = (parseFloat(form.value.price_per_liter) * parseFloat(form.value.liters)).toFixed(2);
  }
};

const handleSubmit = async () => {
  let success;
  if (editingEntryId.value) {
    success = await fuelStore.updateFuelEntry(editingEntryId.value, form.value);
  } else {
    success = await fuelStore.createFuelEntry(form.value);
  }
  if (success) {
    showModal.value = false;
    fuelStore.fetchFuelEntries(vehicleId);
  }
};

onMounted(() => fuelStore.fetchFuelEntries(vehicleId));
</script>

<style scoped>
.fuel-page { display: flex; flex-direction: column; gap: 24px; }
.page-header {
  display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;
}
.page-header-left { display: flex; align-items: center; gap: 8px; }
.back-btn {
  width: 40px; height: 40px; border: none; background: none;
  border-radius: var(--md-shape-full); cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  color: var(--md-on-surface-variant);
}
.back-btn:hover { background: rgba(0,0,0,0.05); }
.page-title { font-size: 28px; font-weight: 700; }
.page-subtitle { font-size: 14px; color: var(--md-on-surface-variant); margin-top: 2px; }

.loading-state { display: flex; justify-content: center; padding: 48px; }
.empty-state {
  text-align: center; padding: 64px 24px;
  background: var(--md-surface-container-lowest); border-radius: var(--md-shape-xl);
  box-shadow: var(--md-elevation-1);
}
.empty-icon {
  width: 96px; height: 96px; border-radius: var(--md-shape-full);
  background: var(--md-surface-container); color: var(--md-on-surface-variant);
  display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;
}
.empty-state h2 { font-size: 20px; font-weight: 600; margin-bottom: 8px; }
.empty-state p { font-size: 14px; color: var(--md-on-surface-variant); }

.fuel-list { display: flex; flex-direction: column; gap: 12px; }
.fuel-entry { padding: 0; }
.fuel-entry-main { display: flex; align-items: center; gap: 16px; padding: 16px 20px; }
.fuel-entry-icon {
  width: 40px; height: 40px; border-radius: var(--md-shape-md);
  background: var(--md-secondary-container); color: var(--md-on-secondary-container);
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.fuel-entry-info { flex: 1; min-width: 0; }
.fuel-entry-station { display: block; font-weight: 600; font-size: 14px; }
.fuel-entry-date { font-size: 12px; color: var(--md-on-surface-variant); }
.fuel-entry-cost { text-align: right; flex-shrink: 0; }
.fuel-entry-total { display: block; font-weight: 700; font-size: 16px; color: var(--md-secondary); }
.fuel-entry-detail { font-size: 12px; color: var(--md-on-surface-variant); }
.fuel-entry-footer {
  display: flex; align-items: center; justify-content: space-between;
  padding: 8px 12px 8px 20px; border-top: 1px solid var(--md-outline-variant);
}
.fuel-entry-tags { display: flex; gap: 8px; }
.entry-actions { display: flex; gap: 4px; }

/* Modal Form */
.modal-form { display: flex; flex-direction: column; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.modal-actions {
  display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; padding-top: 16px;
  border-top: 1px solid var(--md-outline-variant);
}
.form-error {
  display: flex; align-items: center; gap: 8px; padding: 12px 16px;
  background: var(--md-error-container); color: var(--md-on-error-container);
  border-radius: var(--md-shape-sm); font-size: 14px;
}
.fuel-total {
  display: flex; align-items: center; gap: 8px; margin-top: 16px;
  padding: 16px; border-radius: var(--md-shape-md);
  background: var(--md-secondary-container); color: var(--md-on-secondary-container);
  font-size: 20px;
}
.checkbox-row { margin-top: 12px; }
.checkbox-label {
  display: flex; align-items: center; gap: 8px;
  font-size: 14px; cursor: pointer; user-select: none;
}
.md-checkbox { display: none; }
.checkbox-icon { color: var(--md-primary); font-size: 24px; }

.mobile-only { display: none; }
.spinning { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 600px) {
  .page-header .md-btn-filled { display: none; }
  .mobile-only { display: flex; }
  .form-grid { grid-template-columns: 1fr; }
  .fuel-entry-main { flex-wrap: wrap; gap: 12px; }
  .fuel-entry-cost { width: 100%; text-align: left; }
}
</style>
