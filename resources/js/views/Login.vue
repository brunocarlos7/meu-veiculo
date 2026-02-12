<template>
  <div class="auth-page">
    <div class="auth-container animate-in">
      <!-- Brand Header -->
      <div class="auth-brand">
        <div class="auth-brand-icon">
          <span class="material-symbols-rounded" style="font-size: 40px;">directions_car</span>
        </div>
        <h1>Meu Veículo</h1>
        <p>Faça login para gerenciar seus veículos</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleLogin" class="auth-form">
        <div class="md-input-group">
          <label for="email">E-mail</label>
          <input id="email" type="email" v-model="form.email" required class="md-input-outlined" placeholder="seu@email.com">
        </div>
        
        <div class="md-input-group">
          <label for="password">Senha</label>
          <input id="password" type="password" v-model="form.password" required class="md-input-outlined" placeholder="Sua senha">
        </div>

        <div v-if="authStore.errors.length" class="auth-error animate-in">
          <span class="material-symbols-rounded" style="font-size: 18px;">error</span>
          {{ authStore.errors[0] }}
        </div>

        <button type="submit" :disabled="authStore.loading" class="md-btn-filled auth-submit">
          <span v-if="authStore.loading" class="material-symbols-rounded spinning">progress_activity</span>
          {{ authStore.loading ? 'Entrando...' : 'Entrar' }}
        </button>
      </form>

      <hr class="md-divider">

      <div class="auth-footer">
        <span>Não tem uma conta?</span>
        <router-link to="/register" class="md-btn-text">Criar conta</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();
const form = ref({ email: '', password: '' });

const handleLogin = async () => {
  const success = await authStore.login(form.value);
  if (success) router.push('/dashboard');
};
</script>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  background: linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 50%, #C7D2FE 100%);
}
.auth-container {
  width: 100%;
  max-width: 420px;
  background: var(--md-surface-container-lowest);
  border-radius: var(--md-shape-xl);
  padding: 40px 32px;
  box-shadow: var(--md-elevation-3);
}
.auth-brand {
  text-align: center;
  margin-bottom: 32px;
}
.auth-brand-icon {
  width: 72px;
  height: 72px;
  border-radius: var(--md-shape-lg);
  background: var(--md-primary-container);
  color: var(--md-on-primary-container);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 16px;
}
.auth-brand h1 {
  font-size: 24px;
  font-weight: 700;
  color: var(--md-on-surface);
  margin-bottom: 4px;
}
.auth-brand p {
  font-size: 14px;
  color: var(--md-on-surface-variant);
}
.auth-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.auth-error {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  background: var(--md-error-container);
  color: var(--md-on-error-container);
  border-radius: var(--md-shape-sm);
  font-size: 14px;
}
.auth-submit {
  width: 100%;
  padding: 14px;
  font-size: 16px;
  margin-top: 8px;
}
.auth-footer {
  text-align: center;
  margin-top: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  font-size: 14px;
  color: var(--md-on-surface-variant);
}
.md-divider { margin-top: 24px; }
.spinning {
  animation: spin 1s linear infinite;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}
@media (max-width: 480px) {
  .auth-container { padding: 32px 20px; border-radius: var(--md-shape-lg); }
}
</style>
