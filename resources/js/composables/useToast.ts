import { ref } from 'vue';

interface Toast {
    id: string;
    type: 'success' | 'error' | 'info';
    message: string;
}

const toasts = ref<Toast[]>([]);

export function useToast() {
    function pushToast(type: Toast['type'], message: string) {
        const id = Date.now().toString() + Math.random().toString(36).slice(2, 6);
        const toast: Toast = { id, type, message };
        toasts.value.push(toast);
        setTimeout(() => {
            toasts.value = toasts.value.filter((x) => x.id !== id);
        }, 4000);
    }

    return {
        toasts,
        toast: {
            success: (message: string) => pushToast('success', message),
            error: (message: string) => pushToast('error', message),
            info: (message: string) => pushToast('info', message),
        }
    };
}