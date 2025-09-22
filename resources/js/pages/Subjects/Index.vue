<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="Subjects Management" />

        <div class="max-w-7xl mx-auto w-full px-4 py-6 sm:py-8">
            <!-- Header with responsive design -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div>
                    <h1
                        class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-neutral-100 flex items-center gap-3">
                        <BookOpen class="w-8 h-8 text-blue-600" />
                        Subjects Management
                    </h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                        Manage subjects, assign them to classes and teachers
                    </p>
                </div>
            </div>

            <!-- Mobile-First Responsive Tabs -->
            <div class="mb-6">
                <!-- Mobile Tabs (Primary Design) -->
                <div class="md:hidden">
                    <div
                        class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-neutral-700 p-2">
                        <div class="grid grid-cols-3 gap-2">
                            <!-- Subjects Tab -->
                            <button v-can="'read-subjects'" :class="[
                                'flex flex-col items-center justify-center gap-2 p-4 rounded-lg border-2 transition-all duration-200 font-medium text-sm',
                                activeTab === 'subjects'
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 shadow-sm'
                                    : 'border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 text-gray-600 dark:text-gray-400 hover:border-gray-300 dark:hover:border-neutral-600'
                            ]" @click="activeTab = 'subjects'">
                                <BookOpen class="w-6 h-6" />
                                <span>Subjects</span>
                            </button>

                            <!-- Classes Tab -->
                            <button v-can="'assign-subjects-to-classes'" :class="[
                                'flex flex-col items-center justify-center gap-2 p-4 rounded-lg border-2 transition-all duration-200 font-medium text-sm',
                                activeTab === 'assign-classes'
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 shadow-sm'
                                    : 'border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 text-gray-600 dark:text-gray-400 hover:border-gray-300 dark:hover:border-neutral-600'
                            ]" @click="activeTab = 'assign-classes'">
                                <Building2 class="w-6 h-6" />
                                <span>Classes</span>
                            </button>

                            <!-- Teachers Tab -->
                            <button v-can="'assign-teachers-to-subjects'" :class="[
                                'flex flex-col items-center justify-center gap-2 p-4 rounded-lg border-2 transition-all duration-200 font-medium text-sm',
                                activeTab === 'assign-teachers'
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 shadow-sm'
                                    : 'border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800 text-gray-600 dark:text-gray-400 hover:border-gray-300 dark:hover:border-neutral-600'
                            ]" @click="activeTab = 'assign-teachers'">
                                <Users class="w-6 h-6" />
                                <span>Teachers</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Desktop Tabs (Secondary Design) -->
                <div
                    class="hidden md:flex gap-1 border border-gray-200 dark:border-neutral-700 rounded-lg p-1 bg-gray-50 dark:bg-neutral-800">
                    <button
                        :class="['flex items-center gap-2 px-4 py-3 rounded-md font-medium transition-all', activeTab === 'subjects' ? 'bg-white dark:bg-neutral-700 text-blue-600 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100']"
                        @click="activeTab = 'subjects'">
                        <BookOpen class="w-4 h-4" />
                        Subjects
                    </button>
                    <button
                        :class="['flex items-center gap-2 px-4 py-3 rounded-md font-medium transition-all', activeTab === 'assign-classes' ? 'bg-white dark:bg-neutral-700 text-blue-600 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100']"
                        @click="activeTab = 'assign-classes'">
                        <Building2 class="w-4 h-4" />
                        Assign to Classes
                    </button>
                    <button
                        :class="['flex items-center gap-2 px-4 py-3 rounded-md font-medium transition-all', activeTab === 'assign-teachers' ? 'bg-white dark:bg-neutral-700 text-blue-600 shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100']"
                        @click="activeTab = 'assign-teachers'">
                        <Users class="w-4 h-4" />
                        Assign to Teachers
                    </button>
                </div>
            </div>

            <!-- Subjects Tab -->
            <div v-if="activeTab === 'subjects'">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                            <BookOpen class="w-5 h-5 text-blue-600" />
                            All Subjects
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {{ subjects.length }} subject{{ subjects.length !== 1 ? 's' : '' }} available
                        </p>
                    </div>
                    <Button v-can="'create-subjects'" @click="openCreateModal"
                        class="w-full sm:w-auto px-6 py-3 text-base">
                        <Plus class="w-4 h-4 mr-2" />
                        Add Subject
                    </Button>
                </div>

                <!-- Mobile Card View -->
                <div class="md:hidden space-y-4">
                    <div v-if="subjects.length === 0" class="text-center py-12">
                        <div
                            class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                            <BookOpen class="w-8 h-8 text-gray-400" />
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No subjects yet</h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Create your first subject to get started</p>
                        <Button @click="openCreateModal" class="px-6 py-3">
                            <Plus class="w-4 h-4 mr-2" />
                            Add First Subject
                        </Button>
                    </div>

                    <div v-else v-for="subject in subjects" :key="subject.id"
                        class="bg-white dark:bg-neutral-900 rounded-xl border border-gray-200 dark:border-neutral-700 p-4">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100 text-lg">{{ subject.name }}
                                </h3>
                                <p v-if="subject.code" class="text-sm text-blue-600 dark:text-blue-400 font-medium">
                                    {{ subject.code }}
                                </p>
                                <p v-if="subject.description" class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                    {{ subject.description }}
                                </p>
                            </div>
                            <div class="flex gap-2 ml-4">
                                <button
                                    class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                    @click="openEditModal(subject)" aria-label="Edit Subject" title="Edit Subject">
                                    <Edit class="w-5 h-5" />
                                </button>
                                <button
                                    class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                    @click="handleDelete(subject)" aria-label="Delete Subject" title="Delete Subject">
                                    <Trash2 class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desktop Table View -->
                <div class="hidden md:block">
                    <div
                        class="bg-white dark:bg-neutral-900 rounded-xl shadow border border-gray-200 dark:border-neutral-700 overflow-hidden">
                        <BaseDataTable :headers="subjectHeaders" :items="subjects" :loading="loading">
                            <template #item-actions="row">
                                <div class="flex gap-2">
                                    <button
                                        class="inline-flex items-center justify-center rounded-lg p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                                        @click="openEditModal(row)" aria-label="Edit Subject" title="Edit Subject">
                                        <Edit class="w-4 h-4" />
                                    </button>
                                    <button
                                        class="inline-flex items-center justify-center rounded-lg p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                        @click="handleDelete(row)" aria-label="Delete Subject" title="Delete Subject">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </template>
                        </BaseDataTable>
                    </div>
                </div>
            </div>

            <!-- Assign to Classes Tab -->
            <div v-if="activeTab === 'assign-classes'">
                <div
                    class="bg-white dark:bg-neutral-900 rounded-xl shadow border border-gray-200 dark:border-neutral-700 p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div
                            class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                            <Building2 class="w-5 h-5 text-blue-600" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Assign Subjects to
                                Classes</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Select a class to view and manage
                                subject assignments</p>
                        </div>
                    </div>

                    <!-- Class Selection Row -->
                    <div class="mb-6">
                        <Label for="classSelect"
                            class="block mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <Building2 class="w-4 h-4 inline mr-2" />
                            Select Class <span class="text-red-500">*</span>
                        </Label>
                        <div class="space-y-4">
                            <select id="classSelect" v-model="selectedClass"
                                class="w-full p-3 text-sm border border-gray-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                                <option value="">Choose a class...</option>
                                <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Subject Assignment Section -->
                    <div v-if="selectedClass" class="space-y-6">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                            <BookOpen class="w-5 h-5 text-blue-600" />
                            Assign Subjects to <span class="ml-1 font-bold">{{ selectedClassName }}</span>
                        </h4>

                        <!-- Mobile-friendly subject selection -->
                        <div class="space-y-3">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                Tap subjects to assign or unassign them to {{ selectedClassName }}
                            </p>

                            <div class="grid md:grid-cols-3 sm:grid-cols-1 lg:grid-cols-4 gap-3">
                                <button v-for="subject in subjects" :key="subject.id" type="button"
                                    @click="toggleSubject(subject.id)" :class="[
                                        'flex items-center justify-between p-4 rounded-xl border-2 transition-all duration-200 text-left min-h-[60px]',
                                        assignedSubjectsIds.includes(subject.id)
                                            ? 'border-green-700 bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-200 shadow-sm'
                                            : 'border-gray-200 dark:border-neutral-600 bg-white dark:bg-neutral-800 text-gray-700 dark:text-gray-200 hover:border-gray-300 dark:hover:border-neutral-500'
                                    ]">
                                    <div class="flex-1">
                                        <div class="font-medium text-base">{{ subject.name }}</div>
                                        <div v-if="subject.code" class="text-sm opacity-75">({{ subject.code }})</div>
                                    </div>
                                    <div class="ml-3 flex-shrink-0">
                                        <template v-if="assignedSubjectsIds.includes(subject.id)">
                                            <CheckCircle class="w-6 h-6 text-green-600" />
                                        </template>
                                        <template v-else>
                                            <Plus class="w-6 h-6 text-gray-400" />
                                        </template>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Save Button -->
                        <div class="pt-4">
                            <Button @click="saveSubjectAssignments" :disabled="!selectedClass || loading"
                                class="w-full px-6 py-3 text-sm font-medium">
                                <Loader2 v-if="loading" class="w-5 h-5 mr-3 animate-spin" />
                                <CheckCircle v-else class="w-5 h-5 mr-3" />
                                {{ loading ? 'Saving...' : `Save ${assignedSubjectsIds.length}
                                Subject${assignedSubjectsIds.length
                                        !== 1 ? 's' : ''}` }}
                            </Button>
                        </div>
                    </div>

                    <!-- Empty State when no class selected -->
                    <div v-else class="text-center py-12">
                        <div
                            class="w-20 h-20 mx-auto mb-6 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                            <Building2 class="w-10 h-10 text-gray-400" />
                        </div>
                        <h3 class="text-xl font-medium text-gray-900 dark:text-gray-100 mb-3">Select a Class</h3>
                        <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto">
                            Choose a class from the dropdown above to view and manage subject assignments
                        </p>
                    </div>
                </div>
            </div>

            <!-- Assign to Teachers Tab -->
            <div v-if="activeTab === 'assign-teachers'">
                <div
                    class="bg-white dark:bg-neutral-900 rounded-xl shadow border border-gray-200 dark:border-neutral-700 p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div
                            class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                            <Users class="w-5 h-5 text-green-600" />
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Assign Subjects to
                                Teachers</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Assign specific subjects to teachers for
                                specific classes</p>
                        </div>
                    </div>

                    <!-- Mobile-friendly Assignment Form -->
                    <div class="bg-gray-50 dark:bg-neutral-800 rounded-xl p-4 mb-6">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                            <Plus class="w-5 h-5 text-blue-600" />
                            Quick Assignment
                        </h4>

                        <div class="space-y-4">
                            <div class="flex flex-col md:flex-row gap-2">

                                <!-- Class Selection -->
                                <div class="flex-1 w-full">
                                    <Label for="teacherClassSelect"
                                        class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        <Building2 class="w-4 h-4 inline mr-2" />
                                        Select Class <span class="text-red-500">*</span>
                                    </Label>
                                    <select id="teacherClassSelect" v-model="selectedClass"
                                        class="w-full p-3 text-sm border border-gray-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                                        <option value="">Choose a class...</option>
                                        <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Subject Selection -->
                                <div class="flex-1 w-full">
                                    <Label for="teacherSubjectSelect"
                                        class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        <BookOpen class="w-4 h-4 inline mr-2" />
                                        Select Subject <span class="text-red-500">*</span>
                                    </Label>
                                    <select id="teacherSubjectSelect" v-model="selectedSubject"
                                        class="w-full p-3 text-sm border border-gray-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                                        <option value="">Choose a subject...</option>
                                        <option v-for="subject in availableSubjectsForClass" :key="subject.id"
                                            :value="subject.id">
                                            {{ subject.name }} {{ subject.code ? `(${subject.code})` : '' }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Teacher Selection -->
                                <div class="flex-1 w-full">
                                    <Label for="teacherSelect"
                                        class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        <Users class="w-4 h-4 inline mr-2" />
                                        Select Teacher <span class="text-red-500">*</span>
                                    </Label>
                                    <select id="teacherSelect" v-model="selectedTeacher"
                                        class="w-full p-3 text-sm border border-gray-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                                        <option value="">Choose a teacher...</option>
                                        <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                                            {{ teacher.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Assign Button -->
                            <div class="pt-2">
                                <Button @click="assignTeacherToSubject"
                                    :disabled="!selectedClass || !selectedSubject || !selectedTeacher || loading"
                                    class="w-full px-4 py-3 text-sm font-medium bg-blue-600 hover:bg-blue-700">
                                    <Plus v-if="!loading" class="w-5 h-5 mr-3" />
                                    <Loader2 v-else class="w-5 h-5 mr-3 animate-spin" />
                                    {{ loading ? 'Assigning...' : 'Assign Teacher to Subject' }}
                                </Button>
                            </div>
                        </div>

                        <!-- Help Messages -->
                        <div class="mt-4 space-y-2">
                            <p v-if="selectedClass && availableSubjectsForClass.length === 0"
                                class="text-sm text-orange-600 bg-orange-50 dark:bg-orange-900/20 p-3 rounded-lg">
                                <BookOpen class="w-4 h-4 inline mr-2" />
                                No subjects assigned to this class yet. Please assign subjects to the class first.
                            </p>
                            <p v-if="teachers.length === 0"
                                class="text-sm text-orange-600 bg-orange-50 dark:bg-orange-900/20 p-3 rounded-lg">
                                <Users class="w-4 h-4 inline mr-2" />
                                No teachers available for this school. Please add teachers first.
                            </p>
                        </div>
                    </div>

                    <!-- Teacher Assignments Overview -->
                    <div class="space-y-6">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                            <ClipboardList class="w-5 h-5 text-blue-600" />
                            Current Teacher Assignments
                        </h4>

                        <!-- Teacher Filter -->
                        <div class="flex flex-col md:flex-row w-full">
                            <Label for="teacherFilter"
                                class="md:w-1/6 block my-2 justify-center items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                <Users class="w-4 h-4 inline mr-2" />
                                Filter by Teacher
                            </Label>
                            <select id="teacherFilter" v-model="selectedTeacherFilter"
                                class="md:w-2/6 p-3 text-sm border border-gray-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                                <option value="">All Teachers</option>
                                <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                                    {{ teacher.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Assignments List -->
                        <div class="space-y-4">
                            <div v-for="teacher in filteredTeacherAssignments" :key="teacher.id"
                                class="bg-white dark:bg-neutral-900 rounded-xl p-4 border border-gray-200 dark:border-neutral-700 shadow-sm">

                                <!-- Teacher Header -->
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                            <Users class="w-6 h-6 text-blue-600" />
                                        </div>
                                        <div>
                                            <h5 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                                {{ teacher.name }}
                                            </h5>
                                            <div class="flex flex-wrap items-center gap-2 mt-1">
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ getTeacherClassRole(teacher.id) }}
                                                </p>
                                                <!-- Class Teacher Badge -->
                                                <span v-if="isClassTeacher(teacher.id)"
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                                    <Building2 class="w-3 h-3 mr-1" />
                                                    Class Teacher
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Remove All Button -->
                                    <Button @click="removeTeacherAssignment(teacher.id)" variant="outline" size="sm"
                                        class="text-red-600 hover:text-red-700 border-red-200 hover:border-red-300 px-4 py-2">
                                        <Trash2 class="w-4 h-4 mr-2" />
                                        Remove All
                                    </Button>
                                </div>

                                <!-- Assigned Subjects -->
                                <div v-if="teacher.assignments.length > 0" class="space-y-3">
                                    <h6 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Assigned Subjects:
                                    </h6>
                                    <div class="grid sm:grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-3">
                                        <div v-for="assignment in teacher.assignments"
                                            :key="`${assignment.class_id}-${assignment.subject_id}`"
                                            class="flex items-center justify-between p-3 bg-gray-50 dark:bg-neutral-800 rounded-lg border border-gray-200 dark:border-neutral-600">
                                            <div class="flex items-center gap-3">
                                                <BookOpen class="w-5 h-5 text-blue-600 flex-shrink-0" />
                                                <div>
                                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ assignment.subject_name }}
                                                    </span>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        in {{ assignment.class_name }}
                                                    </div>
                                                </div>
                                            </div>
                                            <button
                                                @click="removeSpecificAssignment(teacher.id, assignment.class_id, assignment.subject_id)"
                                                class="ml-3 p-2 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                                <X class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Empty State -->
                                <div v-else class="text-center py-6">
                                    <div
                                        class="w-12 h-12 mx-auto mb-3 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                                        <BookOpen class="w-6 h-6 text-gray-400" />
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        No subjects assigned yet.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-if="filteredTeacherAssignments.length === 0" class="text-center py-12">
                            <div
                                class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center">
                                <Users class="w-8 h-8 text-gray-400" />
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                                No teacher assignments yet
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400">
                                Use the form above to assign subjects to teachers
                            </p>
                        </div>
                    </div>
                </div>
            </div>


        </div>

        <!-- Create/Edit Subject Modal -->
        <Dialog v-model:open="modalOpen">
            <DialogContent class="sm:max-w-md max-w-[95vw] mx-4">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <BookOpen class="w-5 h-5 text-blue-600" />
                        {{ isEdit ? 'Edit Subject' : 'Add New Subject' }}
                    </DialogTitle>
                </DialogHeader>
                <form @submit.prevent="handleSubmit" class="space-y-6">
                    <div>
                        <Label for="name" class="block mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <FileText class="w-4 h-4 inline mr-2" />
                            Subject Name <span class="text-red-500">*</span>
                        </Label>
                        <Input id="name" v-model="form.name" type="text" required
                            placeholder="e.g., Mathematics, English, Science"
                            class="w-full p-3 text-sm border border-gray-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div>
                        <Label for="code" class="block mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <Hash class="w-4 h-4 inline mr-2" />
                            Subject Code
                        </Label>
                        <Input id="code" v-model="form.code" type="text" required placeholder="e.g., MATH101, ENG201"
                            class="w-full p-3 text-sm border border-gray-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" />
                        <InputError :message="form.errors.code" />
                        <p class="text-xs text-gray-500 mt-2">A short code to identify the subject (e.g., MATH101,
                            ENG201)</p>
                    </div>
                    <div>
                        <Label for="description"
                            class="block mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <AlignLeft class="w-4 h-4 inline mr-2" />
                            Description
                        </Label>
                        <textarea id="description" v-model="form.description"
                            class="w-full p-3 text-sm border border-gray-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 text-gray-900 dark:text-gray-100 resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                            rows="4" placeholder="Brief description of the subject (optional)"></textarea>
                        <InputError :message="form.errors.description" />
                        <p class="text-xs text-gray-500 mt-2">Optional: Provide additional details about the subject</p>
                    </div>
                    <DialogFooter class="flex flex-col sm:flex-row gap-3 pt-4">
                        <Button type="button" variant="outline" @click="closeModal"
                            class="w-full sm:w-auto px-6 py-3 text-base">
                            <X class="w-4 h-4 mr-2" />
                            Cancel
                        </Button>
                        <Button :disabled="loading" class="w-full sm:w-auto px-3 py-3 text-base">
                            <span v-if="loading" class="flex items-center">
                                <Loader2 class="w-4 h-4 mr-2 animate-spin" />
                                {{ isEdit ? 'Updating...' : 'Creating...' }}
                            </span>
                            <span v-else class="flex items-center">
                                <CheckCircle class="w-4 h-4 mr-2" />
                                {{ isEdit ? 'Update Subject' : 'Create Subject' }}
                            </span>
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent class="sm:max-w-md max-w-[95vw] mx-4">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <AlertTriangle class="w-5 h-5 text-red-600" />
                        Delete Subject?
                    </DialogTitle>
                </DialogHeader>
                <div class="mb-6">
                    <div
                        class="flex items-start gap-3 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                        <AlertTriangle class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" />
                        <div>
                            <p class="font-medium text-red-900 dark:text-red-100 mb-2">
                                Are you sure you want to delete "<strong>{{ subjectToDelete?.name }}</strong>"?
                            </p>
                            <p class="text-sm text-red-700 dark:text-red-300">
                                This action cannot be undone. The subject will be permanently removed from the system.
                            </p>
                        </div>
                    </div>
                </div>
                <DialogFooter class="flex flex-col sm:flex-row gap-3">
                    <Button variant="outline" @click="cancelDelete" class="w-full sm:w-auto px-6 py-3 text-base">
                        <X class="w-4 h-4 mr-2" />
                        Cancel
                    </Button>
                    <Button variant="destructive" @click="confirmDelete" class="w-full sm:w-auto px-6 py-3 text-base">
                        <span v-if="loading" class="flex flex-row items-center gap-2">
                            <Loader2 class="w-4 h-4 mr-2 animate-spin" />
                            Deleting...
                        </span>
                        <span v-else class="flex flex-row items-center gap-2">
                            <Trash2 class="w-4 h-4 mr-2" />
                            Delete Subject
                        </span>
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Custom Confirmation Dialog -->
        <Dialog v-model:open="showConfirmDialog">
            <DialogContent class="sm:max-w-md max-w-[95vw] mx-4">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <Info class="w-5 h-5 text-blue-600" />
                        Confirm Action
                    </DialogTitle>
                </DialogHeader>
                <div class="mb-6">
                    <div
                        class="flex items-start gap-3 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <Info class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" />
                        <div>
                            <p class="font-medium text-blue-900 dark:text-blue-100 mb-2">
                                {{ confirmMessage }}
                            </p>
                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                This action cannot be undone.
                            </p>
                        </div>
                    </div>
                </div>
                <DialogFooter class="flex flex-col sm:flex-row gap-3">
                    <Button variant="outline" @click="cancelAction" class="w-full sm:w-auto px-6 py-3 text-base">
                        <X class="w-4 h-4 mr-2" />
                        Cancel
                    </Button>
                    <Button variant="default" @click="confirmActionHandler"
                        class="w-full sm:w-auto px-6 py-3 text-base">
                        <CheckCircle class="w-4 h-4 mr-2" />
                        Confirm
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, watch, defineProps, computed, onMounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import AppLayout from '@/layouts/AppLayout.vue';
import BaseDataTable from '@/components/ui/BaseDataTable.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { BookOpen, Building2, Users, ClipboardList, Plus, Edit, Trash2, Info, CheckCircle, RefreshCw, FileText, Hash, AlignLeft, X, Loader2, AlertTriangle } from 'lucide-vue-next';

const page = usePage()

interface Subject {
    id: number;
    name: string;
    code?: string;
    description?: string;
}

interface ClassModel {
    id: number;
    name: string;
}

interface Teacher {
    id: number;
    name: string;
    teacher?: {
        class_id?: number;
        class?: {
            id: number;
            name: string;
        };
    };
}

interface Props {
    subjects: Subject[];
    classes: ClassModel[];
    teachers: Teacher[];
    assignments: any[];
    teacherAssignments: any[];
}

const props = defineProps<Props>();

const subjects = ref(props.subjects ? [...props.subjects] : []);
const classes = ref(props.classes ? [...props.classes] : []);
const teachers = ref(props.teachers ? [...props.teachers] : []);
const assignments = ref(props.assignments ? [...props.assignments] : []);
const teacherAssignments = ref(props.teacherAssignments ? [...props.teacherAssignments] : []);

watch(() => props.subjects, (val) => {
    subjects.value = val ? [...val] : [];
});
watch(() => props.classes, (val) => {
    classes.value = val ? [...val] : [];
});
watch(() => props.teachers, (val) => {
    teachers.value = val ? [...val] : [];
});
watch(() => props.assignments, (val) => {
    assignments.value = val ? [...val] : [];
});
watch(() => props.teacherAssignments, (val) => {
    teacherAssignments.value = val ? [...val] : [];
});

const loading = ref(false);
const modalOpen = ref(false);
const isEdit = ref(false);
const editingSubject = ref<{ id: number; name: string; code?: string; description?: string } | null>(null);
const showDeleteDialog = ref(false);
const subjectToDelete = ref<{ id: number; name: string } | null>(null);
const activeTab = ref('subjects');

// Custom confirmation modal state
const showConfirmDialog = ref(false);
const confirmMessage = ref('');
const confirmAction = ref<(() => void) | null>(null);

// Assignment states
const selectedClass = ref<string>('');
const selectedSubject = ref<string>('');
const selectedTeacher = ref<string>('');
const selectedTeacherFilter = ref<string>('');
const selectedSubjects = ref<number[]>([]);
const assignedSubjects = ref<Array<{ id: number; name: string; code?: string }>>([]);
const assignedTeachers = ref<Array<{ id: number; name: string }>>([]);

// Computed properties for subject management
const availableSubjects = computed(() => {
    if (!selectedClass.value) return [];
    const assignedIds = assignedSubjects.value.map(s => s.id);
    return subjects.value.filter(subject => !assignedIds.includes(subject.id));
});

const selectedClassName = computed(() => {
    const cls = classes.value.find(c => c.id.toString() === selectedClass.value.toString());
    return cls ? cls.name : '';
});

const selectedSubjectName = computed(() => {
    const subject = subjects.value.find(s => s.id.toString() === selectedSubject.value.toString());
    return subject ? subject.name : '';
});

const assignedSubjectsIds = computed(() => assignedSubjects.value.map(s => s.id));

// Computed property for available subjects based on selected class (for teacher assignment)
const availableSubjectsForClass = computed(() => {
    if (!selectedClass.value) return [];
    return subjects.value.filter(subject => assignedSubjectsIds.value.includes(subject.id));
});

// Computed property for filtered teacher assignments
const filteredTeacherAssignments = computed(() => {
    const assignments = teacherAssignments.value;
    const teacherMap = new Map();

    // Group assignments by teacher
    assignments.forEach(assignment => {
        const teacherId = assignment.teacher_user_id; // Use teacher_user_id instead of teacher_id
        if (!teacherMap.has(teacherId)) {
            const teacher = teachers.value.find(t => t.id === teacherId);
            if (teacher) {
                teacherMap.set(teacherId, {
                    id: teacher.id,
                    name: teacher.name,
                    assignments: []
                });
            }
        }

        if (teacherMap.has(teacherId)) {
            teacherMap.get(teacherId).assignments.push({
                class_id: assignment.class_id,
                class_name: assignment.class_name,
                subject_id: assignment.subject_id,
                subject_name: assignment.subject_name,
                subject_code: assignment.subject_code
            });
        }
    });

    let result = Array.from(teacherMap.values());

    // Filter by selected teacher if specified
    if (selectedTeacherFilter.value) {
        result = result.filter(teacher => teacher.id.toString() === selectedTeacherFilter.value.toString());
    }

    return result;
});

function toggleSubject(subjectId: number) {
    const idx = assignedSubjectsIds.value.indexOf(subjectId);
    if (idx === -1) {
        // Add subject
        const subject = subjects.value.find(s => s.id === subjectId);
        if (subject) assignedSubjects.value.push(subject);
    } else {
        // Remove subject
        assignedSubjects.value = assignedSubjects.value.filter(s => s.id !== subjectId);
    }
}

function saveSubjectAssignments() {
    if (!selectedClass.value) {
        toast.error('Please select a class first.');
        return;
    }

    // Validate that the selected class exists
    const selectedClassObj = classes.value.find(c => c.id.toString() === selectedClass.value.toString());
    if (!selectedClassObj) {
        toast.error('Selected class not found. Please select a valid class.');
        return;
    }

    // Show confirmation dialog
    const subjectCount = assignedSubjects.value.length;
    const message = subjectCount === 0
        ? `Are you sure you want to remove all subjects from ${selectedClassObj.name}?`
        : `Are you sure you want to assign ${subjectCount} subject${subjectCount !== 1 ? 's' : ''} to ${selectedClassObj.name}?`;

    showConfirmation(message, () => {
        loading.value = true;

        router.post(route('subjects.assign-to-class', selectedClass.value.toString()),
            { subject_ids: assignedSubjects.value.map(s => s.id) },
            {
                preserveScroll: true,
                onSuccess: (page) => {
                    const successMessage = subjectCount === 0
                        ? `All subjects removed from ${selectedClassObj.name} successfully!`
                        : `${subjectCount} subject${subjectCount !== 1 ? 's' : ''} assigned to ${selectedClassObj.name} successfully!`;
                    toast.success(successMessage);
                    // Refresh assignments data using Inertia
                    router.visit(route('subjects.index'), {
                        only: ['assignments'],
                        preserveState: true,
                        preserveScroll: true,
                        replace: true
                    });
                },
                onError: (errors) => {
                    const errorMessage = errors.message || errors.error || 'Failed to assign subjects';
                    toast.error(`Assignment failed: ${errorMessage}`);
                },
                onFinish: () => {
                    loading.value = false;
                }
            }
        );
    });
}

// Initialize teacher assignments from props
watch(() => props.teacherAssignments, (val) => {
    teacherAssignments.value = val ? [...val] : [];
}, { immediate: true });

const form = ref({
    name: '',
    code: '',
    description: '',
    errors: { name: '', code: '', description: '' } as { name: string; code: string; description: string }
});

const breadcrumbs = [
    { title: 'Dashboard', href: '/' },
    { title: 'Subjects', href: '/subjects' }
];

const subjectHeaders = [
    { text: 'ID', value: 'id' },
    { text: 'Name', value: 'name' },
    { text: 'Code', value: 'code' },
    { text: 'Description', value: 'description' },
    { text: 'Actions', value: 'actions', sortable: false, slotName: 'item-actions' },
];

function openCreateModal() {
    isEdit.value = false;
    form.value = { name: '', code: '', description: '', errors: { name: '', code: '', description: '' } };
    modalOpen.value = true;
}

function openEditModal(subject: { id: number; name: string; code?: string; description?: string }) {
    isEdit.value = true;
    editingSubject.value = subject;
    form.value = {
        name: subject.name,
        code: subject.code || '',
        description: subject.description || '',
        errors: { name: '', code: '', description: '' }
    };
    modalOpen.value = true;
}

function closeModal() {
    modalOpen.value = false;
    form.value = { name: '', code: '', description: '', errors: { name: '', code: '', description: '' } };
    editingSubject.value = null;
}

async function handleSubmit() {
    loading.value = true;
    form.value.errors = { name: '', code: '', description: '' };

    try {
        if (isEdit.value && editingSubject.value) {
            router.put(route('subjects.update', editingSubject.value.id), {
                name: form.value.name,
                code: form.value.code,
                description: form.value.description
            }, {
                onSuccess: () => {
                    closeModal();
                    router.visit(route('subjects.index'), {
                        only: ['subjects'],
                        preserveState: true,
                        preserveScroll: true,
                        replace: true
                    });
                    toast.success('Subject updated successfully!');
                },
                onError: (errors) => {
                    form.value.errors = {
                        name: errors.name || '',
                        code: errors.code || '',
                        description: errors.description || ''
                    };
                    console.error('Update errors:', errors);
                    toast.error(errors.message)
                },
                onFinish: () => {
                    loading.value = false;
                }
            });
        } else {
            router.post(route('subjects.store'), {
                name: form.value.name,
                code: form.value.code,
                description: form.value.description
            }, {
                onSuccess: () => {
                    toast.success('Subject created successfully!');
                    closeModal();
                    router.visit(route('subjects.index'), {
                        only: ['subjects'],
                        preserveState: true,
                        preserveScroll: true,
                        replace: true
                    });
                },
                onError: (errors) => {
                    form.value.errors = {
                        name: errors.name || '',
                        code: errors.code || '',
                        description: errors.description || ''
                    };
                    toast.error(errors.message)
                    console.error('Creation errors:', errors);
                },
                onFinish: () => {
                    loading.value = false;
                }
            });
        }
    } catch (e) {
        console.error('Error in handleSubmit:', e);
        toast.error('An error occurred: ' + (e as Error).message);
        loading.value = false;
    }
}

function handleDelete(subject: { id: number; name: string }) {
    subjectToDelete.value = subject;
    showDeleteDialog.value = true;
}

async function confirmDelete() {
    if (!subjectToDelete.value) return;
    loading.value = true;

    router.delete(route('subjects.destroy', subjectToDelete.value.id), {
        onSuccess: () => {
            toast.success('Subject deleted!');
            showDeleteDialog.value = false;
            subjectToDelete.value = null;
            router.visit(route('subjects.index'), {
                only: ['subjects'],
                preserveState: true,
                preserveScroll: true,
                replace: true
            });
        },
        onError: () => {
            toast.error('Failed to delete subject.');
        },
        onFinish: () => {
            loading.value = false;
        }
    });
}

function cancelDelete() {
    showDeleteDialog.value = false;
    subjectToDelete.value = null;
}

async function loadAssignments() {
    // Refresh assignments data using Inertia
    router.visit(route('subjects.index'), {
        only: ['assignments'],
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
}



async function loadClassAssignments() {
    if (!selectedClass.value) {
        assignedSubjects.value = [];
        return;
    }

    // Validate that the selected class exists
    const selectedClassObj = classes.value.find(c => c.id.toString() === selectedClass.value.toString());
    if (!selectedClassObj) {
        toast.error('Selected class not found. Please select a valid class.');
        return;
    }

    loading.value = true;

    try {
        // Use existing assignments data instead of making a new request
        const classAssignments = assignments.value.filter(a => a.class_id.toString() === selectedClass.value.toString());
        assignedSubjects.value = classAssignments.map(a => ({
            id: a.subject_id,
            name: a.subject_name,
            code: a.subject_code
        }));

        if (assignedSubjects.value.length > 0) {
            // toast.success(`Loaded ${assignedSubjects.value.length} assigned subjects for ${selectedClassObj.name}`);
        } else {
            toast.info(`No subjects currently assigned to ${selectedClassObj.name}`);
        }
    } catch (error: any) {
        // Handle errors
        const errorMessage = error.message || 'Failed to load assignments';
        toast.error(`Error: ${errorMessage}`);
        assignedSubjects.value = [];
    } finally {
        loading.value = false;
    }
}


async function unassignSubject(subjectId: number) {
    showConfirmation('Are you sure you want to unassign this subject?', () => {
        loading.value = true;

        router.post(route('subjects.remove-subject-from-class'),
            {
                class_id: selectedClass.value,
                subject_id: subjectId
            },
            {
                onSuccess: () => {
                    toast.success('Subject unassigned successfully!');
                    // Remove from assigned subjects
                    assignedSubjects.value = assignedSubjects.value.filter(s => s.id !== subjectId);
                },
                onError: () => {
                    toast.error('Failed to unassign subject');
                },
                onFinish: () => {
                    loading.value = false;
                }
            });
    });
}



// Load assignments when assignments tab is opened
watch(activeTab, (newTab) => {
    if (newTab === 'assignments') {
        loadAssignments();
    } else if (newTab === 'assign-teachers') {
        refreshTeacherAssignments();
    }
});

// Teacher assignment logic
const assignedTeachersIds = computed(() => assignedTeachers.value.map(t => t.id));

// Helper function to get teacher name by ID
function getTeacherName(teacherId: string | number): string {
    const teacher = teachers.value.find(t => t.id.toString() === teacherId.toString());
    return teacher ? teacher.name : 'Unknown Teacher';
}

// Add teacher to assignment
function addTeacher(teacherId: string | number) {
    const teacher = teachers.value.find(t => t.id.toString() === teacherId.toString());
    if (teacher && !assignedTeachersIds.value.includes(teacher.id)) {
        assignedTeachers.value.push(teacher);
        selectedTeacher.value = ''; // Reset dropdown
        toast.success(`${teacher.name} added to assignment`);
    }
}

// Remove teacher from assignment
function removeTeacher(teacherId: string | number) {
    const id = typeof teacherId === 'string' ? parseInt(teacherId) : teacherId;
    assignedTeachers.value = assignedTeachers.value.filter(t => t.id !== id);
    toast.success('Teacher removed from assignment');
}

// Function to refresh teacher assignments data using Inertia
function refreshTeacherAssignments() {
    router.visit(route('subjects.index'), {
        only: ['teacherAssignments'],
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
}

// New function for compact assignment
function assignTeacherToSubject() {
    if (!selectedClass.value || !selectedSubject.value || !selectedTeacher.value) {
        toast.error('Please select class, subject, and teacher.');
        return;
    }

    const selectedClassObj = classes.value.find(c => c.id.toString() === selectedClass.value.toString());
    const selectedSubjectObj = subjects.value.find(s => s.id.toString() === selectedSubject.value.toString());
    const selectedTeacherObj = teachers.value.find(t => t.id.toString() === selectedTeacher.value.toString());

    if (!selectedClassObj || !selectedSubjectObj || !selectedTeacherObj) {
        toast.error('Selected class, subject, or teacher not found. Please select valid options.');
        return;
    }

    loading.value = true;

    router.post(route('subjects.assign-to-teacher'), {
        class_id: selectedClass.value.toString(),
        subject_id: selectedSubject.value.toString(),
        teacher_ids: [selectedTeacherObj.id]
    }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`${selectedTeacherObj.name} assigned to ${selectedSubjectObj.name} in ${selectedClassObj.name} successfully!`);
            // Reset form
            selectedTeacher.value = '';
            // Refresh teacher assignments data using Inertia
            refreshTeacherAssignments();
        },
        onError: (errors) => {
            const errorMessage = errors.message || errors.error || 'Failed to assign teacher';
            toast.error(`Assignment failed: ${errorMessage}`);
        },
        onFinish: () => {
            loading.value = false;
        }
    });
}

// Helper function to get teacher's class role
function getTeacherClassRole(teacherId: number): string {
    const teacher = teachers.value.find(t => t.id === teacherId);
    if (!teacher || !teacher.teacher) return "Unknown Teacher";

    if (teacher.teacher?.class_id) {
        const classObj = classes.value.find(c => c.id === teacher.teacher?.class_id);
        if (classObj) {
            return `Class Teacher of ${classObj.name}`;
        }
    }
    return "Subject Teacher";
}

// Helper function to check if a teacher is a class teacher
function isClassTeacher(teacherId: number): boolean {
    const teacher = teachers.value.find(t => t.id === teacherId);
    if (!teacher || !teacher.teacher) return false;

    return !!teacher.teacher?.class_id;
}

// Remove all assignments for a teacher
function removeTeacherAssignment(teacherId: number) {
    const teacher = teachers.value.find(t => t.id === teacherId);
    const teacherName = teacher ? teacher.name : 'Unknown Teacher';

    showConfirmation(`Are you sure you want to remove all assignments for ${teacherName}?`, () => {
        loading.value = true;

        router.post(route('subjects.remove-teacher-assignment'),
            { teacher_id: teacherId },
            {
                onSuccess: () => {
                    toast.success(`All assignments removed for ${teacherName} successfully!`);
                    refreshTeacherAssignments();
                },
                onError: (errors) => {
                    const errorMessage = errors.message || errors.error || 'Failed to remove assignments';
                    toast.error(`Failed to remove assignments: ${errorMessage}`);
                },
                onFinish: () => {
                    loading.value = false;
                }
            });
    });
}

// Remove specific assignment
function removeSpecificAssignment(teacherId: number, classId: number, subjectId: number) {
    const teacher = teachers.value.find(t => t.id === teacherId);
    const teacherName = teacher ? teacher.name : 'Unknown Teacher';
    const className = classes.value.find(c => c.id === classId)?.name || 'Unknown Class';
    const subjectName = subjects.value.find(s => s.id === subjectId)?.name || 'Unknown Subject';

    showConfirmation(`Are you sure you want to remove ${teacherName} from ${subjectName} in ${className}?`, () => {
        loading.value = true;

        router.post(
            route('subjects.remove-specific-assignment'),
            {
                teacher_id: teacherId,
                class_id: classId,
                subject_id: subjectId,
            },
            {
                onSuccess: () => {
                    toast.success('Assignment removed successfully!');
                    refreshTeacherAssignments();
                },
                onError: (errors: any) => {
                    const errorMessage = errors.message || errors.error || 'Failed to remove assignment';
                    toast.error(`Failed to remove assignment: ${errorMessage}`);
                },
                onFinish: () => {
                    loading.value = false;
                }
            }
        );

    });
}

// Watch for changes in class or subject selection
watch([selectedClass, selectedSubject], () => {
    // Reset teacher selection when class or subject changes
    selectedTeacher.value = '';
});

// Watch for class selection to load assigned subjects
watch(selectedClass, (newClass) => {
    if (newClass) {
        loadClassAssignments();
    } else {
        assignedSubjects.value = [];
    }
});

// Watch for teacher filter changes to refresh data
watch(selectedTeacherFilter, (newValue, oldValue) => {
    // Only refresh if we're on the teacher assignment tab and the filter changed
    if (activeTab.value === 'assign-teachers' && newValue !== oldValue) {
        // For filtering, we don't need to make a new request since we already have the data
        // The computed property will handle the filtering
    }
});

// Call debug on component mount
onMounted(() => {
    // Component mounted - data will be loaded from props
});
// Custom confirmation functions
function showConfirmation(message: string, action: () => void) {
    confirmMessage.value = message;
    confirmAction.value = action;
    showConfirmDialog.value = true;
}

function confirmActionHandler() {
    if (confirmAction.value) {
        confirmAction.value();
    }
    showConfirmDialog.value = false;
    confirmMessage.value = '';
    confirmAction.value = null;
}

function cancelAction() {
    showConfirmDialog.value = false;
    confirmMessage.value = '';
    confirmAction.value = null;
}
</script>