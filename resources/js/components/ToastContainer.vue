<template>
  <div class="toast-container">
    <TransitionGroup name="toast">
      <div 
        v-for="toast in toasts" 
        :key="toast.id" 
        class="toast-item"
        :class="toast.type"
      >
        <div class="toast-icon">
          <span class="material-symbols-rounded">{{ getIcon(toast.type) }}</span>
        </div>
        <div class="toast-content">
          {{ toast.message }}
        </div>
        <button @click="remove(toast.id)" class="toast-close">
          <span class="material-symbols-rounded">close</span>
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { useToast } from '../composables/useToast';

const { toasts, remove } = useToast();

const getIcon = (type) => {
  switch (type) {
    case 'success': return 'check_circle';
    case 'error': return 'error';
    case 'warning': return 'warning';
    default: return 'info';
  }
};
</script>

<style scoped>
.toast-container {
  position: fixed;
  top: 24px;
  right: 24px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-width: 400px;
  width: 100%;
  pointer-events: none;
}

.toast-item {
  pointer-events: auto;
  border-radius: var(--md-shape-md);
  padding: 14px 16px;
  box-shadow: var(--md-elevation-3);
  display: flex;
  align-items: flex-start;
  gap: 12px;
  min-width: 300px;
  background: var(--md-surface-container);
  color: var(--md-on-surface);
  transition: all 0.3s cubic-bezier(0.2, 0, 0, 1);
}

/* Variants - Material Design 3 Container Styles */
.toast-item.success {
  background: #dcfce7; /* Green Container */
  color: #14532d;      /* On Green Container */
}
.toast-item.success .toast-icon { color: #15803d; }

.toast-item.error {
  background: var(--md-error-container);
  color: var(--md-on-error-container);
}
.toast-item.error .toast-icon { color: var(--md-error); }

.toast-item.warning {
  background: #fef3c7; /* Amber Container */
  color: #78350f;      /* On Amber Container */
}
.toast-item.warning .toast-icon { color: #d97706; }

.toast-item.info {
  background: var(--md-secondary-container);
  color: var(--md-on-secondary-container);
}
.toast-item.info .toast-icon { color: var(--md-primary); }

.toast-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-top: 2px;
}

.toast-content {
  flex: 1;
  font-size: 14px;
  line-height: 1.4;
  font-weight: 500;
}

.toast-close {
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  margin: -4px -4px -4px 0;
  border-radius: 50%;
  display: flex;
  opacity: 0.6;
  color: inherit;
  transition: all 0.2s;
}

.toast-close:hover {
  background: rgba(0,0,0,0.08);
  opacity: 1;
}

/* Animations - Slide & Fade */
.toast-enter-active {
  animation: toastIn 0.4s cubic-bezier(0.2, 0, 0, 1);
}
.toast-leave-active {
  animation: toastOut 0.3s cubic-bezier(0.2, 0, 0, 1);
  position: absolute; /* Take out of flow for smooth stack collapse */
}
.toast-move {
  transition: transform 0.4s cubic-bezier(0.2, 0, 0, 1);
}

@keyframes toastIn {
  from { opacity: 0; transform: translateX(40px) scale(0.95); }
  to { opacity: 1; transform: translateX(0) scale(1); }
}
@keyframes toastOut {
  from { opacity: 1; transform: translateX(0) scale(1); }
  to { opacity: 0; transform: translateX(40px) scale(0.95); }
}
</style>
