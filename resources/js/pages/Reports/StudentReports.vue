<template>
    <ReportTabsLayout title="Manage Reports" :icon="BarChart3" :tabs="[
        { label: 'Students', route: 'reports.students', permission: 'read-student-reports', icon: Users },
        { label: 'Attendance', route: 'reports.attendance', permission: 'read-attendance-reports', icon: CalendarDays },
        { label: 'Fees', route: 'reports.fees', permission: 'read-fee-reports', icon: Banknote }
    ]">
        <!-- Your actual report content goes here -->
        <div>Report content here...</div>
    </ReportTabsLayout>
</template>

<script setup lang="ts">
import ReportTabsLayout from './ReportTabsLayout.vue';
import { BarChart3, Users, CalendarDays, Banknote } from 'lucide-vue-next';
</script>
