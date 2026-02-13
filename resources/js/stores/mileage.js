import { defineStore } from 'pinia';
import axios from '../axios';

export const useMileageStore = defineStore('mileage', {
    state: () => ({
        mileageEntries: [],
        loading: false,
        errors: [],
    }),

    actions: {
        async fetchMileage(vehicleId) {
            this.loading = true;
            try {
                const response = await axios.get(`/api/vehicles/${vehicleId}/mileage`);
                this.mileageEntries = response.data;
            } catch (error) {
                console.error('Error fetching mileage:', error);
            } finally {
                this.loading = false;
            }
        },

        async addMileage(vehicleId, data) {
            this.loading = true;
            this.errors = [];
            try {
                const response = await axios.post(`/api/vehicles/${vehicleId}/mileage`, data);
                this.mileageEntries.unshift(response.data);
                // Sort by date desc
                this.mileageEntries.sort((a, b) => new Date(b.date) - new Date(a.date));
                return true;
            } catch (error) {
                if (error.response?.data?.errors) {
                    this.errors = Object.values(error.response.data.errors).flat();
                } else if (error.response) {
                    this.errors = [`Erro (${error.response.status}): ${error.response.data?.message || 'Erro desconhecido'}`];
                } else {
                    this.errors = ['Erro de conexão ou servidor'];
                }
                console.error('Mileage Error:', error);
                return false;
            } finally {
                this.loading = false;
            }
        },

        async updateMileage(id, data) {
            this.loading = true;
            this.errors = [];
            try {
                const response = await axios.put(`/api/mileage/${id}`, data);
                const index = this.mileageEntries.findIndex(e => e.id === id);
                if (index !== -1) {
                    this.mileageEntries[index] = response.data;
                    // Sort again just in case date changed
                    this.mileageEntries.sort((a, b) => new Date(b.date) - new Date(a.date));
                }
                return true;
            } catch (error) {
                if (error.response?.data?.errors) {
                    this.errors = Object.values(error.response.data.errors).flat();
                } else if (error.response) {
                    this.errors = [`Erro (${error.response.status}): ${error.response.data?.message || 'Erro desconhecido'}`];
                } else {
                    this.errors = ['Erro de conexão'];
                }
                return false;
            } finally {
                this.loading = false;
            }
        },

        async deleteMileage(id) {
            try {
                await axios.delete(`/api/mileage/${id}`);
                this.mileageEntries = this.mileageEntries.filter(entry => entry.id !== id);
                return true;
            } catch (error) {
                console.error('Error deleting mileage:', error);
                return false;
            }
        }
    }
});
