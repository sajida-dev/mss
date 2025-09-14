<script setup lang="ts">
import { ArrowUpRight, Users, School, UserCheck, GraduationCap, FileText, BarChart3, Trophy, CalendarCheck, Layers } from 'lucide-vue-next';

const props = defineProps({
  label: { type: String, required: true },
  value: { type: [String, Number], required: true },
  icon: { type: String, default: 'arrow-up-right' },
  color: { type: String, default: 'blue' },
  change: { type: String, default: '' },
});

// Icon mapping
const iconMap = {
  'users': Users,
  'school': School,
  'user-tie': UserCheck,
  'graduation-cap': GraduationCap,
  'file-alt': FileText,
  'chart-bar': BarChart3,
  'trophy': Trophy,
  'chart-line': BarChart3,
  'calendar-check': CalendarCheck,
  'layer-group': Layers,
  'arrow-up-right': ArrowUpRight,
};

// Color mapping
const colorMap = {
  'blue': 'bg-blue-600',
  'green': 'bg-green-600',
  'purple': 'bg-purple-600',
  'orange': 'bg-orange-600',
  'red': 'bg-red-600',
  'yellow': 'bg-yellow-600',
};

const IconComponent = iconMap[props.icon as keyof typeof iconMap] || ArrowUpRight;
const bgColor = colorMap[props.color as keyof typeof colorMap] || 'bg-blue-600';
</script>

<template>
  <div class="bg-white dark:bg-neutral-700 rounded-lg p-4 shadow-sm border">
    <div class="flex items-center justify-between">
      <div class="flex-1">
        <div class="text-sm text-neutral-500 dark:text-white font-medium mb-1">{{ label }}</div>
        <div class="text-3xl font-bold text-neutral-800 dark:text-white mb-2">{{ value }}</div>
        <div v-if="change" class="text-xs text-green-600 font-medium">{{ change }}</div>
      </div>
      <div class="flex items-center justify-center w-12 h-12 rounded-full" :class="bgColor">
        <IconComponent class="w-6 h-6 text-white" />
      </div>
    </div>
  </div>
</template>