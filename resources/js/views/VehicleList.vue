<template>
  <div class="vehicle-page">
    <div class="page-header animate-in">
      <div>
        <h1 class="page-title">Meus Veículos</h1>
        <p class="page-subtitle">{{ vehicleStore.vehicles.length }} veículo(s) cadastrado(s)</p>
      </div>
      <button @click="openModal()" class="md-btn-filled">
        <span class="material-symbols-rounded">add</span>
        Novo Veículo
      </button>
    </div>

    <!-- Loading -->
    <div v-if="vehicleStore.loading && !vehicleStore.vehicles.length" class="loading-state">
      <span class="material-symbols-rounded spinning" style="font-size: 40px; color: var(--md-primary);">progress_activity</span>
    </div>

    <!-- Empty State -->
    <div v-else-if="vehicleStore.vehicles.length === 0" class="empty-state animate-in">
      <div class="empty-icon">
        <span class="material-symbols-rounded" style="font-size: 64px;">garage</span>
      </div>
      <h2>Nenhum veículo cadastrado</h2>
      <p>Adicione seu primeiro veículo para começar a acompanhar seus custos.</p>
      <button @click="openModal()" class="md-btn-filled" style="margin-top: 16px;">
        <span class="material-symbols-rounded">add</span>
        Adicionar Veículo
      </button>
    </div>

    <!-- Vehicle Cards -->
    <div v-else class="vehicle-grid">
      <div v-for="(vehicle, index) in vehicleStore.vehicles" :key="vehicle.id"
           class="vehicle-card md-card animate-in" :style="{ animationDelay: (index * 60) + 'ms' }">
        
        <!-- Header -->
        <div class="card-header">
          <div class="vehicle-identity">
            <div class="vehicle-icon">
              <span class="material-symbols-rounded">directions_car</span>
            </div>
            <div class="vehicle-text">
              <h3 class="vehicle-name">{{ vehicle.brand }} {{ vehicle.model }}</h3>
              <div class="vehicle-meta">
                <span>{{ vehicle.year }}</span>
                <span class="dot">•</span>
                <span class="plate">{{ vehicle.plate }}</span>
              </div>
            </div>
          </div>
          <span class="md-chip md-chip-primary">{{ vehicle.fuel_type }}</span>
        </div>

        <div class="card-divider"></div>

        <!-- Quick Actions Grid -->
        <div class="actions-grid">
          <router-link :to="`/vehicles/${vehicle.id}/fuel`" class="action-item">
            <div class="action-icon-bg fuel">
              <span class="material-symbols-rounded">local_gas_station</span>
            </div>
            <span class="action-label">Abastecer</span>
          </router-link>
          
          <router-link :to="`/vehicles/${vehicle.id}/maintenance`" class="action-item">
            <div class="action-icon-bg maintenance">
              <span class="material-symbols-rounded">build</span>
            </div>
            <span class="action-label">Manutenção</span>
          </router-link>
          
          <router-link :to="`/vehicles/${vehicle.id}/expenses`" class="action-item">
            <div class="action-icon-bg expense">
              <span class="material-symbols-rounded">receipt_long</span>
            </div>
            <span class="action-label">Despesas</span>
          </router-link> 
        </div>

        <!-- Footer Actions -->
        <div class="card-footer">
          <button @click="openFuelModal(vehicle)" class="md-btn-tonal full-width-btn">
             <span class="material-symbols-rounded">bolt</span>
             Lançamento Rápido
          </button>
          
          <div class="manage-actions">
            <button @click="openEditModal(vehicle)" class="icon-btn" title="Editar">
              <span class="material-symbols-rounded">edit</span>
            </button>
            <button @click="confirmDelete(vehicle)" class="icon-btn delete" title="Excluir">
              <span class="material-symbols-rounded">delete</span>
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- Mobile FAB -->
    <button @click="openModal()" class="md-fab mobile-only">
      <span class="material-symbols-rounded">add</span>
    </button>

    <!-- Vehicle Modal (Create/Edit) -->
    <ModalDialog :show="showVehicleModal" :title="editingVehicleId ? 'Editar Veículo' : 'Novo Veículo'" @close="showVehicleModal = false">
      <form @submit.prevent="handleSaveVehicle" class="modal-form">
        <div class="form-grid">
          <div class="md-input-group">
            <label>Marca</label>
            <input type="text" v-model="vehicleForm.brand" required class="md-input-outlined" placeholder="Ex: Honda">
          </div>
          <div class="md-input-group">
            <label>Modelo</label>
            <input type="text" v-model="vehicleForm.model" required class="md-input-outlined" placeholder="Ex: Civic">
          </div>
          <div class="md-input-group">
            <label>Ano</label>
            <input type="number" v-model="vehicleForm.year" required class="md-input-outlined" placeholder="2024">
          </div>
          <div class="md-input-group">
            <label>Placa</label>
            <input type="text" v-model="vehicleForm.plate" required class="md-input-outlined" placeholder="ABC1D23">
          </div>
        </div>
        <div class="md-input-group" style="margin-top: 16px;">
          <label>Tipo de Combustível</label>
          <select v-model="vehicleForm.fuel_type" required class="md-select">
            <option value="Gasolina">Gasolina</option>
            <option value="Etanol">Etanol</option>
            <option value="Flex">Flex</option>
            <option value="Diesel">Diesel</option>
          </select>
        </div>
        <div v-if="vehicleStore.errors.length" class="form-error animate-in" style="margin-top: 16px;">
          <span class="material-symbols-rounded" style="font-size: 18px;">error</span>
          {{ vehicleStore.errors[0] }}
        </div>
        <div class="modal-actions">
          <button type="button" @click="showVehicleModal = false" class="md-btn-outlined">Cancelar</button>
          <button type="submit" :disabled="vehicleStore.loading" class="md-btn-filled">
            <span v-if="vehicleStore.loading" class="material-symbols-rounded spinning">progress_activity</span>
            {{ vehicleStore.loading ? 'Salvando...' : (editingVehicleId ? 'Atualizar' : 'Salvar') }}
          </button>
        </div>
      </form>
    </ModalDialog>

    <!-- Quick Fuel Modal -->
    <ModalDialog :show="showFuelModal" :title="'Abastecimento Rápido' + (selectedVehicle ? ' — ' + selectedVehicle.brand + ' ' + selectedVehicle.model : '')" @close="showFuelModal = false">
      <form @submit.prevent="handleQuickFuel" class="modal-form">
        <div class="form-grid">
          <div class="md-input-group">
            <label>Data</label>
            <input type="date" v-model="fuelForm.date" required class="md-input-outlined">
          </div>
          <div class="md-input-group">
            <label>Posto</label>
            <input type="text" v-model="fuelForm.station" class="md-input-outlined" placeholder="Nome do posto">
          </div>
          <div class="md-input-group">
            <label>Combustível</label>
            <select v-model="fuelForm.fuel_type" required class="md-select">
              <option value="Gasolina">Gasolina</option>
              <option value="Etanol">Etanol</option>
              <option value="Diesel">Diesel</option>
            </select>
          </div>
          <div class="md-input-group">
            <label>Odômetro (km)</label>
            <input type="number" v-model="fuelForm.odometer" required class="md-input-outlined" placeholder="Km atual">
          </div>
          <div class="md-input-group">
            <label>Preço/Litro (R$)</label>
            <input type="number" step="0.01" v-model="fuelForm.price_per_liter" required @input="calcTotal" class="md-input-outlined" placeholder="0.00">
          </div>
          <div class="md-input-group">
            <label>Litros</label>
            <input type="number" step="0.01" v-model="fuelForm.liters" required @input="calcTotal" class="md-input-outlined" placeholder="0.00">
          </div>
        </div>
        <div class="fuel-total" v-if="fuelForm.total_value">
          <span class="material-symbols-rounded">payments</span>
          <strong>R$ {{ parseFloat(fuelForm.total_value).toFixed(2) }}</strong>
        </div>
        <div class="modal-actions">
          <button type="button" @click="showFuelModal = false" class="md-btn-outlined">Cancelar</button>
          <button type="submit" :disabled="fuelLoading" class="md-btn-filled">
            <span v-if="fuelLoading" class="material-symbols-rounded spinning">progress_activity</span>
            {{ fuelLoading ? 'Salvando...' : 'Lançar' }}
          </button>
        </div>
      </form>
    </ModalDialog>

    <!-- Delete Confirmation -->
    <ConfirmModal
      :show="showDeleteModal"
      title="Excluir Veículo"
      :message="`Tem certeza que deseja excluir ${vehicleToDelete?.brand} ${vehicleToDelete?.model}? Todos os abastecimentos e manutenções também serão removidos.`"
      confirmText="Sim, Excluir"
      @close="showDeleteModal = false"
      @confirm="handleDelete"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useVehicleStore } from '../stores/vehicle';
import { useFuelStore } from '../stores/fuel';
import ModalDialog from '../components/ModalDialog.vue';
import ConfirmModal from '../components/ConfirmModal.vue';

const vehicleStore = useVehicleStore();
const fuelStore = useFuelStore();

const showVehicleModal = ref(false);
const showFuelModal = ref(false);
const showDeleteModal = ref(false);
const selectedVehicle = ref(null);
const vehicleToDelete = ref(null);
const fuelLoading = ref(false);
const editingVehicleId = ref(null);

const vehicleForm = ref({ brand: '', model: '', year: new Date().getFullYear(), plate: '', fuel_type: 'Flex' });
const fuelForm = ref({
  vehicle_id: '', date: new Date().toISOString().split('T')[0],
  station: '', fuel_type: 'Gasolina', price_per_liter: '', liters: '',
  total_value: '', odometer: '', is_full_tank: true,
});

const openModal = () => {
  editingVehicleId.value = null;
  vehicleForm.value = { brand: '', model: '', year: new Date().getFullYear(), plate: '', fuel_type: 'Flex' };
  vehicleStore.errors = [];
  showVehicleModal.value = true;
};

const openEditModal = (vehicle) => {
  editingVehicleId.value = vehicle.id;
  vehicleForm.value = {
    brand: vehicle.brand,
    model: vehicle.model,
    year: vehicle.year,
    plate: vehicle.plate,
    fuel_type: vehicle.fuel_type,
  };
  vehicleStore.errors = [];
  showVehicleModal.value = true;
};

const openFuelModal = (vehicle) => {
  selectedVehicle.value = vehicle;
  fuelForm.value = {
    vehicle_id: vehicle.id, date: new Date().toISOString().split('T')[0],
    station: '', fuel_type: vehicle.fuel_type === 'Flex' ? 'Gasolina' : vehicle.fuel_type,
    price_per_liter: '', liters: '', total_value: '', odometer: '', is_full_tank: true,
  };
  showFuelModal.value = true;
};

const confirmDelete = (vehicle) => {
  vehicleToDelete.value = vehicle;
  showDeleteModal.value = true;
};

const handleDelete = async () => {
  if (vehicleToDelete.value) {
    await vehicleStore.deleteVehicle(vehicleToDelete.value.id);
    showDeleteModal.value = false;
    vehicleToDelete.value = null;
  }
};

const calcTotal = () => {
  if (fuelForm.value.price_per_liter && fuelForm.value.liters) {
    fuelForm.value.total_value = (parseFloat(fuelForm.value.price_per_liter) * parseFloat(fuelForm.value.liters)).toFixed(2);
  }
};

const handleSaveVehicle = async () => {
  let success;
  if (editingVehicleId.value) {
    success = await vehicleStore.updateVehicle(editingVehicleId.value, vehicleForm.value);
  } else {
    success = await vehicleStore.createVehicle(vehicleForm.value);
  }
  if (success) {
    showVehicleModal.value = false;
    if (!editingVehicleId.value) await vehicleStore.fetchVehicles();
  }
};

const handleQuickFuel = async () => {
  fuelLoading.value = true;
  const success = await fuelStore.createFuelEntry(fuelForm.value);
  fuelLoading.value = false;
  if (success) showFuelModal.value = false;
};

onMounted(() => vehicleStore.fetchVehicles());
</script>

<style scoped>
.vehicle-page { display: flex; flex-direction: column; gap: 24px; padding-bottom: 80px; }
.page-header {
  display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;
}
.page-title { font-size: 28px; font-weight: 700; color: var(--md-on-surface); }
.page-subtitle { font-size: 14px; color: var(--md-on-surface-variant); margin-top: 2px; }

.loading-state { display: flex; justify-content: center; padding: 48px; }

.empty-state {
  text-align: center; padding: 64px 24px;
  background: var(--md-surface-container-lowest); border-radius: 28px;
  box-shadow: var(--md-elevation-1);
}
.empty-icon {
  width: 96px; height: 96px; border-radius: 50%;
  background: var(--md-surface-container); color: var(--md-on-surface-variant);
  display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;
}
.empty-state h2 { font-size: 20px; font-weight: 600; margin-bottom: 8px; }
.empty-state p { font-size: 14px; color: var(--md-on-surface-variant); max-width: 360px; margin: 0 auto; }

/* Vehicle Config */
.vehicle-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 24px;
}
.vehicle-card {
  padding: 0; display: flex; flex-direction: column;
  background: var(--md-surface-container-lowest);
  border-radius: 24px;
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
}
.vehicle-card:hover { transform: translateY(-4px); box-shadow: var(--md-elevation-3); }
.card-divider { height: 1px; background: var(--md-outline-variant); margin: 0; opacity: 0.5; }

/* Header */
.card-header {
  padding: 24px 24px 16px;
  display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;
}
.vehicle-identity { display: flex; align-items: center; gap: 16px; }
.vehicle-icon {
  width: 56px; height: 56px; border-radius: 20px;
  background: var(--md-primary-container); color: var(--md-on-primary-container);
  display: flex; align-items: center; justify-content: center;
  font-size: 28px; flex-shrink: 0;
}
.vehicle-text { display: flex; flex-direction: column; gap: 4px; }
.vehicle-name { font-size: 18px; font-weight: 700; color: var(--md-on-surface); line-height: 1.2; }
.vehicle-meta {
  display: flex; align-items: center; gap: 6px;
  font-size: 13px; color: var(--md-on-surface-variant);
}
.dot { font-weight: bold; opacity: 0.5; }
.plate { font-family: 'Roboto Mono', monospace; letter-spacing: 0.5px; background: var(--md-surface-container); padding: 2px 6px; border-radius: 4px; font-size: 12px; }

/* Action Grid */
.actions-grid {
  display: grid; grid-template-columns: 1fr 1fr 1fr;
  padding: 16px 24px; gap: 16px;
}
.action-item {
  display: flex; flex-direction: column; align-items: center; gap: 8px;
  text-decoration: none; color: var(--md-on-surface);
  transition: opacity 0.2s;
}
.action-item:hover { opacity: 0.8; }
.action-icon-bg {
  width: 48px; height: 48px; border-radius: 16px;
  display: flex; align-items: center; justify-content: center;
  color: white; font-size: 24px;
}
.action-icon-bg.fuel { background: linear-gradient(135deg, #3b82f6, #2563eb); box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2); }
.action-icon-bg.maintenance { background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 4px 6px -1px rgba(217, 119, 6, 0.2); }
.action-icon-bg.expense { background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.2); }
.action-label { font-size: 12px; font-weight: 600; color: var(--md-on-surface-variant); }

/* Footer */
.card-footer {
  padding: 16px 24px 24px;
  display: flex; align-items: center; gap: 12px;
}
.full-width-btn {
  flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px;
  padding: 10px; border-radius: 12px; font-size: 14px; font-weight: 600;
  background: var(--md-secondary-container); color: var(--md-on-secondary-container);
  border: none; cursor: pointer; transition: all 0.2s;
}
.full-width-btn:hover { filter: brightness(0.95); }
.manage-actions { display: flex; gap: 4px; }
.icon-btn {
  width: 40px; height: 40px; border: none; background: transparent;
  border-radius: 50%; color: var(--md-on-surface-variant);
  display: flex; align-items: center; justify-content: center; cursor: pointer;
  transition: background 0.2s;
}
.icon-btn:hover { background: rgba(0,0,0,0.05); }
.icon-btn.delete:hover { background: var(--md-error-container); color: var(--md-on-error-container); }

/* --- Modal Form --- */
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

.mobile-only { display: none; }
.spinning { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 600px) {
  .page-header .md-btn-filled { display: none; }
  .mobile-only { display: flex; }
  .vehicle-grid { grid-template-columns: 1fr; }
  .form-grid { grid-template-columns: 1fr; }
}
</style>
