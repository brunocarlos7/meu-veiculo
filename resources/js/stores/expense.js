import { defineStore } from 'pinia';
import axios from '../axios';

export const useExpenseStore = defineStore('expense', {
    state: () => ({
        expenses: [],
        loading: false,
        errors: []
    }),
    actions: {
        async fetchExpenses(vehicleId) {
            this.loading = true;
            try {
                const response = await axios.get('/api/expenses', {
                    params: { vehicle_id: vehicleId }
                });
                this.expenses = response.data;
                return response.data;
            } catch (error) {
                console.error(error);
                return [];
            } finally {
                this.loading = false;
            }
        },
        async createExpense(data) {
            this.loading = true;
            this.errors = [];
            try {
                await axios.post('/api/expenses', data);
                return true;
            } catch (error) {
                if (error.response?.data?.errors) {
                    this.errors = Object.values(error.response.data.errors).flat();
                } else {
                    this.errors = ['Erro ao salvar despesa'];
                }
                return false;
            } finally {
                this.loading = false;
            }
        },
        async deleteExpense(id) {
            if (!confirm('Deseja excluir esta despesa?')) return;
            try {
                await axios.delete(`/api/expenses/${id}`);
                this.expenses = this.expenses.filter(e => e.id !== id);
            } catch (error) {
                console.error(error);
            }
        }
    }
});
