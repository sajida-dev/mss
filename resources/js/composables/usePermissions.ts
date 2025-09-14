// composables/usePermissions.ts
import { usePage } from '@inertiajs/vue3';

export function usePermissions() {
    const page = usePage();
    const can = (permission: string): boolean => {
        return page.props.auth.can?.[permission] ?? false;
    };
    return { can };
}
