<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import StatCard from '@/components/StatCard.vue';
import { ref, computed } from 'vue';
import {
  School,
  Users,
  Settings,
  UserPlus,
  WebhookIcon,
  CircleDollarSign,
  LineChart,
  CalendarCheck,
  Trophy,
  FileText,
  BarChart2,
  UserCircle2,
  Clock3,
  Plus,
  Settings2,
  SettingsIcon,
  LucideSettings,
} from 'lucide-vue-next'

interface School {
  id: string | number;
  name: string;
}

interface Stat {
  label: string;
  value: string | number;
  icon: string;
  color: string;
  change: string;
}

const props = defineProps<{
  user: any;
  userRoles: string[];
  activeSchoolId: string | number;
  schools: School[];
  stats: Stat[];
  recentData: any;
  errors: string[];
  activeSchool?: any;
}>();

const selectedSchool = ref<string | number>(props.activeSchoolId);

function switchSchool() {
  router.post('/switch-school', { school_id: selectedSchool.value }, {
    preserveScroll: true,
    onSuccess: () => {
      router.reload({ only: ['schools', 'activeSchoolId'] });
    }
  });
}

// Get role display name
const getRoleDisplayName = (roles: string[]) => {
  if (roles.includes('superadmin')) return 'Super Admin';
  if (roles.includes('admin')) return 'Admin';
  if (roles.includes('principal')) return 'Principal';
  if (roles.includes('teacher')) return 'Teacher';
  return 'User';
};

// Get role-specific welcome message
const getWelcomeMessage = (roles: string[]) => {
  if (roles.includes('superadmin')) return 'System Overview';
  if (roles.includes('admin')) return 'School Management';
  if (roles.includes('principal')) return 'Academic Overview';
  if (roles.includes('teacher')) return 'My Classes';
  return 'Dashboard';
};

// Check if there's any recent data to display
const hasRecentData = computed(() => {
  if (!props.recentData) return false;

  const dataKeys = [
    'recentSchools', 'recentTeachers', 'recentStudents', 'recentFees', 'recentApplicants',
    'myStudents', 'myPapers', 'myAdmissions', 'recentPapers'
  ];

  return dataKeys.some(key =>
    props.recentData[key] &&
    Array.isArray(props.recentData[key]) &&
    props.recentData[key].length > 0
  );
});
</script>

<template>

  <Head title="Dashboard" />
  <AppLayout>
    <div
      class="p-4 md:p-8 bg-gradient-to-br from-indigo-50 via-white to-pink-50 dark:from-neutral-800 dark:via-neutral-900 dark:to-neutral-800  min-h-screen">
      <!-- Error Messages -->
      <div v-if="props.errors && props.errors.length > 0" class="mb-6">
        <div v-for="error in props.errors" :key="error"
          class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-2">
          {{ error }}
        </div>
      </div>

      <!-- Header Section -->
      <div class="mb-8">
        <div class="flex justify-between items-center mb-4">
          <div>
            <h1 class="text-3xl font-bold text-neutral-800 dark:text-neutral-50">{{ getWelcomeMessage(props.userRoles)
            }}
            </h1>
            <p class="text-neutral-600 dark:text-neutral-100">Welcome back, {{ props.user.name }} ({{
              getRoleDisplayName(props.userRoles) }})</p>
          </div>

          <!-- School Switcher -->
          <div class="flex items-center gap-4">
          </div>
        </div>

        <!-- Active School Info -->
        <div v-if="props.activeSchool"
          class="bg-white dark:bg-neutral-800 rounded-lg p-4 shadow-sm border flex flex-row justify-between items-center">
          <h3 class="font-semibold text-neutral-800 dark:text-white mb-2">Current School: {{ props.activeSchool.name }}
          </h3>
          <p class="text-sm text-neutral-600 dark:text-white">Location:
            {{ props.activeSchool.address || 'No address available' }}
          </p>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <StatCard v-for="stat in props.stats" :key="stat.label" :label="stat.label" :value="stat.value"
          :icon="stat.icon" :color="stat.color" :change="stat.change" />
      </div>

      <!-- Role-specific content -->
      <div class="felx flex-col space-y-6">
        <!-- Quick Actions -->
        <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-lg p-6 border">
          <h3 class="text-lg font-semibold text-neutral-800 dark:text-white mb-4">Quick Actions</h3>
          <div class="space-y-3">
            <div v-if="props.userRoles.includes('superadmin')" class="grid grid-cols-1 lg:grid-cols-5 gap-3 space-y-2">
              <button @click="router.visit(route('schools.index'))"
                class="flex flex-row gap-3 items-center w-full text-left p-2 rounded-lg bg-blue-50 dark:bg-blue-500 hover:bg-blue-400 transition-colors">
                <School class="w-10 h-10 mr-2 p-2 text-blue-600 rounded-full bg-blue-300" />
                Manage Schools
              </button>
              <button @click="router.visit(route('admissions.create'))"
                class="flex flex-row gap-3 items-center w-full text-left p-3 rounded-lg bg-red-50 dark:bg-red-500 hover:bg-red-400 transition-colors">
                <UserPlus class="w-10 h-10 mr-2 p-2 rounded-full bg-red-300 text-red-600" />
                New Admission
              </button>
              <button @click="router.visit(route('settings.users'))"
                class="flex flex-row gap-3 items-center w-full text-left p-2 rounded-lg bg-green-50 dark:bg-green-500 hover:bg-green-400 transition-colors">
                <Users class="w-10 h-10 mr-2 p-2  rounded-full bg-green-300 text-green-600" />
                System Users
              </button>
              <button @click="router.visit(route('teachers.create'))"
                class="flex flex-row gap-3 items-center w-full text-left p-3 rounded-lg bg-orange-50 dark:bg-orange-500 hover:bg-orange-400 transition-colors">
                <Users class="w-10 h-10 mr-2 p-2 rounded-full bg-orange-300 text-orange-600" />
                Manage Teachers
              </button>
              <button @click="router.visit(route('profile.edit'))"
                class="flex flex-row gap-3 items-center w-full text-left px-2 py-1 rounded-lg bg-purple-50 dark:bg-purple-500 hover:bg-purple-400 transition-colors">
                <LucideSettings class="w-10 h-10 mr-2 p-2 rounded-full bg-purple-300 text-purple-600" />
                System Settings
              </button>
            </div>

            <div v-if="props.userRoles.includes('admin')" class="grid grid-cols-1 lg:grid-cols-3 gap-3 space-y-2">
              <button @click="router.visit(route('admissions.create'))"
                class="flex flex-row gap-3 items-center w-full text-left p-3 rounded-lg bg-blue-50 dark:bg-blue-500 hover:bg-blue-400 transition-colors">
                <UserPlus class="w-10 h-10 mr-2 p-2 rounded-full bg-blue-300 text-blue-600" />
                New Admission
              </button>
              <button @click="router.visit(route('teachers.create'))"
                class="flex flex-row gap-3 items-center w-full text-left p-3 rounded-lg bg-green-50 dark:bg-green-500 hover:bg-green-400 transition-colors">
                <Users class="w-10 h-10 mr-2 p-2 rounded-full bg-green-300 text-green-600" />
                Manage Teachers
              </button>
              <button @click="router.visit(route('fees.index'))"
                class="flex flex-row gap-3 items-center w-full text-left p-3 rounded-lg bg-purple-50 dark:bg-purple-500 hover:bg-purple-400 transition-colors">
                <CircleDollarSign class="w-10 h-10 mr-2 p-2 rounded-full bg-purple-300 text-purple-600" />
                Fee Management
              </button>
            </div>

            <div v-if="props.userRoles.includes('principal')" class="grid grid-cols-1 lg:grid-cols-3 gap-3 space-y-2">
              <button @click="router.visit(route('reports.index'))"
                class="flex flex-row gap-3 items-center w-full text-left p-3 rounded-lg bg-blue-50 dark:bg-blue-500 hover:bg-blue-400 transition-colors">
                <LineChart class="w-10 h-10 mr-2 p-2 rounded-full bg-blue-300 text-blue-600" />
                Academic Reports
              </button>
              <button @click="router.visit(route('attendance.index'))"
                class="flex flex-row gap-3 items-center w-full text-left p-3 rounded-lg bg-green-50 dark:bg-green-500 hover:bg-green-400 transition-colors">
                <CalendarCheck class="w-10 h-10 mr-2 p-2 rounded-full bg-green-300 text-green-600" />
                Attendance Overview
              </button>
              <button
                class="flex flex-row gap-3 items-center w-full text-left p-3 rounded-lg bg-purple-50 dark:bg-purple-500 hover:bg-purple-400 transition-colors">
                <Trophy class="w-10 h-10 mr-2 p-2 rounded-full bg-purple-300 text-purple-600" />
                Performance Analysis
              </button>
            </div>

            <div v-if="props.userRoles.includes('teacher')" class="grid grid-cols-1 lg:grid-cols-3 gap-3 space-y-2">
              <button @click="router.visit(route('papers.create'))"
                class="flex flex-row gap-3 items-center w-full text-left p-3 rounded-lg bg-blue-50 dark:bg-blue-500 hover:bg-blue-400 transition-colors">
                <FileText class="w-10 h-10 mr-2 p-2 rounded-full bg-blue-300 text-blue-600" />
                Create Paper
              </button>
              <button @click="router.visit(route('results.index'))"
                class="flex flex-row gap-3 items-center w-full text-left p-3 rounded-lg bg-green-50 dark:bg-green-500 hover:bg-green-400 transition-colors">
                <BarChart2 class="w-10 h-10 mr-2 p-2 rounded-full bg-green-300 text-green-600" />
                Publish Results
              </button>
              <button @click="router.visit(route('students.index'))"
                class="flex flex-row gap-3 items-center w-full text-left p-3 rounded-lg bg-purple-50 dark:bg-purple-500 hover:bg-purple-400 transition-colors">
                <UserCircle2 class="w-10 h-10 mr-2 p-2 rounded-full bg-purple-300 text-purple-600" />
                My Students
              </button>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-lg p-6 border">
          <h3 class="text-lg font-semibold text-neutral-800 dark:text-white mb-4">Recent Activity</h3>
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Schools (for Super Admin) -->
            <div v-if="props.recentData?.recentSchools && props.recentData.recentSchools.length > 0">
              <h4 class="text-sm font-medium text-neutral-700 mb-2">Recent Schools</h4>
              <div v-for="school in props.recentData.recentSchools.slice(0, 3)" :key="school.id"
                class="flex items-center p-3 mt-1 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                <div>
                  <p class="text-sm font-medium">{{ school.name || 'Unknown School' }}</p>
                  <p class="text-xs text-neutral-500">Created: {{ school.created_at ? new
                    Date(school.created_at).toLocaleDateString() : 'Recently' }}</p>
                </div>
              </div>
            </div>

            <!-- Recent Teachers (for Super Admin) -->
            <div v-if="props.recentData?.recentTeachers && props.recentData.recentTeachers.length > 0">
              <h4 class="text-sm font-medium text-neutral-700 mb-2">Recent Teachers</h4>
              <div v-for="teacher in props.recentData.recentTeachers.slice(0, 3)" :key="teacher.id"
                class="flex items-center p-3 mt-1 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                <div>
                  <p class="text-sm font-medium">{{ teacher.user.name || 'Unknown Teacher' }}</p>
                  <p class="text-xs text-neutral-500">{{ teacher.school?.name || 'School not assigned' }}</p>
                </div>
              </div>
            </div>

            <!-- Recent Students -->
            <div v-if="props.recentData?.recentStudents && props.recentData.recentStudents.length > 0">
              <h4 class="text-sm font-medium text-neutral-700 mb-2">Recent Students</h4>
              <div v-for="student in props.recentData.recentStudents.slice(0, 3)" :key="student.id"
                class="flex items-center p-3 mt-1 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                <div class="w-2 h-2 bg-purple-500 rounded-full mr-3"></div>
                <div>
                  <p class="text-sm font-medium">{{ student.name || 'Unknown Student' }}</p>
                  <p class="text-xs text-neutral-500">Admitted: {{ student.created_at ? new
                    Date(student.created_at).toLocaleDateString() : 'Recently' }}</p>
                </div>
              </div>
            </div>

            <!-- Recent Fees -->
            <div v-if="props.recentData?.recentFees && props.recentData.recentFees.length > 0">
              <h4 class="text-sm font-medium text-neutral-700 mb-2">Recent Fees</h4>
              <div v-for="fee in props.recentData.recentFees.slice(0, 3)" :key="fee.id"
                class="flex items-center p-3 mt-1 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                <div>
                  <p class="text-sm font-medium">{{ fee.student?.name || 'Unknown Student' }}</p>
                  <p class="text-xs text-neutral-500">Amount: ${{ fee.amount }}</p>
                </div>
              </div>
            </div>


            <!-- Recent Applicants -->
            <div v-if="props.recentData?.recentApplicants && props.recentData.recentApplicants.length > 0">
              <h4 class="text-sm font-medium text-neutral-700 mb-2">Recent Applicants</h4>
              <div v-for="applicant in props.recentData.recentApplicants.slice(0, 3)" :key="applicant.id"
                class="flex items-center p-3 mt-1 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                <div class="w-2 h-2 bg-yellow-500 rounded-full mr-3"></div>
                <div>
                  <p class="text-sm font-medium">{{ applicant.name || 'Unknown Student' }}</p>
                  <p class="text-xs text-neutral-500">Applied: {{ applicant.created_at ? new
                    Date(applicant.created_at).toLocaleDateString() : 'Recently' }}</p>
                </div>
              </div>
            </div>

            <!-- My Students (for teachers) -->
            <div v-if="props.recentData?.myStudents && props.recentData.myStudents.length > 0">
              <h4 class="text-sm font-medium text-neutral-700 mb-2">My Students</h4>
              <div v-for="student in props.recentData.myStudents.slice(0, 3)" :key="student.id"
                class="flex items-center p-3 mt-1 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                <div>
                  <p class="text-sm font-medium">{{ student.name }}</p>
                  <p class="text-xs text-neutral-500">Class: {{ student.class?.name || 'Not assigned' }}</p>
                </div>
              </div>
            </div>

            <!-- My Papers (for teachers) -->
            <div v-if="props.recentData?.myPapers && props.recentData.myPapers.length > 0">
              <h4 class="text-sm font-medium text-neutral-700 mb-2">My Papers</h4>
              <div v-for="paper in props.recentData.myPapers.slice(0, 3)" :key="paper.id"
                class="flex items-center p-3 mt-1 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                <div>
                  <p class="text-sm font-medium">{{ paper.title || 'Untitled Paper' }}</p>
                  <p class="text-xs text-neutral-500">Subject: {{ paper.subject || 'Not specified' }}</p>
                </div>
              </div>
            </div>

            <!-- My Admissions (for teachers) -->
            <div v-if="props.recentData?.myAdmissions && props.recentData.myAdmissions.length > 0">
              <h4 class="text-sm font-medium text-neutral-700 mb-2">My Admissions</h4>
              <div v-for="admission in props.recentData.myAdmissions.slice(0, 3)" :key="admission.id"
                class="flex items-center p-3 mt-1 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                <div class="w-2 h-2 bg-orange-500 rounded-full mr-3"></div>
                <div>
                  <p class="text-sm font-medium">{{ admission.name || 'Unknown Student' }}</p>
                  <p class="text-xs text-neutral-500">Admitted: {{ admission.created_at ? new
                    Date(admission.created_at).toLocaleDateString() : 'Recently' }}</p>
                </div>
              </div>
            </div>

            <!-- No recent activity -->
            <div v-if="!hasRecentData" class="text-center py-8">
              <div class="text-neutral-400 mb-3">
                <i class="fas fa-clock text-4xl"></i>
              </div>
              <p class="text-sm font-medium text-neutral-600 mb-2">No Recent Activity</p>
              <p class="text-xs text-neutral-500 mb-4">Recent students, fees, and applications will appear here once
                data
                is added</p>

              <!-- Role-specific suggestions -->
              <div v-if="props.userRoles.includes('superadmin')" class="text-xs text-neutral-400">
                <p>• Add schools to see school management activity</p>
                <p>• Create teachers and students to see recent activity</p>
              </div>
              <div v-else-if="props.userRoles.includes('admin')" class="text-xs text-neutral-400">
                <p>• Add students to see recent admissions</p>
                <p>• Create fees to see payment activity</p>
              </div>
              <div v-else-if="props.userRoles.includes('principal')" class="text-xs text-neutral-400">
                <p>• Review pending applications</p>
                <p>• Monitor student admissions and papers</p>
              </div>
              <div v-else-if="props.userRoles.includes('teacher')" class="text-xs text-neutral-400">
                <p>• Create papers to see your recent work</p>
                <p>• Handle admissions to see your activity</p>
              </div>

              <!-- Quick Action Buttons -->
              <div class="mt-4 space-y-2">
                <button v-if="props.userRoles.includes('superadmin')" @click="router.visit(route('schools.create'))"
                  class="inline-flex items-center px-3 py-2 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                  <i class="fas fa-plus mr-1"></i>
                  Add School
                </button>
                <button v-if="props.userRoles.includes('admin')" @click="router.visit(route('admissions.create'))"
                  class="inline-flex items-center px-3 py-2 text-xs font-medium text-green-700 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                  <i class="fas fa-user-plus mr-1"></i>
                  Add Student
                </button>
                <button v-if="props.userRoles.includes('teacher')"
                  @click="router.visit(route('papersquestions.create'))"
                  class="inline-flex items-center px-3 py-2 text-xs font-medium text-purple-700 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                  <i class="fas fa-file-alt mr-1"></i>
                  Create Paper
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
@import 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
</style>
