<template>
  <div class="reminders-page">
    <div class="page-header animate-in">
      <div>
        <h1 class="page-title">Lembretes</h1>
        <p class="page-subtitle">{{ pendingCount }} pendente(s)</p>
      </div>
      <button @click="openModal()" class="md-btn-filled">
        <span class="material-symbols-rounded">add</span>
        Novo Lembrete
      </button>
    </div>

    <!-- Loading -->
    <div v-if="reminderStore.loading && !reminderStore.reminders.length" class="loading-state">
      <span class="material-symbols-rounded spinning" style="font-size: 40px; color: var(--md-primary);">progress_activity</span>
    </div>

    <!-- Empty -->
    <div v-else-if="reminderStore.reminders.length === 0" class="empty-state animate-in">
      <div class="empty-icon">
        <span class="material-symbols-rounded" style="font-size: 64px;">notifications</span>
      </div>
      <h2>Nenhum lembrete</h2>
      <p>Configure lembretes para troca de óleo, vencimento de IPVA, seguro e mais.</p>
      <button @click="openModal()" class="md-btn-filled" style="margin-top: 16px;">
        <span class="material-symbols-rounded">add</span>
        Criar Lembrete
      </button>
    </div>

    <!-- List -->
    <div v-else class="entries-list">
      <div v-for="(item, index) in reminderStore.reminders" :key="item.id"
           class="reminder-card md-card animate-in" :class="{ completed: item.is_completed }"
           :style="{ animationDelay: (index * 50) + 'ms' }">
        <div class="reminder-main">
          <button @click="reminderStore.toggleComplete(item)" class="check-btn" :class="{ checked: item.is_completed }">
            <span class="material-symbols-rounded">{{ item.is_completed ? 'check_circle' : 'radio_button_unchecked' }}</span>
          </button>
          <div class="reminder-info">
            <span class="reminder-title">{{ item.title }}</span>
            <span class="reminder-vehicle">{{ item.vehicle?.brand }} {{ item.vehicle?.model }}</span>
          </div>
          <div class="reminder-meta">
            <span v-if="item.due_date" class="md-chip" :class="isOverdue(item) ? 'md-chip-error' : 'md-chip-primary'">
              <span class="material-symbols-rounded" style="font-size: 14px;">calendar_today</span>
              {{ new Date(item.due_date).toLocaleDateString('pt-BR') }}
            </span>
            <span v-if="item.due_odometer" class="md-chip md-chip-tertiary">
              <span class="material-symbols-rounded" style="font-size: 14px;">speed</span>
              {{ item.due_odometer }} km
            </span>
          </div>
        </div>
        <div class="reminder-footer" v-if="item.notes">
          <span class="reminder-notes">{{ item.notes }}</span>
          <button @click="reminderStore.deleteReminder(item.id)" class="md-btn-text" style="color: var(--md-error); padding: 6px 12px;">
            <span class="material-symbols-rounded" style="font-size: 20px;">delete</span>
          </button>
        </div>
        <div class="reminder-footer" v-else>
          <span></span>
          <button @click="reminderStore.deleteReminder(item.id)" class="md-btn-text" style="color: var(--md-error); padding: 6px 12px;">
            <span class="material-symbols-rounded" style="font-size: 20px;">delete</span>
          </button>
        </div>
      </div>
    </div>

    <!-- FAB -->
    <button @click="openModal()" class="md-fab mobile-only">
      <span class="material-symbols-rounded">add</span>
    </button>

    <!-- Modal -->
    <ModalDialog :show="showModal" title="Novo Lembrete" @close="showModal = false">
      <form @submit.prevent="handleSubmit" class="modal-form">
        <div class="form-grid">
          <div class="md-input-group">
            <label>Veículo</label>
            <select v-model="form.vehicle_id" required class="md-select">
              <option v-for="v in vehicles" :key="v.id" :value="v.id">{{ v.brand }} {{ v.model }}</option>
            </select>
          </div>
          <div class="md-input-group">
            <label>Tipo</label>
            <select v-model="form.type" required class="md-select">
              <option value="oil_change">Troca de Óleo</option>
              <option value="ipva">IPVA</option>
              <option value="insurance">Seguro</option>
              <option value="licensing">Licenciamento</option>
              <option value="revision">Revisão</option>
              <option value="tire_change">Troca de Pneus</option>
              <option value="other">Outro</option>
            </select>
          </div>
          <div class="md-input-group" style="grid-column: 1 / -1;">
            <label>Título</label>
            <input type="text" v-model="form.title" required class="md-input-outlined" placeholder="Ex: Próxima troca de óleo">
          </div>
          <div class="md-input-group">
            <label>Data limite</label>
            <input type="date" v-model="form.due_date" class="md-input-outlined">
          </div>
          <div class="md-input-group">
            <label>Km limite</label>
            <input type="number" v-model="form.due_odometer" class="md-input-outlined" placeholder="Opcional">
          </div>
        </div>
        <div class="md-input-group" style="margin-top: 16px;">
          <label>Observações</label>
          <input type="text" v-model="form.notes" class="md-input-outlined" placeholder="Detalhes...">
        </div>
        <div v-if="reminderStore.errors.length" class="form-error animate-in" style="margin-top: 16px;">
          <span class="material-symbols-rounded" style="font-size: 18px;">error</span>
          {{ reminderStore.errors[0] }}
        </div>
        <div class="modal-actions">
          <button type="button" @click="showModal = false" class="md-btn-outlined">Cancelar</button>
          <button type="submit" :disabled="reminderStore.loading" class="md-btn-filled">
            <span v-if="reminderStore.loading" class="material-symbols-rounded spinning">progress_activity</span>
            {{ reminderStore.loading ? 'Salvando...' : 'Salvar' }}
          </button>
        </div>
      </form>
    </ModalDialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useReminderStore } from '../stores/reminder';
import { useVehicleStore } from '../stores/vehicle';
import ModalDialog from '../components/ModalDialog.vue';

const reminderStore = useReminderStore();
const vehicleStore = useVehicleStore();
const showModal = ref(false);

const vehicles = computed(() => vehicleStore.vehicles);
const pendingCount = computed(() => reminderStore.reminders.filter(r => !r.is_completed).length);

const isOverdue = (item) => {
  if (!item.due_date || item.is_completed) return false;
  return new Date(item.due_date) < new Date();
};

const defaultForm = () => ({
  vehicle_id: vehicles.value[0]?.id || '',
  type: 'oil_change',
  title: '',
  due_date: '', due_odometer: '', notes: '',
});
const form = ref(defaultForm());

const openModal = () => {
  form.value = defaultForm();
  reminderStore.errors = [];
  showModal.value = true;
};

const handleSubmit = async () => {
  const data = { ...form.value };
  if (!data.due_date) delete data.due_date;
  if (!data.due_odometer) delete data.due_odometer;
  const success = await reminderStore.createReminder(data);
  if (success) {
    showModal.value = false;
  }
};

onMounted(async () => {
  await vehicleStore.fetchVehicles();
  await reminderStore.fetchReminders();
});
</script>

<style scoped>
.reminders-page { display: flex; flex-direction: column; gap: 24px; }
.page-header {
  display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;
}
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

.entries-list { display: flex; flex-direction: column; gap: 12px; }
.reminder-card { padding: 0; transition: opacity 300ms; }
.reminder-card.completed { opacity: 0.55; }
.reminder-main { display: flex; align-items: center; gap: 12px; padding: 16px 20px; }

.check-btn {
  width: 36px; height: 36px; border: none; background: none;
  border-radius: var(--md-shape-full); cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  color: var(--md-on-surface-variant); flex-shrink: 0;
  transition: all 200ms;
}
.check-btn:hover { background: rgba(0,0,0,0.05); }
.check-btn.checked { color: var(--md-secondary); }

.reminder-info { flex: 1; min-width: 0; }
.reminder-title { display: block; font-weight: 600; font-size: 14px; }
.reminder-vehicle { font-size: 12px; color: var(--md-on-surface-variant); }

.reminder-meta { display: flex; gap: 8px; flex-shrink: 0; flex-wrap: wrap; }

.md-chip-error {
  background: var(--md-error-container); color: var(--md-on-error-container);
}

.reminder-footer {
  display: flex; align-items: center; justify-content: space-between;
  padding: 8px 12px 8px 20px; border-top: 1px solid var(--md-outline-variant);
}
.reminder-notes { font-size: 13px; color: var(--md-on-surface-variant); }

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

.mobile-only { display: none; }
.spinning { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 600px) {
  .page-header .md-btn-filled { display: none; }
  .mobile-only { display: flex; }
  .form-grid { grid-template-columns: 1fr; }
  .reminder-main { flex-wrap: wrap; gap: 12px; }
  .reminder-meta { width: 100%; }
}
</style>
