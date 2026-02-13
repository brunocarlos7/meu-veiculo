import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            redirect: '/dashboard'
        },
        {
            path: '/login',
            component: () => import('../views/Login.vue'),
            meta: { guest: true }
        },
        {
            path: '/register',
            component: () => import('../views/Register.vue'),
            meta: { guest: true }
        },
        {
            path: '/',
            component: () => import('../layouts/AppLayout.vue'),
            meta: { requiresAuth: true },
            children: [
                {
                    path: 'dashboard',
                    name: 'Dashboard',
                    component: () => import('../views/Dashboard.vue'),
                },
                {
                    path: 'vehicles',
                    name: 'VehicleList',
                    component: () => import('../views/VehicleList.vue'),
                },
                {
                    path: 'vehicles/create',
                    name: 'VehicleCreate',
                    component: () => import('../views/VehicleForm.vue'),
                },
                {
                    path: 'vehicles/:id/fuel',
                    name: 'FuelList',
                    component: () => import('../views/FuelList.vue'),
                },
                {
                    path: 'vehicles/:id/fuel/create',
                    name: 'FuelCreate',
                    component: () => import('../views/FuelForm.vue'),
                },
                {
                    path: 'vehicles/:id/mileage',
                    name: 'MileageTracker',
                    component: () => import('../views/MileageTracker.vue'),
                },
                {
                    path: 'vehicles/:id/maintenance',
                    name: 'MaintenanceList',
                    component: () => import('../views/MaintenanceList.vue'),
                },
                {
                    path: 'vehicles/:id/expenses',
                    name: 'ExpenseList',
                    component: () => import('../views/ExpenseList.vue'),
                },
                {
                    path: 'reminders',
                    name: 'Reminders',
                    component: () => import('../views/Reminders.vue'),
                },
                {
                    path: 'reports',
                    name: 'Reports',
                    component: () => import('../views/Reports.vue'),
                },
            ]
        },
    ]
});

router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();

    // Check auth status (if not already checked)
    if (!authStore.user && localStorage.getItem('token')) {
        await authStore.fetchUser();
    }

    if (to.meta.requiresAuth && !authStore.user) {
        next('/login');
    } else if (to.meta.guest && authStore.user) {
        next('/dashboard');
    } else {
        next();
    }
});

export default router;
