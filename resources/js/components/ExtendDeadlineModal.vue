<template>
    <Dialog v-model:open="localShow">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Extend Result Entry Deadline</DialogTitle>
            </DialogHeader>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label for="new-deadline" class="block text-sm font-medium text-gray-700">
                        New Deadline
                    </label>
                    <input id="new-deadline" type="datetime-local" v-model="form.result_entry_deadline"
                        class="mt-1 block w-full border border-gray-300 p-2 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500" />
                </div>

                <div class="flex justify-end space-x-2">
                    <Button type="button" variant="outline" @click="close">Cancel</Button>
                    <Button type="submit" variant="default">Update</Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';

const props = defineProps({
    show: Boolean,
    exam: Object
})
const emit = defineEmits(['close'])

const localShow = ref(props.show)

watch(() => props.show, (val) => {
    localShow.value = val
})

watch(localShow, (val) => {
    if (!val) {
        emit('close')
    }
})

const form = ref({
    result_entry_deadline: props.exam?.result_entry_deadline ?? ''
})

watch(() => props.exam, (exam) => {
    form.value.result_entry_deadline = exam?.result_entry_deadline ?? ''
})

const submit = () => {
    router.put(route('exams.extend-deadline', props.exam?.id), form.value, {
        onSuccess: () => {
            close()
        },
    })
}

const close = () => {
    localShow.value = false
}
</script>
