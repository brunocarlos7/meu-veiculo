import { defineStore } from 'pinia';
import axios from '../axios';

export const useReminderStore = defineStore('reminder', {
    state: () => ({
        reminders: [],
        loading: false,
        errors: []
    }),
    actions: {
        async fetchReminders(vehicleId = null) {
            this.loading = true;
            try {
                const params = vehicleId ? { vehicle_id: vehicleId } : {};
                const response = await axios.get('/api/reminders', { params });
                this.reminders = response.data;
                return response.data;
            } catch (error) {
                console.error(error);
                return [];
            } finally {
                this.loading = false;
            }
        },
        async createReminder(data) {
            this.loading = true;
            this.errors = [];
            try {
                const response = await axios.post('/api/reminders', data);
                this.reminders.unshift(response.data);
                return true;
            } catch (error) {
                if (error.response?.data?.errors) {
                    this.errors = Object.values(error.response.data.errors).flat();
                } else {
                    this.errors = ['Erro ao salvar lembrete'];
                }
                return false;
            } finally {
                this.loading = false;
            }
        },
        async toggleComplete(reminder) {
            try {
                const response = await axios.put(`/api/reminders/${reminder.id}`, {
                    is_completed: !reminder.is_completed
                });
                const index = this.reminders.findIndex(r => r.id === reminder.id);
                if (index !== -1) this.reminders[index] = response.data;
            } catch (error) {
                console.error(error);
            }
        },
        async deleteReminder(id) {
            if (!confirm('Deseja excluir este lembrete?')) return;
            try {
                await axios.delete(`/api/reminders/${id}`);
                this.reminders = this.reminders.filter(r => r.id !== id);
            } catch (error) {
                console.error(error);
            }
        }
    }
});
