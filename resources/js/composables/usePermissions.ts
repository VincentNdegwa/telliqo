import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function usePermissions() {
    const page = usePage();

    const permissions = computed(() => page.props.auth?.permissions || []);

    const can = (permission: string | string[]): boolean => {
        if (Array.isArray(permission)) {
            return permission.some(p => permissions.value.includes(p));
        }
        return permissions.value.includes(permission);
    };

    const canAll = (permissionsToCheck: string[]): boolean => {
        return permissionsToCheck.every(p => permissions.value.includes(p));
    };

    const canAny = (permissionsToCheck: string[]): boolean => {
        return permissionsToCheck.some(p => permissions.value.includes(p));
    };

    const hasPermission = can;

    return {
        permissions,
        can,
        canAll,
        canAny,
        hasPermission,
    };
}
