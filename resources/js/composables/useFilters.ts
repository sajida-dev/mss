import { ref, watch } from 'vue';
import axios from 'axios';

export function useFilters(initialClass = '', initialSection = '', initialTerm = '') {
    const selectedClass = ref(initialClass);
    const selectedSection = ref(initialSection);
    const selectedTerm = ref(initialTerm);

    const sections = ref([]);
    const sectionCache = new Map();

    async function fetchSections() {
        if (!selectedClass.value) {
            sections.value = [];
            return;
        }

        if (sectionCache.has(selectedClass.value)) {
            sections.value = sectionCache.get(selectedClass.value);
            return;
        }

        try {
            const res = await axios.get(`/api/classes/${selectedClass.value}/sections`);
            sections.value = res.data;
            sectionCache.set(selectedClass.value, res.data);
        } catch (err) {
            console.error('Failed to fetch sections', err);
        }
    }

    watch(selectedClass, () => {
        selectedSection.value = '';
        fetchSections();
    }, { immediate: true });

    return {
        selectedClass,
        selectedSection,
        selectedTerm,
        sections,
        fetchSections
    };
}
