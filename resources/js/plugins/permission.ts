import { usePage } from '@inertiajs/vue3';
import type { Plugin, DirectiveBinding } from 'vue';

const hasPermission = (permission: string | string[]): boolean => {
    const page = usePage();
    const userPermissions = page.props.auth?.permissions || [];

    if (Array.isArray(permission)) {
        return permission.some(p => userPermissions.includes(p));
    }

    return userPermissions.includes(permission);
};

const hasAllPermissions = (permissions: string[]): boolean => {
    const page = usePage();
    const userPermissions = page.props.auth?.permissions || [];

    return permissions.every(p => userPermissions.includes(p));
};

const permissionDirective = {
    mounted(el: HTMLElement, binding: DirectiveBinding) {
        const { value, modifiers } = binding;

        if (!value) {
            console.warn('v-permission directive requires a permission name or array');
            return;
        }

        const checkPermission = modifiers.all ? hasAllPermissions : hasPermission;
        const allowed = Array.isArray(value) ? checkPermission(value) : hasPermission(value);

        if (!allowed) {
            el.style.display = 'none';
        }
    },

    updated(el: HTMLElement, binding: DirectiveBinding) {
        const { value, modifiers } = binding;

        if (!value) {
            return;
        }

        const checkPermission = modifiers.all ? hasAllPermissions : hasPermission;
        const allowed = Array.isArray(value) ? checkPermission(value) : hasPermission(value);

        if (allowed) {
            el.style.display = '';
        } else {
            el.style.display = 'none';
        }
    }
};

const PermissionPlugin: Plugin = {
    install(app) {
        app.directive('permission', permissionDirective);

        app.config.globalProperties.$can = hasPermission;
        app.config.globalProperties.$canAll = hasAllPermissions;
    }
};

export { hasPermission, hasAllPermissions };
export default PermissionPlugin;
