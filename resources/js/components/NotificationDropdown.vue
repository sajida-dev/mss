<script setup lang="ts">
import { ref, computed, PropType } from 'vue';
import { Bell } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { router } from '@inertiajs/vue3';
import { formatDistanceToNow } from 'date-fns';

interface Notification {
    id: number;
    title: string;
    body: string;
    time: string;
    read: boolean;
    link?: string;
}

const props = defineProps({
    notifications: {
        type: Array as PropType<Notification[]>,
        required: true,
    },
});

const emit = defineEmits(['read', 'mark-all-as-read', 'close']);

const unreadNotifications = computed(() => props.notifications.filter(n => !n.read));
const unreadCount = computed(() => unreadNotifications.value.length);

const formatDate = (date: string) => {
    if (!date) return '';
    const d = new Date(date);
    if (isNaN(d.getTime())) return '';
    return formatDistanceToNow(d, { addSuffix: true });
};

function openNotification(notification: Notification) {
    if (!notification.read) emit('read', notification);
    if (notification.link) {
        router.visit(notification.link);
        emit('close');
    }
}

function markAsRead(notification: Notification, event: Event) {
    event.stopPropagation();
    if (!notification.read) emit('read', notification);
}

function markAllAsRead() {
    unreadNotifications.value.forEach(notification => emit('read', notification));
    emit('mark-all-as-read');
}
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" size="icon" class="group h-9 w-9 cursor-pointer relative">
                <Bell class="size-5 opacity-80 group-hover:opacity-100 text-white" />
                <span v-if="unreadCount"
                    class="absolute top-1 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-600 rounded-full">{{
                        unreadCount }}</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" side="bottom" :sideOffset="8" class="w-72 max-w-full">
            <div class="bg-white dark:bg-neutral-900 rounded-lg shadow-lg p-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Notifications</h3>
                    <button v-if="unreadNotifications.length" @click="markAllAsRead"
                        class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                        Mark all as read
                    </button>
                </div>

                <div v-if="unreadNotifications.length" class="space-y-2">
                    <div v-for="notification in unreadNotifications" :key="notification.id"
                        class="p-3 rounded-lg transition-colors duration-200 cursor-pointer bg-white dark:bg-neutral-800 hover:bg-gray-50 dark:hover:bg-neutral-700"
                        :class="{ 'bg-blue-50 dark:bg-blue-900/30': !notification.read }"
                        @click="openNotification(notification)">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fa-bell text-gray-500 dark:text-gray-400"></i>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ notification.title }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-300">
                                    {{ notification.body }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                    {{ formatDate(notification.time) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-4 text-gray-500 dark:text-gray-400">
                    No unread notifications
                </div>
            </div>
        </DropdownMenuContent>
    </DropdownMenu>
</template>