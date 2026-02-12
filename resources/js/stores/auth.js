import { defineStore } from 'pinia';
import axios from '../axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('token') || null,
        loading: false,
        errors: []
    }),
    actions: {
        async login(credentials) {
            this.loading = true;
            this.errors = [];
            try {
                // Get CSRF cookie first (Sanctum)
                await axios.get('/sanctum/csrf-cookie');

                const response = await axios.post('/api/login', credentials);
                this.token = response.data.access_token;
                this.user = response.data.user;

                localStorage.setItem('token', this.token);
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;

                return true;
            } catch (error) {
                this.errors = error.response?.data?.errors || ['Login failed'];
                return false;
            } finally {
                this.loading = false;
            }
        },
        async register(data) {
            this.loading = true;
            this.errors = [];
            try {
                const response = await axios.post('/api/register', data);
                this.token = response.data.access_token;
                this.user = response.data.user;

                localStorage.setItem('token', this.token);
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;

                return true;
            } catch (error) {
                this.errors = error.response?.data?.errors || ['Registration failed'];
                return false;
            } finally {
                this.loading = false;
            }
        },
        async logout() {
            try {
                await axios.post('/api/logout');
            } catch (e) {
                // ignore
            } finally {
                this.user = null;
                this.token = null;
                localStorage.removeItem('token');
                delete axios.defaults.headers.common['Authorization'];
            }
        },
        async fetchUser() {
            if (!this.token) return;
            try {
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
                const response = await axios.get('/api/user');
                this.user = response.data;
            } catch (error) {
                this.user = null;
                this.token = null;
                localStorage.removeItem('token');
            }
        },
        async updateProfile(data) {
            this.loading = true;
            this.errors = [];
            try {
                const response = await axios.put('/api/profile', data);
                this.user = response.data.user;
                return true;
            } catch (error) {
                this.errors = error.response?.data?.errors || ['Falha ao atualizar perfil'];
                return false;
            } finally {
                this.loading = false;
            }
        },
        async updatePassword(data) {
            this.loading = true;
            this.errors = [];
            try {
                await axios.put('/api/profile/password', data);
                return true;
            } catch (error) {
                this.errors = error.response?.data?.errors || ['Falha ao alterar senha'];
                return false;
            } finally {
                this.loading = false;
            }
        }
    }
});
