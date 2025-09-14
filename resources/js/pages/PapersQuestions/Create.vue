<template>
    <AppLayout>

        <Head title="Create Paper" />

        <div class="max-w-7xl mx-auto w-full px-2 sm:px-4 md:px-6 lg:px-8 py-4 sm:py-8">
            <div class="bg-white dark:bg-neutral-900 rounded-lg shadow-md p-4 sm:p-6">
                <div class="flex items-center justify-between my-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Create New Paper</h1>
                    <Button variant="outline" @click="goBack">Back to Papers</Button>
                </div>
                <!-- Help or Instruction section -->
                <div
                    class="my-3 p-6 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                    <h4 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-4 flex items-center gap-2">
                        <HelpCircle class="w-5 h-5" />
                        Quick Tips
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-blue-800 dark:text-blue-200">
                        <div>
                            <p class="font-medium mb-2 flex items-center gap-2">
                                <FileText class="w-4 h-4" />
                                Adding Questions:
                            </p>
                            <ul class="space-y-1 ml-6">
                                <li>• Click "Add Question" to add new questions</li>
                                <li>• Fill in all required fields (marked with *)</li>
                                <li>• Use different sections to organize your paper</li>
                            </ul>
                        </div>
                        <div>
                            <p class="font-medium mb-2 flex items-center gap-2">
                                <Target class="w-4 h-4" />
                                Question Types:
                            </p>
                            <ul class="space-y-1 ml-6">
                                <li>• Multiple Choice: Add 2-4 options</li>
                                <li>• True/False: Simple yes/no questions</li>
                                <li>• Short/Long Answer: Text-based responses</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- create form -->
                <form @submit.prevent="submitForm" class="space-y-4 sm:space-y-6">
                    <!-- Paper Information -->
                    <div class="bg-gray-50 dark:bg-neutral-800 rounded-lg p-3 sm:p-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Paper Information</h3>

                        <!-- First Row: Title, Class, Section -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <!-- Title -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                    <FileText class="w-4 h-4 inline mr-2" />
                                    Paper Title <span class="text-red-500">*</span>
                                </label>
                                <input v-model="form.title" type="text" placeholder="Enter paper title"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    :class="{ 'border-red-500 ring-red-500': errors.title }" />
                                <InputError :message="errors.title" />
                            </div>

                            <!-- Class Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                    <Building2 class="w-4 h-4 inline mr-2" />
                                    Class <span class="text-red-500">*</span>
                                </label>
                                <select v-model="form.class_id"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    :class="{ 'border-red-500 ring-red-500': errors.class_id }">
                                    <option value="">Select Class</option>
                                    <option v-for="classItem in props.classes" :key="classItem.id" :value="classItem.id"
                                        class="py-2">
                                        {{ classItem.name }}
                                    </option>
                                </select>
                                <InputError :message="errors.class_id" />
                            </div>

                            <!-- Section Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                    <BookOpen class="w-4 h-4 inline mr-2" />
                                    Section (Optional)
                                </label>
                                <select v-model="form.section_id"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    :class="{ 'border-red-500 ring-red-500': errors.section_id }">
                                    <option value="">Select Section</option>
                                    <option v-for="section in props.sections" :key="section.id" :value="section.id"
                                        class="py-2">
                                        {{ section.name }}
                                    </option>
                                </select>
                                <InputError :message="errors.section_id" />
                            </div>
                        </div>

                        <!-- Second Row: Teacher, Published -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Teacher Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                    <Users class="w-4 h-4 inline mr-2" />
                                    Teacher <span class="text-red-500">*</span>
                                </label>
                                <select v-model="form.teacher_id"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    :class="{ 'border-red-500 ring-red-500': errors.teacher_id }">
                                    <option value="">Select Teacher</option>
                                    <option v-for="teacher in props.teachers" :key="teacher.id" :value="teacher.id"
                                        class="py-2">
                                        {{ teacher.name }}
                                    </option>
                                </select>
                                <InputError :message="errors.teacher_id" />
                            </div>

                            <!-- Published Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                    <CheckCircle class="w-4 h-4 inline mr-2" />
                                    Status
                                </label>
                                <div
                                    class="flex items-center space-x-4 p-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-neutral-900">
                                    <label class="flex items-center cursor-pointer">
                                        <input v-model="form.published" type="checkbox" value="true"
                                            class="w-4 h-4 rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-200">
                                            Published
                                        </span>
                                    </label>
                                </div>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    <Info class="w-4 h-4 inline mr-1" />
                                    Published papers are visible to students
                                </p>
                                <InputError :message="errors.published" />
                            </div>
                        </div>
                    </div>

                    <!-- Subject Details -->
                    <div class="bg-gray-50 dark:bg-neutral-800 rounded-lg p-3 sm:p-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3 sm:mb-4">Subject Details
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Subject Selection (Admin/SuperAdmin) or Display (Teachers) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                    <BookOpen class="w-4 h-4 inline mr-2" />
                                    Subject <span class="text-red-500">*</span>
                                </label>

                                <!-- For Admin/SuperAdmin: Show subject dropdown -->
                                <div v-if="isAdmin">
                                    <select v-model="form.subject_id" @change="onSubjectChange"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                        :class="{ 'border-red-500 ring-red-500': errors.subject_id }"
                                        :disabled="!form.class_id">
                                        <option :value="''">{{ subjectPlaceholderText }}</option>
                                        <option v-for="subject in getAvailableSubjects" :key="subject.id"
                                            :value="subject.id" class="py-2">
                                            {{ subject.name }} ({{ subject.code }})
                                        </option>
                                    </select>

                                    <!-- Warning when no subjects are available -->
                                    <div v-if="form.class_id && getAvailableSubjects.length === 0"
                                        class="mt-2 p-2 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 rounded text-xs">
                                        <div class="flex items-start gap-1">
                                            <AlertCircle class="w-3 h-3 text-orange-600 mt-0.5 flex-shrink-0" />
                                            <div>
                                                <p class="text-orange-800 dark:text-orange-200 font-medium">
                                                    No subjects assigned
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- For Teachers: Show read-only subject display -->
                                <div v-else
                                    class="w-full border border-green-200 dark:border-green-700 rounded-lg px-3 py-2 bg-green-50 dark:bg-green-900/20">
                                    <div class="flex items-center gap-2">
                                        <BookOpen class="w-4 h-4 text-green-600" />
                                        <span v-if="getAvailableSubjects.length > 0"
                                            class="text-green-800 dark:text-green-200 text-sm font-medium">
                                            {{ getAvailableSubjects[0].name }}
                                        </span>
                                        <span v-else class="text-gray-500 text-sm">No subjects assigned</span>
                                    </div>
                                </div>

                                <InputError :message="errors.subject_id" />
                            </div>

                            <!-- Total Marks -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                    <Target class="w-4 h-4 inline mr-2" />
                                    Total Marks
                                </label>
                                <input v-model="form.total_marks" type="number" min="1" placeholder="e.g., 35"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    :class="{ 'border-red-500 ring-red-500': errors.total_marks }" />
                                <InputError :message="errors.total_marks" />
                            </div>

                            <!-- Time Duration -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                    <Clock class="w-4 h-4 inline mr-2" />
                                    Time Duration (Minutes)
                                </label>
                                <input v-model="form.time_duration" type="number" min="1" placeholder="e.g., 90"
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    :class="{ 'border-red-500 ring-red-500': errors.time_duration }" />
                                <InputError :message="errors.time_duration" />
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-3">
                                <BookOpen class="w-4 h-4 inline mr-2" />
                                Instructions for Students
                            </label>
                            <textarea v-model="form.instructions" rows="3"
                                placeholder="Enter any special instructions for students (e.g., 'All questions are compulsory', 'Show your work for numerical questions')..."
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                :class="{ 'border-red-500 ring-red-500': errors.instructions }"></textarea>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                <Info class="w-4 h-4 inline mr-1" />
                                These instructions will appear at the top of the exam paper for students
                            </p>
                            <InputError :message="errors.instructions" />
                        </div>
                    </div>

                    <!-- Questions Section -->
                    <div class="bg-gray-50 dark:bg-neutral-800 rounded-lg p-4 sm:p-6">
                        <div class="flex items-center justify-between mb-4 sm:mb-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Questions</h3>

                        </div>

                        <div v-if="questions.length === 0" class="text-center py-12 text-gray-500 dark:text-gray-400">
                            <div
                                class="w-16 h-16 mx-auto mb-4 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                <FileText class="w-8 h-8 text-gray-400" />
                            </div>
                            <p class="text-lg font-medium mb-2">No questions added yet</p>
                            <p class="text-sm">Click "Add Question" to start creating your exam paper</p>
                        </div>

                        <div v-else class="space-y-4">
                            <div v-for="(question, index) in questions" :key="index"
                                class="border-2 border-gray-200 dark:border-gray-700 rounded-xl p-4 sm:p-6 bg-white dark:bg-neutral-900 shadow-sm">
                                <div class="flex items-center justify-between mb-3 sm:mb-4">
                                    <h4
                                        class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                        <span
                                            class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs sm:text-sm font-bold">
                                            {{ index + 1 }}
                                        </span>
                                        Question {{ index + 1 }}
                                    </h4>
                                    <Button type="button" variant="outline" size="sm" @click="removeQuestion(index)"
                                        class="text-red-600 hover:text-red-700 px-2 py-1 sm:px-4 sm:py-2 text-xs sm:text-sm">
                                        <Trash2 class="w-3 h-3 sm:w-4 sm:h-4 inline mr-1" />
                                        Remove
                                    </Button>
                                </div>

                                <!-- Question Text -->
                                <div class="mb-4 sm:mb-6">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                        <FileText class="w-4 h-4 inline mr-2" />
                                        Question Text <span class="text-red-500">*</span>
                                    </label>
                                    <textarea v-model="question.text" rows="2" placeholder="Enter your question here..."
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                        :class="{ 'border-red-500 ring-red-500': getError(`questions.${index}.text`) }"></textarea>
                                    <InputError :message="getError(`questions.${index}.text`)" />
                                </div>

                                <!-- Question Type and Section -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 mb-4 sm:mb-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                            <Target class="w-4 h-4 inline mr-2" />
                                            Question Type <span class="text-red-500">*</span>
                                        </label>
                                        <select v-model="question.type"
                                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                            :class="{ 'border-red-500 ring-red-500': getError(`questions.${index}.type`) }">
                                            <option value="">Select Type</option>
                                            <option value="multiple_choice" class="py-2">Multiple Choice</option>
                                            <option value="true_false" class="py-2">True/False</option>
                                            <option value="short_answer" class="py-2">Short Answer</option>
                                            <option value="long_answer" class="py-2">Long Answer</option>
                                            <option value="essay" class="py-2">Essay</option>
                                            <option value="numerical" class="py-2">Numerical</option>
                                        </select>
                                        <InputError :message="getError(`questions.${index}.type`)" />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                            <BookOpen class="w-4 h-4 inline mr-2" />
                                            Section <span class="text-red-500">*</span>
                                        </label>
                                        <select v-model="question.section"
                                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                            :class="{ 'border-red-500 ring-red-500': getError(`questions.${index}.section`) }">
                                            <option value="">Select Section</option>
                                            <option value="objective" class="py-2">Objective</option>
                                            <option value="short_questions" class="py-2">Short Questions</option>
                                            <option value="long_questions" class="py-2">Long Questions</option>
                                            <option value="essay" class="py-2">Essay</option>
                                        </select>
                                        <InputError :message="getError(`questions.${index}.section`)" />
                                    </div>
                                </div>

                                <!-- Marks and Question Number -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 mb-4 sm:mb-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                            <Target class="w-4 h-4 inline mr-2" />
                                            Marks <span class="text-red-500">*</span>
                                        </label>
                                        <input v-model="question.marks" type="number" min="1" placeholder="Enter marks"
                                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                            :class="{ 'border-red-500 ring-red-500': getError(`questions.${index}.marks`) }" />
                                        <InputError :message="getError(`questions.${index}.marks`)" />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                            <FileText class="w-4 h-4 inline mr-2" />
                                            Question Number (Optional)
                                        </label>
                                        <input v-model="question.question_number" type="number" min="1"
                                            placeholder="Question number"
                                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                            :class="{ 'border-red-500 ring-red-500': getError(`questions.${index}.question_number`) }" />
                                        <InputError :message="getError(`questions.${index}.question_number`)" />
                                    </div>
                                </div>

                                <!-- Answer -->
                                <div class="mb-4 sm:mb-6">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                                        <CheckCircle class="w-4 h-4 inline mr-2" />
                                        Correct Answer (Optional)
                                    </label>
                                    <input v-model="question.answer" type="text" placeholder="Enter correct answer"
                                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-neutral-900 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                        :class="{ 'border-red-500 ring-red-500': getError(`questions.${index}.answer`) }" />
                                    <InputError :message="getError(`questions.${index}.answer`)" />
                                </div>

                                <!-- Options for Multiple Choice -->
                                <div v-if="question.type === 'multiple_choice'" class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-3">
                                        <BookOpen class="w-4 h-4 inline mr-2" />
                                        Options <span class="text-red-500">*</span>
                                    </label>

                                    <!-- Options Grid -->
                                    <div class="space-y-3">
                                        <div v-for="(option, optionIndex) in question.options" :key="optionIndex"
                                            class="relative group">
                                            <!-- Option Card -->
                                            <div
                                                class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-neutral-800 rounded-lg border border-gray-200 dark:border-neutral-700 hover:border-blue-300 dark:hover:border-blue-600 transition-colors">
                                                <!-- Option Letter Badge -->
                                                <div class="flex-shrink-0">
                                                    <span
                                                        class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-sm">
                                                        {{ String.fromCharCode(97 + optionIndex) }}
                                                    </span>
                                                </div>

                                                <!-- Option Input -->
                                                <div class="flex-1 min-w-0">
                                                    <input v-model="question.options[optionIndex]" type="text"
                                                        :placeholder="`Enter option ${optionIndex + 1}`"
                                                        class="w-full border-0 bg-transparent text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-0 text-sm font-medium"
                                                        :class="{ 'text-red-600 dark:text-red-400': !question.options[optionIndex].trim() }" />
                                                </div>

                                                <!-- Remove Button (Hidden by default, shown on hover/focus) -->
                                                <div
                                                    class="flex-shrink-0 opacity-0 group-hover:opacity-100 group-focus-within:opacity-100 transition-opacity">
                                                    <button type="button" @click="removeOption(index, optionIndex)"
                                                        class="w-8 h-8 bg-red-100 dark:bg-red-900/30 hover:bg-red-200 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 rounded-full flex items-center justify-center transition-colors"
                                                        title="Remove option">
                                                        <Trash2 class="w-4 h-4" />
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Empty State Warning -->
                                            <div v-if="!question.options[optionIndex].trim()" class="mt-1 ml-11">
                                                <p class="text-xs text-red-500 dark:text-red-400">Please enter option
                                                    text</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Add Option Button -->
                                    <div class="mt-4">
                                        <button type="button" @click="addOption(index)"
                                            class="w-full sm:w-auto px-3 py-2 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-300 border-2 border-dashed border-blue-300 dark:border-blue-600 rounded-lg flex items-center justify-center gap-2 transition-colors font-medium">
                                            <Plus class="w-5 h-5" />
                                            <span>Add Another Option</span>
                                        </button>
                                    </div>

                                    <!-- Help Text -->
                                    <div class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                        <div class="flex items-start gap-2">
                                            <Info
                                                class="w-4 h-4 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" />
                                            <div class="text-sm text-blue-700 dark:text-blue-300">
                                                <p class="font-medium mb-1">Multiple Choice Tips:</p>
                                                <ul class="space-y-1 text-xs">
                                                    <li>• Add 2-4 options for best results</li>
                                                    <li>• Make options clear and distinct</li>
                                                    <li>• Hover over options to remove them</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <InputError :message="getError(`questions.${index}.options`)" />
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-end my-4 sm:my-6">
                            <Button type="button" variant="outline" @click="addQuestion" class="px-4 py-2 text-sm">
                                <Plus class="w-4 h-4 inline mr-2" />
                                Add Question
                            </Button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex flex-col sm:flex-row gap-4 mt-8">
                        <Button type="button" variant="outline" @click="goBack" class="px-6 py-3 text-sm">
                            <ArrowLeft class="w-4 h-4 inline mr-2" />
                            Back to Papers
                        </Button>
                        <Button type="submit" :disabled="form.processing"
                            class="px-6 py-3 text-sm bg-green-600 hover:bg-green-700">
                            <span v-if="form.processing">
                                <Loader2 class="w-4 h-4 inline mr-2 animate-spin" />
                                Creating Paper...
                            </span>
                            <span v-else>
                                <CheckCircle class="w-4 h-4 inline mr-2" />
                                Create Paper
                            </span>
                        </Button>
                    </div>
                    <!-- Help Section -->

                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed, reactive, ref, watch, onMounted } from 'vue';
import { router, Head, usePage } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import InputError from '@/components/InputError.vue';
import { toast } from 'vue3-toastify';
import {
    FileText,
    Building2,
    BookOpen,
    Users,
    CheckCircle,
    Target,
    Clock,
    HelpCircle,
    Plus,
    Trash2,
    Edit,
    AlertCircle,
    Info,
    Loader2,
    ArrowLeft
} from 'lucide-vue-next';

interface ClassModel {
    id: number;
    name: string;
}

interface Section {
    id: number;
    name: string;
}

interface Teacher {
    id: number;
    name: string;
}

interface Subject {
    id: number;
    name: string;
    code: string;
}

interface Question {
    text: string;
    type: string;
    section: string;
    marks: number;
    question_number?: number;
    options: string[];
    answer: string;
}

interface Props {
    classes: ClassModel[];
    sections: Section[];
    teachers: Teacher[];
    subjects: Subject[];
    teacherSubjects: Subject[];
    userRole: string;
}

const props = defineProps<Props>();

// Use reactive for questions array to avoid Inertia typing issues
const questions = reactive<Question[]>([]);
const availableSubjects = ref<Subject[]>([]);

const form = useForm({
    title: '',
    class_id: '',
    section_id: '',
    teacher_id: '',
    published: false,
    subject_id: '',
    total_marks: '',
    time_duration: 90,
    instructions: '',
    questions: [] as any[],
});

// Helper function to safely get nested error messages
function getError(key: string): string | undefined {
    return (form.errors as any)[key];
}

// Explicitly define errors as computed
const errors = computed(() => form.errors);

// Check if user is admin/superadmin
const isAdmin = computed(() => ['admin', 'superadmin'].includes(props.userRole));

// Get available subjects based on user role
const getAvailableSubjects = computed(() => {
    if (isAdmin.value) {
        return availableSubjects.value;
    } else {
        return props.teacherSubjects;
    }
});

// Computed property for subject placeholder text
const subjectPlaceholderText = computed(() => {
    if (!form.class_id) {
        return 'Please select a class first';
    }

    if (availableSubjects.value.length === 0) {
        return 'No subjects assigned to this class';
    }

    return 'Select a subject...';
});

// Explicitly define onSubjectChange function
function onSubjectChange(): void {
    // Clear any subject-related errors when subject changes
    if (form.errors.subject_id) {
        form.clearErrors('subject_id');
    }
}

function goBack() {
    router.visit(route('papersquestions.index'));
}

// Watch for class selection changes
watch(() => form.class_id, async (newClassId) => {
    if (newClassId && isAdmin.value) {
        await loadSubjectsForClass(newClassId);
    } else if (newClassId && !isAdmin.value) {
        // For teachers, auto-select their first assigned subject
        if (props.teacherSubjects.length > 0) {
            form.subject_id = props.teacherSubjects[0].id.toString();
        }
    } else {
        availableSubjects.value = [];
        form.subject_id = '';
    }
});

async function loadSubjectsForClass(classId: string) {
    try {
        console.log('Loading subjects for class:', classId);
        const response = await fetch(route('papersquestions.subjects-by-class', classId));

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const subjects = await response.json();
        console.log('Loaded subjects:', subjects);
        availableSubjects.value = subjects;

        // Show feedback to user
        if (subjects.length === 0) {
            toast.warning('No subjects are assigned to this class. Please assign subjects to the class first.');
        }
    } catch (error) {
        console.error('Error loading subjects:', error);
        availableSubjects.value = [];
        toast.error('Failed to load subjects. Please try again.');
    }
}

function addQuestion() {
    const newQuestion = {
        text: '',
        type: '',
        section: '',
        marks: 1,
        question_number: undefined,
        options: ['', '', '', ''],
        answer: '',
    };
    questions.push(newQuestion);
    // Update the form questions array
    form.questions = [...questions];
}

function removeQuestion(index: number) {
    questions.splice(index, 1);
    // Update the form questions array
    form.questions = [...questions];
}

function addOption(questionIndex: number) {
    questions[questionIndex].options.push('');
    // Update the form questions array
    form.questions = [...questions];
}

function removeOption(questionIndex: number, optionIndex: number) {
    questions[questionIndex].options.splice(optionIndex, 1);
    // Update the form questions array
    form.questions = [...questions];
}

// Watch for changes in questions array and update form
watch(questions, (newQuestions) => {
    form.questions = [...newQuestions];
}, { deep: true });

// Also watch for changes in individual question properties
watch(questions, (newQuestions) => {
    form.questions = newQuestions.map(q => ({ ...q }));
}, { deep: true });

function submitForm() {
    // Ensure questions are properly set before submission
    form.questions = questions.map(q => ({ ...q }));

    // Validate that at least one question exists
    if (questions.length === 0) {
        toast.error('Please add at least one question before submitting.');
        return;
    }

    // Validate that all questions have required fields
    for (let i = 0; i < questions.length; i++) {
        const question = questions[i];
        if (!question.text.trim()) {
            toast.error(`Question ${i + 1}: Question text is required.`);
            return;
        }
        if (!question.type) {
            toast.error(`Question ${i + 1}: Question type is required.`);
            return;
        }
        if (!question.section) {
            toast.error(`Question ${i + 1}: Section is required.`);
            return;
        }
        if (!question.marks || question.marks < 1) {
            toast.error(`Question ${i + 1}: Marks must be at least 1.`);
            return;
        }
    }

    // Log the form data for debugging
    console.log('Submitting form with questions:', form.questions);

    form.post(route('papersquestions.store'), {
        onSuccess: () => {
            toast.success('Paper created successfully!');
        },
        onError: (errors) => {
            console.log('Form errors:', errors);
            // Show validation errors as toast messages for non-field errors
            if (errors.error) {
                toast.error(errors.error);
            }
            if (errors.questions) {
                toast.error(errors.questions);
            }
        },
    });
}

const page = usePage()

// Handle flash messages
onMounted(() => {
    const flash = page.props.flash as any
    if (flash?.success) {
        toast.success(flash.success)
    }
    if (flash?.error) {
        toast.error(flash.error)
    }
})
</script>