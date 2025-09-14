// plugins/v-can.ts
import { Directive } from 'vue';
import { usePage } from '@inertiajs/vue3';

const vCan: Directive = {
    mounted(el, binding) {
        const permissions = usePage().props.auth.can ?? {};
        if (!permissions[binding.value]) {
            el.parentNode && el.parentNode.removeChild(el);
        }
    },
};

export default {
    install(app: any) {
        app.directive('can', vCan);
    },
};
