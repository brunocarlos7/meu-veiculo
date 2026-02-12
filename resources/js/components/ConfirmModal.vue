<template>
  <ModalDialog :show="show" :title="title" @close="$emit('close')" size="sm">
    <div class="confirm-content">
      <p class="confirm-message">{{ message }}</p>
      
      <div class="confirm-actions">
        <button @click="$emit('close')" class="md-btn-outlined">
          {{ cancelText }}
        </button>
        <button @click="$emit('confirm')" class="md-btn-filled" :class="confirmTypeClass">
          {{ confirmText }}
        </button>
      </div>
    </div>
  </ModalDialog>
</template>

<script setup>
import { computed } from 'vue';
import ModalDialog from './ModalDialog.vue';

const props = defineProps({
  show: Boolean,
  title: { type: String, default: 'Confirmação' },
  message: { type: String, required: true },
  confirmText: { type: String, default: 'Confirmar' },
  cancelText: { type: String, default: 'Cancelar' },
  type: { type: String, default: 'danger' } // danger, primary
});

defineEmits(['close', 'confirm']);

const confirmTypeClass = computed(() => {
  return props.type === 'danger' ? 'btn-danger' : 'btn-primary';
});
</script>

<style scoped>
.confirm-content {
  display: flex; flex-direction: column; gap: 24px;
}
.confirm-message {
  font-size: 16px; color: var(--md-on-surface-variant); line-height: 1.5;
}
.confirm-actions {
  display: flex; justify-content: flex-end; gap: 12px;
}
.btn-danger {
  background: var(--md-error); color: var(--md-on-error);
}
.btn-danger:hover {
  background: #b91c1c; /* Darker red */
  box-shadow: var(--md-elevation-2);
}
</style>
