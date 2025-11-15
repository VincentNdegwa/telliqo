import { usePermissions } from '@/composables/usePermissions';
import { hasAllPermissions, hasPermission } from '@/plugins/permission';

export default {
    name: 'PermissionExamples',

    setup() {
        const { can, canAll, canAny } = usePermissions();

        return {
            can,
            canAll,
            canAny,
        };
    },

    methods: {
        checkDirectPermission() {
            if (hasPermission('dashboard.manage')) {
                console.log('User can manage dashboard');
            }

            if (hasAllPermissions(['dashboard.manage', 'dashboard.view'])) {
                console.log('User has all dashboard permissions');
            }
        },
    },
};
