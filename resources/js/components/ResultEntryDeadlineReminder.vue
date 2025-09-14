<template>
    <div v-if="deadline">
        <p class="text-sm text-red-600">
            Result submission deadline: {{ formattedDeadline }}<br />
            Time remaining: {{ countdown }}
        </p>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import dayjs from 'dayjs';
import duration from 'dayjs/plugin/duration';

dayjs.extend(duration);

const props = defineProps({
    deadline: String, // from backend e.g. 2025-09-20T23:59:00
});

const countdown = ref('');
const formattedDeadline = dayjs(props.deadline).format('YYYY-MM-DD HH:mm');

function updateCountdown() {
    const now = dayjs();
    const deadline = dayjs(props.deadline);
    const diff = deadline.diff(now);

    if (diff <= 0) {
        countdown.value = 'Deadline has passed!';
        return;
    }

    const d = dayjs.duration(diff);
    countdown.value = `${d.days()}d ${d.hours()}h ${d.minutes()}m ${d.seconds()}s`;
}

onMounted(() => {
    updateCountdown();
    setInterval(updateCountdown, 1000);
});
</script>
