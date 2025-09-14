<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head :title="`Assign Subjects to ${classData.name}`" />
        <div class="max-w-2xl mx-auto py-8">
            <h1 class="text-xl font-bold mb-4">Assign Subjects to {{ classData.name }}</h1>
            <form @submit.prevent="submit">
                <label class="block mb-2">Select Subjects:</label>
                <select v-model="selectedSubjects" multiple class="input w-full mb-4">
                    <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                        {{ subject.name }}
                    </option>
                </select>
                <Button type="submit">Assign</Button>
            </form>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Button from '@/components/ui/button/Button.vue'
import { BreadcrumbItem } from '@/types'

const props = defineProps<{ classData: any, subjects: any[], assigned: any[] }>()
const selectedSubjects = ref<number[]>(props.assigned.map((s: any) => s.id))
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/' },
    { title: 'Classes', href: '/classes' },
    { title: props.classData.name, href: `/classes/${props.classData.id}` },
    { title: 'Assign Subjects', href: '#' }
]

function submit() {
    useForm({ subject_ids: selectedSubjects.value }).post(`/classes/${props.classData.id}/subjects`)
}
</script>