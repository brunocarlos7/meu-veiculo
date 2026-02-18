<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="modal-overlay" @click.self="$emit('close')">
        <div class="modal-surface" :class="sizeClass">
          <!-- Header -->
          <div class="modal-header">
            <h2 class="modal-title">{{ title }}</h2>
            <button @click="$emit('close')" class="modal-close">
              <span class="material-symbols-rounded">close</span>
            </button>
          </div>
          <!-- Body -->
          <div class="modal-body">
            <slot></slot>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue';
const props = defineProps({
  show: Boolean,
  title: { type: String, default: '' },
  size: { type: String, default: 'md' },
});
defineEmits(['close']);
const sizeClass = computed(() => `modal-${props.size}`);
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 16px;
  backdrop-filter: blur(4px);
}
.modal-surface {
  background: var(--md-surface);
  border-radius: 28px;
  box-shadow: var(--md-elevation-5);
  width: 100%;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  position: relative;
}
.modal-sm { max-width: 400px; }
.modal-md { max-width: 600px; }
.modal-lg { max-width: 800px; }

/* Header */
.modal-header {
  background: var(--md-primary-container);
  color: var(--md-on-primary-container);
  padding: 32px 24px 24px;
  text-align: center;
  position: relative;
}

.modal-title { 
  font-size: 22px; 
  font-weight: 700; 
  color: var(--md-on-primary-container);
  margin: 0;
}

.modal-close {
  position: absolute; 
  top: 16px; 
  right: 16px;
  width: 32px; 
  height: 32px; 
  border: none; 
  background: rgba(0,0,0,0.1);
  border-radius: 50%; 
  cursor: pointer; 
  display: flex; 
  align-items: center; 
  justify-content: center;
  color: inherit; 
  transition: background 0.2s;
}

.modal-close:hover { 
  background: rgba(0,0,0,0.2); 
}

.modal-body {
  padding: 24px;
  overflow-y: auto;
  flex: 1;
}

/* Animations - Pop In */
.modal-enter-active { animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.modal-leave-active { animation: fadeOut 0.2s ease; }

@keyframes popIn {
  from { opacity: 0; transform: scale(0.9) translateY(20px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}
@keyframes fadeOut {
  from { opacity: 1; transform: scale(1); }
  to { opacity: 0; transform: scale(0.98); }
}

@media (max-width: 600px) {
  .modal-overlay { align-items: flex-end; padding: 0; }
  .modal-surface {
    max-width: 100%;
    border-radius: 28px 28px 0 0;
    max-height: 92vh;
  }
  .modal-enter-active { animation: slideUp 0.3s cubic-bezier(0.2, 0, 0, 1); }
  .modal-leave-active { animation: slideDown 0.2s cubic-bezier(0.2, 0, 0, 1); }
}

@keyframes slideUp {
  from { transform: translateY(100%); }
  to { transform: translateY(0); }
}
@keyframes slideDown {
  from { transform: translateY(0); }
  to { transform: translateY(100%); }
}
</style>
