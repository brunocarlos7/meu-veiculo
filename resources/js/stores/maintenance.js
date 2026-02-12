import { defineStore } from 'pinia';
import axios from '../axios';

export const useMaintenanceStore = defineStore('maintenance', {
    state: () => ({
        maintenances: [],
        loading: false,
        errors: []
    }),
    actions: {
        async fetchMaintenances(vehicleId) {
            this.loading = true;
            try {
                const response = await axios.get('/api/maintenances', {
                    params: { vehicle_id: vehicleId }
                });
                this.maintenances = response.data;
                return response.data;
            } catch (error) {
                console.error(error);
                return [];
            } finally {
                this.loading = false;
            }
        },
        async createMaintenance(data) {
            this.loading = true;
            this.errors = [];
            try {
                await axios.post('/api/maintenances', data);
                return true;
            } catch (error) {
                if (error.response?.data?.errors) {
                    this.errors = Object.values(error.response.data.errors).flat();
                } else {
                    this.errors = ['Erro ao salvar manutenção'];
                }
                return false;
            } finally {
                this.loading = false;
            }
        },
        async updateMaintenance(id, data) {
            this.loading = true;
            this.errors = [];
            try {
                const response = await axios.put(`/api/maintenances/${id}`, data);
                const index = this.maintenances.findIndex(m => m.id === id);
                if (index !== -1) this.maintenances[index] = response.data;
                return true;
            } catch (error) {
                if (error.response?.data?.errors) {
                    this.errors = Object.values(error.response.data.errors).flat();
                } else {
                    this.errors = ['Erro ao atualizar manutenção'];
                }
                return false;
            } finally {
                this.loading = false;
            }
        },
        async deleteMaintenance(id) {
            if (!confirm('Deseja realmente excluir esta manutenção?')) return false;
            try {
                await axios.delete(`/api/maintenances/${id}`);
                this.maintenances = this.maintenances.filter(m => m.id !== id);
                return true;
            } catch (error) {
                console.error(error);
                return false;
            }
        }
    }
});
