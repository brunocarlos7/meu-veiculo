import { defineStore } from 'pinia';
import axios from '../axios';

export const useVehicleStore = defineStore('vehicle', {
    state: () => ({
        vehicles: [],
        vehicle: null,
        loading: false,
        errors: []
    }),
    actions: {
        async fetchVehicles() {
            this.loading = true;
            try {
                const response = await axios.get('/api/vehicles');
                this.vehicles = response.data;
            } catch (error) {
                console.error(error);
            } finally {
                this.loading = false;
            }
        },
        async fetchVehicle(id) {
            this.loading = true;
            try {
                const response = await axios.get(`/api/vehicles/${id}`);
                this.vehicle = response.data;
            } catch (error) {
                console.error(error);
            } finally {
                this.loading = false;
            }
        },
        async createVehicle(data) {
            this.loading = true;
            this.errors = [];
            try {
                await axios.post('/api/vehicles', data);
                return true;
            } catch (error) {
                if (error.response?.data?.errors) {
                    this.errors = Object.values(error.response.data.errors).flat();
                } else {
                    this.errors = ['Erro ao cadastrar veículo'];
                }
                return false;
            } finally {
                this.loading = false;
            }
        },
        async updateVehicle(id, data) {
            this.loading = true;
            this.errors = [];
            try {
                const response = await axios.put(`/api/vehicles/${id}`, data);
                const index = this.vehicles.findIndex(v => v.id === id);
                if (index !== -1) this.vehicles[index] = response.data;
                return true;
            } catch (error) {
                if (error.response?.data?.errors) {
                    this.errors = Object.values(error.response.data.errors).flat();
                } else {
                    this.errors = ['Erro ao atualizar veículo'];
                }
                return false;
            } finally {
                this.loading = false;
            }
        },
        async deleteVehicle(id) {
            try {
                await axios.delete(`/api/vehicles/${id}`);
                this.vehicles = this.vehicles.filter(v => v.id !== id);
                return true;
            } catch (error) {
                console.error(error);
                return false;
            }
        }
    }
});
