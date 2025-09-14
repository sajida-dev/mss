import { ref } from 'vue';
import axios from 'axios';
import { toast } from 'vue3-toastify';

export function useSections() {
    const sections = ref<{ id: number; name: string }[]>([]);
    const loading = ref(false);

    async function fetchSectionsByClass(classId: string | number) {
        sections.value = [];
        if (!classId) return;

        loading.value = true;
        try {
            const response = await axios.get(`/api/classes/${classId}/sections`);
            sections.value = response.data;
        } catch (error) {
            toast.error('Failed to fetch sections');
        } finally {
            loading.value = false;
        }
    }

    return {
        sections,
        loading,
        fetchSectionsByClass,
    };
}
