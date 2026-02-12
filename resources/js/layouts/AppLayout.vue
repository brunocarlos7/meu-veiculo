<template>
  <div class="app-shell">
    <!-- Mobile Overlay -->
    <div v-if="sidebarOpen" class="sidebar-overlay" @click="sidebarOpen = false"></div>

    <!-- Sidebar / Navigation Rail -->
    <aside class="sidebar" :class="{ open: sidebarOpen }">
      <div class="sidebar-header">
        <span class="material-symbols-rounded sidebar-logo">directions_car</span>
        <span class="sidebar-title">Meu Veículo</span>
      </div>

      <nav class="sidebar-nav">
        <router-link to="/dashboard" class="nav-item" :class="{ active: $route.path === '/dashboard' }" @click="sidebarOpen = false">
          <span class="material-symbols-rounded">dashboard</span>
          <span class="nav-label">Dashboard</span>
        </router-link>
        <router-link to="/vehicles" class="nav-item" :class="{ active: $route.path.startsWith('/vehicles') }" @click="sidebarOpen = false">
          <span class="material-symbols-rounded">garage</span>
          <span class="nav-label">Veículos</span>
        </router-link>
        <router-link to="/reminders" class="nav-item" :class="{ active: $route.path === '/reminders' }" @click="sidebarOpen = false">
          <span class="material-symbols-rounded">notifications</span>
          <span class="nav-label">Lembretes</span>
        </router-link>
        <router-link to="/reports" class="nav-item" :class="{ active: $route.path === '/reports' }" @click="sidebarOpen = false">
          <span class="material-symbols-rounded">bar_chart</span>
          <span class="nav-label">Relatórios</span>
        </router-link>
      </nav>

      <div class="sidebar-bottom">
        <div class="user-info" @click="showProfileModal = true">
          <div class="user-avatar">
            {{ userInitial }}
          </div>
          <div class="user-details">
            <span class="user-name">{{ authStore.user?.name }}</span>
            <span class="user-email">{{ authStore.user?.email }}</span>
          </div>
        </div>
        <button @click="handleLogout" class="nav-item logout-btn">
          <span class="material-symbols-rounded">logout</span>
          <span class="nav-label">Sair</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="main-area">
      <!-- Top App Bar (Mobile) -->
      <header class="top-bar">
        <button class="top-bar-menu" @click="sidebarOpen = !sidebarOpen">
          <span class="material-symbols-rounded">menu</span>
        </button>
        <h1 class="top-bar-title">{{ pageTitle }}</h1>
        <div class="top-bar-spacer"></div>
      </header>

      <main class="main-content">
        <router-view></router-view>
      </main>
    </div>
    <!-- Profile Modal -->
    <ProfileModal :show="showProfileModal" @close="showProfileModal = false" />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter, useRoute } from 'vue-router';
import ProfileModal from '../components/ProfileModal.vue';

const authStore = useAuthStore();
const router = useRouter();
const route = useRoute();
const sidebarOpen = ref(false);
const showProfileModal = ref(false);

const userInitial = computed(() => authStore.user?.name?.charAt(0)?.toUpperCase() || '?');

const pageTitle = computed(() => {
  const titles = {
    '/dashboard': 'Dashboard',
    '/vehicles': 'Meus Veículos',
    '/vehicles/create': 'Novo Veículo',
    '/reminders': 'Lembretes',
    '/reports': 'Relatórios',
  };
  for (const [path, title] of Object.entries(titles)) {
    if (route.path === path) return title;
  }
  if (route.path.includes('/fuel/create')) return 'Novo Abastecimento';
  if (route.path.includes('/fuel')) return 'Abastecimentos';
  if (route.path.includes('/maintenance')) return 'Manutenções';
  if (route.path.includes('/expenses')) return 'Despesas';
  return 'Meu Veículo';
});

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>

<style scoped>
.app-shell {
  display: flex;
  min-height: 100vh;
  background: var(--md-surface);
}

/* ---- Sidebar ---- */
.sidebar {
  width: 280px;
  background: var(--md-surface-container-low);
  display: flex;
  flex-direction: column;
  border-right: 1px solid var(--md-outline-variant);
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  z-index: 200;
  transition: transform var(--md-motion-duration-medium) var(--md-motion-emphasized);
}
.sidebar-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 20px 24px;
  border-bottom: 1px solid var(--md-outline-variant);
}
.sidebar-logo {
  font-size: 32px;
  color: var(--md-primary);
}
.sidebar-title {
  font-size: 18px;
  font-weight: 700;
  color: var(--md-on-surface);
}

/* ---- Nav Items ---- */
.sidebar-nav {
  padding: 12px;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: var(--md-shape-full);
  color: var(--md-on-surface-variant);
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
  transition: all var(--md-motion-duration-short) var(--md-motion-standard);
  cursor: pointer;
  border: none;
  background: none;
  width: 100%;
  text-align: left;
  font-family: 'Inter', sans-serif;
}
.nav-item:hover {
  background: rgba(79, 70, 229, 0.08);
  color: var(--md-on-surface);
}
.nav-item.active {
  background: var(--md-primary-container);
  color: var(--md-on-primary-container);
  font-weight: 600;
}

/* ---- User ---- */
.sidebar-bottom {
  padding: 12px;
  border-top: 1px solid var(--md-outline-variant);
}
.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  margin-bottom: 4px;
  cursor: pointer;
  border-radius: var(--md-shape-md);
  transition: background 200ms;
}
.user-info:hover {
  background: var(--md-surface-container-high);
}
.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: var(--md-shape-full);
  background: var(--md-primary);
  color: var(--md-on-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
  flex-shrink: 0;
}
.user-details {
  display: flex;
  flex-direction: column;
  min-width: 0;
}
.user-name {
  font-size: 13px;
  font-weight: 600;
  color: var(--md-on-surface);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.user-email {
  font-size: 11px;
  color: var(--md-on-surface-variant);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.logout-btn {
  color: var(--md-error);
}
.logout-btn:hover {
  background: var(--md-error-container);
}

/* ---- Main Area ---- */
.main-area {
  flex: 1;
  margin-left: 280px;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}
.top-bar {
  display: none;
  align-items: center;
  gap: 4px;
  padding: 8px 8px 8px 4px;
  background: var(--md-surface);
  position: sticky;
  top: 0;
  z-index: 50;
}
.top-bar-menu {
  width: 48px;
  height: 48px;
  border: none;
  background: none;
  border-radius: var(--md-shape-full);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: var(--md-on-surface);
}
.top-bar-menu:hover { background: rgba(0,0,0,0.05); }
.top-bar-title {
  font-size: 20px;
  font-weight: 600;
  color: var(--md-on-surface);
}
.top-bar-spacer { flex: 1; }
.sidebar-overlay { display: none; }

.main-content {
  flex: 1;
  padding: 32px;
  max-width: 1200px;
  width: 100%;
  margin: 0 auto;
}

/* ---- Responsive ---- */
@media (max-width: 1024px) {
  .sidebar {
    transform: translateX(-100%);
  }
  .sidebar.open {
    transform: translateX(0);
    box-shadow: var(--md-elevation-4);
  }
  .sidebar-overlay {
    display: block;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    z-index: 150;
    animation: fadeIn 200ms ease;
  }
  .main-area {
    margin-left: 0;
  }
  .top-bar {
    display: flex;
  }
  .main-content {
    padding: 16px;
  }
}
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>
