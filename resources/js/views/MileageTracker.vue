<template>
  <div class="mileage-page">
    <div class="page-header animate-in">
      <div class="page-header-left">
        <button @click="$router.push('/vehicles')" class="back-btn">
          <span class="material-symbols-rounded">arrow_back</span>
        </button>
        <div>
          <h1 class="page-title">Controle de KM</h1>
          <p class="page-subtitle animate-in" style="animation-delay: 100ms">
            {{ mileageStore.mileageEntries.length }} registro(s)
          </p>
        </div>
      </div>
      <button @click="openModal()" class="md-btn-filled animate-in" style="animation-delay: 200ms">
        <span class="material-symbols-rounded">add</span>
        Novo Registro
      </button>
    </div>

    <!-- Loading -->
    <div v-if="mileageStore.loading && !mileageStore.mileageEntries.length" class="loading-state">
      <span class="material-symbols-rounded spinning" style="font-size: 40px; color: var(--md-primary);">progress_activity</span>
    </div>

    <!-- Empty State -->
    <div v-else-if="mileageStore.mileageEntries.length === 0" class="empty-state animate-in" style="animation-delay: 200ms">
      <div class="empty-icon">
        <span class="material-symbols-rounded" style="font-size: 64px;">speed</span>
      </div>
      <h2>Nenhum registro</h2>
      <p>Lance a kilometragem do dia para acompanhar o uso do veículo.</p>
      <button @click="openModal()" class="md-btn-filled" style="margin-top: 16px;">
        <span class="material-symbols-rounded">add</span>
        Lançar KM
      </button>
    </div>

    <!-- Mileage List -->
    <div v-else class="mileage-list">
      <div v-for="(entry, index) in sortedEntries" :key="entry.id"
           class="mileage-entry md-card animate-in" :style="{ animationDelay: (index * 50) + 'ms' }">
        
        <div class="mileage-entry-main">
          <div class="mileage-entry-icon">
            <span class="material-symbols-rounded">speed</span>
          </div>
          
          <div class="mileage-entry-info">
            <span class="mileage-entry-date">{{ formatDate(entry.date) }}</span>
            <div class="mileage-entry-notes" v-if="entry.notes">{{ entry.notes }}</div>
          </div>

          <div class="mileage-entry-stats">
            <span class="mileage-total">{{ entry.odometer }} km</span>
            <span class="mileage-delta" v-if="index > 0">
              +{{ entry.odometer - sortedEntries[index - 1].odometer }} km
            </span>
            <span class="mileage-delta first" v-else>Inicial</span>
          </div>
        </div>

        <div class="mileage-entry-footer">
          <div class="mileage-entry-tags">
             <!-- Placeholder -->
          </div>
          <div class="entry-actions">
            <button @click="openEditModal(entry)" class="md-btn-text" style="color: var(--md-on-surface-variant); padding: 6px;" title="Editar">
              <span class="material-symbols-rounded">edit</span>
            </button>
            <button @click="confirmDelete(entry)" class="md-btn-text delete-btn" title="Excluir">
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

    <!-- Modal Form -->
    <ModalDialog :show="showModal" :title="editingEntryId ? 'Editar Registro' : 'Registrar Kilometragem'" @close="showModal = false">
      <form @submit.prevent="submitMileage" class="modal-form">
        <div class="form-grid">
          <div class="md-input-group">
            <label>Data</label>
            <input type="date" v-model="form.date" required class="md-input-outlined">
          </div>
          <div class="md-input-group">
            <label>Odômetro (Km)</label>
            <input type="number" v-model="form.odometer" required min="0" class="md-input-outlined" placeholder="Ex: 50230">
          </div>
          <div class="md-input-group full-width">
            <label>Notas (Opcional)</label>
            <input type="text" v-model="form.notes" class="md-input-outlined" placeholder="Viagem, trabalho...">
          </div>
        </div>

        <div v-if="mileageStore.errors.length" class="form-error animate-in" style="margin-top: 16px;">
          <span class="material-symbols-rounded" style="font-size: 18px;">error</span>
          {{ mileageStore.errors[0] }}
        </div>

        <div class="modal-actions">
          <button type="button" @click="showModal = false" class="md-btn-outlined">Cancelar</button>
          <button type="submit" :disabled="mileageStore.loading" class="md-btn-filled">
            <span v-if="mileageStore.loading" class="material-symbols-rounded spinning">progress_activity</span>
            {{ mileageStore.loading ? 'Salvando...' : (editingEntryId ? 'Atualizar' : 'Salvar') }}
          </button>
        </div>
      </form>
    </ModalDialog>

    <ConfirmModal
      :show="showDeleteModal"
      title="Excluir Registro"
      message="Tem certeza que deseja excluir este registro?"
      confirmText="Sim, Excluir"
      @close="showDeleteModal = false"
      @confirm="handleDelete"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useMileageStore } from '../stores/mileage';
import ModalDialog from '../components/ModalDialog.vue';
import ConfirmModal from '../components/ConfirmModal.vue';

const route = useRoute();
const mileageStore = useMileageStore();
const vehicleId = route.params.id;

// Sort ascending for grid layout (Left=Initial, Right=Final)
const sortedEntries = computed(() => {
  return [...mileageStore.mileageEntries].sort((a, b) => new Date(a.date) - new Date(b.date));
});

const form = ref({
  date: new Date().toISOString().split('T')[0],
  odometer: '',
  notes: ''
});

const showModal = ref(false);
const showDeleteModal = ref(false);
const editingEntryId = ref(null);
const entryToDelete = ref(null);

onMounted(() => {
  mileageStore.fetchMileage(vehicleId);
});

function openModal() {
  editingEntryId.value = null;
  form.value = {
    date: new Date().toISOString().split('T')[0],
    odometer: '',
    notes: ''
  };
  mileageStore.errors = [];
  showModal.value = true;
}

function openEditModal(entry) {
  editingEntryId.value = entry.id;
  form.value = {
    date: entry.date.split('T')[0],
    odometer: entry.odometer,
    notes: entry.notes || ''
  };
  mileageStore.errors = [];
  showModal.value = true;
}

async function submitMileage() {
  let success;
  if (editingEntryId.value) {
    success = await mileageStore.updateMileage(editingEntryId.value, form.value);
  } else {
    success = await mileageStore.addMileage(vehicleId, form.value);
  }
  
  if (success) {
    showModal.value = false;
  }
}

function confirmDelete(entry) {
  entryToDelete.value = entry;
  showDeleteModal.value = true;
}

async function handleDelete() {
  if (entryToDelete.value) {
    await mileageStore.deleteMileage(entryToDelete.value.id);
    showDeleteModal.value = false;
    entryToDelete.value = null;
  }
}

function formatDate(dateString) {
  if (!dateString) return '';
  // Fix: Handle ISO strings properly
  const dateObj = new Date(dateString);
  // Check if date is valid
  if (isNaN(dateObj.getTime())) return dateString;
  
  // Adjust for timezone offset if needed, but UTC split is safer for YYYY-MM-DD
  // Since backend sends YYYY-MM-DD or ISO, let's just use the YYYY-MM-DD part
  const ymd = dateString.split('T')[0];
  const [year, month, day] = ymd.split('-');
  return `${day}/${month}/${year}`;
}
</script>

<style scoped>
.mileage-page { display: flex; flex-direction: column; gap: 24px; padding-bottom: 80px; }
.page-header {
  display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;
}
.page-header-left { display: flex; align-items: center; gap: 8px; }
.back-btn {
  width: 40px; height: 40px; border: none; background: none;
  border-radius: 50%; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  color: var(--md-on-surface-variant);
}
.back-btn:hover { background: rgba(0,0,0,0.05); }
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
.empty-state p { font-size: 14px; color: var(--md-on-surface-variant); }

/* List Styles */
/* List Styles */
.mileage-list {
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px;
}
@media (min-width: 800px) {
  .mileage-list {
    grid-template-columns: 1fr 1fr;
  }
}
.mileage-entry { padding: 0; display: flex; flex-direction: column; justify-content: space-between; }
.mileage-entry-main { display: flex; align-items: center; gap: 16px; padding: 16px 20px; }
.mileage-entry-icon {
  width: 40px; height: 40px; border-radius: 12px;
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
  color: white;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.mileage-entry-info { flex: 1; min-width: 0; }
.mileage-entry-date { display: block; font-weight: 600; font-size: 15px; }
.mileage-entry-notes { font-size: 12px; color: var(--md-on-surface-variant); margin-top: 2px; }

.mileage-entry-stats { text-align: right; flex-shrink: 0; display: flex; flex-direction: column; align-items: flex-end; }
.mileage-total { display: block; font-weight: 700; font-size: 16px; color: var(--md-on-surface); }
.mileage-delta { font-size: 12px; font-weight: 600; color: var(--md-primary); background: var(--md-primary-container); padding: 2px 6px; border-radius: 4px; margin-top: 4px; }
.mileage-delta.first { background: var(--md-surface-container); color: var(--md-on-surface-variant); }

.mileage-entry-footer {
  display: flex; align-items: center; justify-content: space-between;
  padding: 8px 12px 8px 20px; border-top: 1px solid var(--md-outline-variant);
}

.delete-btn { color: var(--md-error); padding: 6px; }

/* Modal */
.modal-form { display: flex; flex-direction: column; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.full-width { grid-column: 1 / -1; }
.modal-actions {
  display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; padding-top: 16px;
  border-top: 1px solid var(--md-outline-variant);
}

.form-error {
  display: flex; align-items: center; gap: 8px; padding: 12px 16px;
  background: var(--md-error-container); color: var(--md-on-error-container);
  border-radius: 8px; font-size: 14px;
}

.mobile-only { display: none; }
.spinning { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 600px) {
  .page-header .md-btn-filled { display: none; }
  .mobile-only { display: flex; }
  .form-grid { grid-template-columns: 1fr; }
}
</style>
