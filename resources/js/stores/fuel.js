import { defineStore } from 'pinia';
import axios from '../axios';

export const useFuelStore = defineStore('fuel', {
    state: () => ({
        fuelEntries: [],
        fuelEntry: null,
        loading: false,
        errors: []
    }),
    actions: {
        async fetchFuelEntries(vehicleId) {
            this.loading = true;
            try {
                const response = await axios.get('/api/fuel-entries', {
                    params: { vehicle_id: vehicleId }
                });
                this.fuelEntries = response.data;
                return response.data;
            } catch (error) {
                console.error(error);
                return [];
            } finally {
                this.loading = false;
            }
        },
        async createFuelEntry(data) {
            this.loading = true;
            this.errors = [];
            try {
                await axios.post('/api/fuel-entries', data);
                return true;
            } catch (error) {
                if (error.response?.data?.errors) {
                    this.errors = Object.values(error.response.data.errors).flat();
                } else {
                    this.errors = ['Erro ao salvar abastecimento'];
                }
                return false;
            } finally {
                this.loading = false;
            }
        },
        async updateFuelEntry(id, data) {
            this.loading = true;
            this.errors = [];
            try {
                const response = await axios.put(`/api/fuel-entries/${id}`, data);
                const index = this.fuelEntries.findIndex(e => e.id === id);
                if (index !== -1) this.fuelEntries[index] = response.data;
                return true;
            } catch (error) {
                if (error.response?.data?.errors) {
                    this.errors = Object.values(error.response.data.errors).flat();
                } else {
                    this.errors = ['Erro ao atualizar abastecimento'];
                }
                return false;
            } finally {
                this.loading = false;
            }
        },
        async deleteFuelEntry(id) {
            if (!confirm('Deseja realmente excluir este abastecimento?')) return false;
            try {
                await axios.delete(`/api/fuel-entries/${id}`);
                this.fuelEntries = this.fuelEntries.filter(e => e.id !== id);
                return true;
            } catch (error) {
                console.error(error);
                return false;
            }
        }
    }
});
