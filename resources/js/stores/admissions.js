import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useAdmissionsStore = defineStore('admissions', () => {
    const students = ref([]);

    function addStudent(student) {
        // Avoid duplicates
        if (!students.value.find(s => s.id === student.id)) {
            students.value.unshift(student);
        }
    }

    function updateStudent(updated) {
        const idx = students.value.findIndex(s => s.id === updated.id);
        if (idx !== -1) students.value[idx] = updated;
    }

    function removeStudent(id) {
        students.value = students.value.filter(s => s.id !== id);
    }

    return { students, addStudent, updateStudent, removeStudent };
}); 