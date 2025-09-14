<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

// Import only needed icons
import {
    User,
    Users,
    Edit,
    Trash,
    BookOpen,
    House,
    Calendar,
    Info,
    SquarePen,
    Shield,
    Eye,
    EyeOff,
    ArrowLeft,
    ArrowRight,
    Plus,
    Save,
    RefreshCw,
    TriangleAlert,
    Target,
    Building2,
    Check,
    ChevronDown,
    LoaderCircle,
} from 'lucide-vue-next';

interface Props {
    name: string;
    class?: string;
    size?: number;
    color?: string;
    strokeWidth?: number | string;
}

const props = withDefaults(defineProps<Props>(), {
    class: '',
    size: 16,
    strokeWidth: 2,
});

// Optional: fallback icon if name not found
const fallbackIcon = Info;

const iconMap = {
    User,
    Users,
    Edit,
    Trash,
    BookOpen,
    House,
    Calendar,
    Info,
    SquarePen,
    Shield,
    Eye,
    EyeOff,
    ArrowLeft,
    ArrowRight,
    Plus,
    Save,
    RefreshCw,
    TriangleAlert,
    Target,
    Building2,
    Check,
    ChevronDown,
    LoaderCircle,
};

const className = computed(() => cn('h-4 w-4', props.class));

const icon = computed(() => {
    const iconName = props.name.charAt(0).toUpperCase() + props.name.slice(1);
    return iconMap[iconName as keyof typeof iconMap] || fallbackIcon;
});
</script>

<template>
    <component :is="icon" :class="className" :size="size" :stroke-width="strokeWidth" :color="color" />
</template>
