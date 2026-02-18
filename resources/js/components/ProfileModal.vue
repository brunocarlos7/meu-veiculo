<template>
  <div v-if="show" class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-surface">
      <div class="modal-header-hero">
        <button @click="$emit('close')" class="modal-close-hero">
          <span class="material-symbols-rounded">close</span>
        </button>
        <div class="hero-content">
          <div class="large-avatar">
            {{ userInitial }}
          </div>
          <h2 class="hero-title">{{ authStore.user?.name }}</h2>
          <p class="hero-subtitle">{{ authStore.user?.email }}</p>
        </div>
      </div>
      
      <div class="modal-body">
        <!-- Tabs -->
        <div class="tabs-container">
          <div class="tabs">
            <button 
              @click="activeTab = 'info'" 
              class="tab-btn" 
              :class="{ active: activeTab === 'info' }">
              <span class="material-symbols-rounded">person</span>
              Dados Pessoais
            </button>
            <button 
              @click="activeTab = 'password'" 
              class="tab-btn" 
              :class="{ active: activeTab === 'password' }">
              <span class="material-symbols-rounded">lock</span>
              Segurança
            </button>
          </div>
        </div>

        <!-- Info Tab -->
        <form v-if="activeTab === 'info'" @submit.prevent="handleUpdateProfile" class="tab-content animate-fade">
          <div class="md-input-group">
            <label>Nome Completo</label>
            <input type="text" v-model="profileForm.name" required class="md-input-outlined">
          </div>
          <div class="md-input-group">
            <label>E-mail</label>
            <input type="email" v-model="profileForm.email" required class="md-input-outlined">
          </div>
          <div class="form-actions">
            <button type="submit" :disabled="authStore.loading" class="md-btn-filled full-width">
              <span v-if="authStore.loading" class="material-symbols-rounded spinning">progress_activity</span>
              {{ authStore.loading ? 'Salvando...' : 'Salvar Alterações' }}
            </button>
          </div>
        </form>

        <!-- Password Tab -->
        <form v-if="activeTab === 'password'" @submit.prevent="handleUpdatePassword" class="tab-content animate-fade">
          <div class="md-input-group">
            <label>Senha Atual</label>
            <input type="password" v-model="passwordForm.current_password" required class="md-input-outlined">
          </div>
          <div class="row-inputs">
            <div class="md-input-group">
              <label>Nova Senha</label>
              <input type="password" v-model="passwordForm.password" required class="md-input-outlined" minlength="8">
            </div>
            <div class="md-input-group">
              <label>Confirmar Nova Senha</label>
              <input type="password" v-model="passwordForm.password_confirmation" required class="md-input-outlined" minlength="8">
            </div>
          </div>
          <div class="form-actions">
            <button type="submit" :disabled="authStore.loading" class="md-btn-filled full-width">
              <span v-if="authStore.loading" class="material-symbols-rounded spinning">progress_activity</span>
              {{ authStore.loading ? 'Alterando...' : 'Atualizar Senha' }}
            </button>
          </div>
        </form>

        <!-- Feedback -->
        <div v-if="successMessage" class="feedback-msg success animate-fade">
          <span class="material-symbols-rounded">check_circle</span>
          {{ successMessage }}
        </div>
        <div v-if="authStore.errors.length" class="feedback-msg error animate-fade">
          <span class="material-symbols-rounded">error</span>
          {{ authStore.errors[0] }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { useAuthStore } from '../stores/auth';

const props = defineProps({ show: Boolean });
const emit = defineEmits(['close']);
const authStore = useAuthStore();
const activeTab = ref('info');
const successMessage = ref('');

const profileForm = ref({ name: '', email: '' });
const passwordForm = ref({ current_password: '', password: '', password_confirmation: '' });

const userInitial = computed(() => authStore.user?.name?.charAt(0)?.toUpperCase() || '?');

// Reset form when modal opens
watch(() => props.show, async (newVal) => {
  if (newVal) {
    if (!authStore.user) await authStore.fetchUser();
    if (authStore.user) {
      profileForm.value = {
        name: authStore.user.name,
        email: authStore.user.email
      };
    }
    passwordForm.value = { current_password: '', password: '', password_confirmation: '' };
    authStore.errors = [];
    successMessage.value = '';
    activeTab.value = 'info';
  }
});

const handleUpdateProfile = async () => {
    successMessage.value = '';
    const success = await authStore.updateProfile(profileForm.value);
    if (success) {
        successMessage.value = 'Perfil atualizado!';
        setTimeout(() => successMessage.value = '', 3000);
    }
};

const handleUpdatePassword = async () => {
    successMessage.value = '';
    const success = await authStore.updatePassword(passwordForm.value);
    if (success) {
        successMessage.value = 'Senha alterada!';
        passwordForm.value = { current_password: '', password: '', password_confirmation: '' };
        setTimeout(() => successMessage.value = '', 3000);
    }
};
</script>

<style scoped>
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.6);
  display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 16px;
  backdrop-filter: blur(4px);
}
.modal-surface {
  background: var(--md-surface);
  border-radius: 28px;
  box-shadow: var(--md-elevation-5);
  width: 100%; max-width: 600px;
  display: flex; flex-direction: column; overflow: hidden;
  position: relative;
}

/* Hero Header */
.modal-header-hero {
  background: var(--md-primary-container);
  color: var(--md-on-primary-container);
  padding: 32px 24px 24px;
  text-align: center;
  position: relative;
}
.modal-close-hero {
  position: absolute; top: 16px; right: 16px;
  width: 32px; height: 32px; border: none; background: rgba(0,0,0,0.1);
  border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;
  color: inherit; transition: background 0.2s;
}
.modal-close-hero:hover { background: rgba(0,0,0,0.2); }

.hero-content { display: flex; flex-direction: column; align-items: center; gap: 8px; }
.large-avatar {
  width: 80px; height: 80px; border-radius: 50%;
  background: var(--md-primary); color: var(--md-on-primary);
  font-size: 32px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 8px rgba(0,0,0,0.15);
  margin-bottom: 8px;
}
.hero-title { font-size: 22px; font-weight: 700; margin: 0; }
.hero-subtitle { font-size: 14px; opacity: 0.8; margin: 0; }

.modal-body { padding: 24px; }

/* Tabs */
.tabs-container { margin-bottom: 24px; display: flex; justify-content: center; }
.tabs {
  display: flex; background: var(--md-surface-container-high);
  padding: 4px; border-radius: 100px;
}
.tab-btn {
  display: flex; align-items: center; gap: 8px;
  padding: 10px 24px; border: none; background: none;
  font-size: 14px; font-weight: 600; color: var(--md-on-surface-variant);
  cursor: pointer; border-radius: 100px; transition: all 0.2s;
}
.tab-btn.active {
  background: var(--md-surface); color: var(--md-primary);
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.tab-btn:hover:not(.active) { color: var(--md-on-surface); }

/* Form */
.tab-content { display: flex; flex-direction: column; gap: 16px; }
.row-inputs { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

.full-width { width: 100%; justify-content: center; padding: 12px; font-size: 15px; }

.feedback-msg {
  display: flex; align-items: center; gap: 8px; padding: 12px;
  border-radius: 12px; font-size: 14px; margin-top: 16px;
  justify-content: center;
}
.feedback-msg.success { background: #dcfce7; color: #166534; }
.feedback-msg.error { background: #fee2e2; color: #991b1b; }

/* Animations */
.animate-pop { animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
@keyframes popIn {
  from { opacity: 0; transform: scale(0.9) translateY(20px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}
.animate-fade { animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
.spinning { animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
