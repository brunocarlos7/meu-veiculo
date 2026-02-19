import { ref } from 'vue';

const toasts = ref([]);

export function useToast() {
    const add = (message, type = 'info', duration = 4000) => {
        const id = Date.now();
        toasts.value.push({ id, message, type });

        if (duration > 0) {
            setTimeout(() => {
                remove(id);
            }, duration);
        }
    };

    const remove = (id) => {
        const index = toasts.value.findIndex((t) => t.id === id);
        if (index !== -1) {
            toasts.value.splice(index, 1);
        }
    };

    const success = (msg, duration) => add(msg, 'success', duration);
    const error = (msg, duration) => add(msg, 'error', duration);
    const info = (msg, duration) => add(msg, 'info', duration);
    const warning = (msg, duration) => add(msg, 'warning', duration);

    return {
        toasts,
        add,
        remove,
        success,
        error,
        info,
        warning
    };
}
