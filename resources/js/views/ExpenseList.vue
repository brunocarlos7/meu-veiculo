<template>
  <div class="expense-page">
    <div class="page-header animate-in">
      <div class="page-header-left">
        <button @click="$router.push('/vehicles')" class="back-btn">
          <span class="material-symbols-rounded">arrow_back</span>
        </button>
        <div>
          <h1 class="page-title">Despesas</h1>
          <p class="page-subtitle">{{ expenseStore.expenses.length }} registro(s)</p>
        </div>
      </div>
      <button @click="openModal()" class="md-btn-filled">
        <span class="material-symbols-rounded">add</span>
        Nova Despesa
      </button>
    </div>

    <!-- Loading -->
    <div v-if="expenseStore.loading && !expenseStore.expenses.length" class="loading-state">
      <span class="material-symbols-rounded spinning" style="font-size: 40px; color: var(--md-primary);">progress_activity</span>
    </div>

    <!-- Empty -->
    <div v-else-if="expenseStore.expenses.length === 0" class="empty-state animate-in">
      <div class="empty-icon">
        <span class="material-symbols-rounded" style="font-size: 64px;">receipt_long</span>
      </div>
      <h2>Nenhuma despesa</h2>
      <p>Registre despesas como IPVA, seguro, pedágios e outros custos do veículo.</p>
      <button @click="openModal()" class="md-btn-filled" style="margin-top: 16px;">
        <span class="material-symbols-rounded">add</span>
        Registrar Despesa
      </button>
    </div>

    <!-- List -->
    <div v-else class="entries-list">
      <div v-for="(item, index) in expenseStore.expenses" :key="item.id"
           class="entry-card md-card animate-in" :style="{ animationDelay: (index * 50) + 'ms' }">
        <div class="entry-main">
          <div class="entry-icon">
            <span class="material-symbols-rounded">receipt_long</span>
          </div>
          <div class="entry-info">
            <span class="entry-title">{{ item.type }}</span>
            <span class="entry-date">{{ new Date(item.date).toLocaleDateString('pt-BR') }}</span>
          </div>
          <div class="entry-cost">
            <span class="entry-total">R$ {{ parseFloat(item.cost).toFixed(2) }}</span>
          </div>
        </div>
        <div class="entry-footer">
          <span class="entry-notes">{{ item.notes || '' }}</span>
          <button @click="expenseStore.deleteExpense(item.id)" class="md-btn-text" style="color: var(--md-error); padding: 6px 12px;">
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
    <ModalDialog :show="showModal" title="Nova Despesa" @close="showModal = false">
      <form @submit.prevent="handleSubmit" class="modal-form">
        <div class="form-grid">
          <div class="md-input-group">
            <label>Tipo</label>
            <select v-model="form.type" required class="md-select">
              <option value="IPVA">IPVA</option>
              <option value="Seguro">Seguro</option>
              <option value="Licenciamento">Licenciamento</option>
              <option value="Estacionamento">Estacionamento</option>
              <option value="Pedágio">Pedágio</option>
              <option value="Lavagem">Lavagem</option>
              <option value="Multa">Multa</option>
              <option value="Outro">Outro</option>
            </select>
          </div>
          <div class="md-input-group">
            <label>Data</label>
            <input type="date" v-model="form.date" required class="md-input-outlined">
          </div>
          <div class="md-input-group">
            <label>Valor (R$)</label>
            <input type="number" step="0.01" v-model="form.cost" required class="md-input-outlined" placeholder="0.00">
          </div>
          <div class="md-input-group">
            <label>Observações</label>
            <input type="text" v-model="form.notes" class="md-input-outlined" placeholder="Detalhes da despesa">
          </div>
        </div>
        <div v-if="expenseStore.errors.length" class="form-error animate-in" style="margin-top: 16px;">
          <span class="material-symbols-rounded" style="font-size: 18px;">error</span>
          {{ expenseStore.errors[0] }}
        </div>
        <div class="modal-actions">
          <button type="button" @click="showModal = false" class="md-btn-outlined">Cancelar</button>
          <button type="submit" :disabled="expenseStore.loading" class="md-btn-filled">
            <span v-if="expenseStore.loading" class="material-symbols-rounded spinning">progress_activity</span>
            {{ expenseStore.loading ? 'Salvando...' : 'Salvar' }}
          </button>
        </div>
      </form>
    </ModalDialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useExpenseStore } from '../stores/expense';
import ModalDialog from '../components/ModalDialog.vue';

const route = useRoute();
const expenseStore = useExpenseStore();
const vehicleId = route.params.id;
const showModal = ref(false);

const defaultForm = () => ({
  vehicle_id: vehicleId,
  type: 'IPVA',
  date: new Date().toISOString().split('T')[0],
  cost: '', notes: '',
});
const form = ref(defaultForm());

const openModal = () => {
  form.value = defaultForm();
  expenseStore.errors = [];
  showModal.value = true;
};

const handleSubmit = async () => {
  const success = await expenseStore.createExpense(form.value);
  if (success) {
    showModal.value = false;
    expenseStore.fetchExpenses(vehicleId);
  }
};

onMounted(() => expenseStore.fetchExpenses(vehicleId));
</script>

<style scoped>
.expense-page { display: flex; flex-direction: column; gap: 24px; }
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

.entries-list { display: flex; flex-direction: column; gap: 12px; }
.entry-card { padding: 0; }
.entry-main { display: flex; align-items: center; gap: 16px; padding: 16px 20px; }
.entry-icon {
  width: 40px; height: 40px; border-radius: var(--md-shape-md);
  background: var(--md-primary-container); color: var(--md-on-primary-container);
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.entry-info { flex: 1; min-width: 0; }
.entry-title { display: block; font-weight: 600; font-size: 14px; }
.entry-date { font-size: 12px; color: var(--md-on-surface-variant); }
.entry-cost { text-align: right; flex-shrink: 0; }
.entry-total { display: block; font-weight: 700; font-size: 16px; color: var(--md-primary); }
.entry-footer {
  display: flex; align-items: center; justify-content: space-between;
  padding: 8px 12px 8px 20px; border-top: 1px solid var(--md-outline-variant);
}
.entry-notes { font-size: 13px; color: var(--md-on-surface-variant); }

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
  .entry-main { flex-wrap: wrap; gap: 12px; }
  .entry-cost { width: 100%; text-align: left; }
}
</style>
