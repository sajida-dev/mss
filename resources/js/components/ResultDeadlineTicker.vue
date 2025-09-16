<template>
    <div v-if="exams.length > 0"
        class="bg-yellow-100 dark:bg-yellow-900/20 border-b border-yellow-300 dark:border-yellow-700 py-2 px-4 overflow-hidden">
        <Vue3Marquee :pauseOnHover="true" :speed="30" class="text-sm text-yellow-800 dark:text-yellow-300 marquee">
            <template v-for="exam in exams" :key="exam.id">
                🔔 <strong>{{ exam.exam_type_name }} - {{ exam.academic_year }}</strong> — Result Submission Deadline:
                {{
                    formatDate(exam.result_entry_deadline) }}
                — Time Left: {{ countdowns[exam.id] || '...' }}

            </template>
        </Vue3Marquee>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Vue3Marquee } from 'vue3-marquee';
import dayjs from 'dayjs';
import duration from 'dayjs/plugin/duration';
dayjs.extend(duration);

const props = defineProps({
    exams: {
        type: Array,
        required: true,
    },
});

const countdowns = ref({});
console.log('props.exams : ', props.exams)
function updateCountdowns() {
    const now = dayjs();
    props.exams.forEach((exam) => {
        const deadline = dayjs(exam.result_entry_deadline);
        const diff = deadline.diff(now);

        if (diff <= 0) {
            countdowns.value[exam.id] = 'Deadline passed!';
        } else {
            const d = dayjs.duration(diff);
            countdowns.value[exam.id] = `${d.days()}d ${d.hours()}h ${d.minutes()}m ${d.seconds()}s`;
        }
    });
}

function formatDate(date) {
    return dayjs(date).format('YYYY-MM-DD HH:mm');
}

onMounted(() => {
    updateCountdowns();
    setInterval(updateCountdowns, 1000);
});
</script>

<style scoped>
.marquee {
    white-space: nowrap;
    overflow: hidden;
    display: block;
    font-weight: 600;
    font-size: 0.875rem;
    padding: 0.25rem 0;
}
</style>
