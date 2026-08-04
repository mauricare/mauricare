import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export const useCareOptions = () => {
    const page = usePage();

    return {
        careTypes: computed(() => page.props.care_options?.care_types || []),
        carerTypes: computed(() => page.props.care_options?.carer_types || []),
    };
};
