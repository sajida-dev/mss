<template>
    <form @submit.prevent="$emit('submit')" enctype="multipart/form-data">
        <!-- Pattern Information -->
        <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2"> Required Formats:</h3>
            <div class="text-xs text-blue-700 dark:text-blue-300 space-y-1">
                <p><strong>Registration Number:</strong> 6-12 characters, letters and numbers only (e.g., STU2024001)
                </p>
                <p><strong>Names:</strong> Letters and spaces only</p>
                <p><strong>B-Form & CNIC:</strong> Format: 12345-6789012-3</p>
                <p><strong>Mobile Number:</strong> Must start with 03 and be 11 digits (e.g., 03001234567)</p>
                <p><strong>Address:</strong> Minimum 10 characters</p>
            </div>
        </div>


        <!-- Student Information Section -->
        <div class="mb-6 p-4 bg-white dark:bg-neutral-800 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-2 text-blue-700 dark:text-blue-300 flex items-center">
                <Icon name="user" class="w-5 h-5 mr-2" /> Student Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <SelectInput id="nationality" v-model="form.nationality" label="Nationality"
                    :options="nationalityOptions" :required="true" :error="errors.nationality"
                    placeholder="Select nationality" />
                <TextInput id="registration_number" v-model="form.registration_number" label="Registration Number"
                    required :error="errors.registration_number" placeholder="e.g. STU2024001" pattern="[A-Z0-9]{6,12}"
                    title="6-12 characters, letters and numbers only" />
                <TextInput id="name" v-model="form.name" label="Name" required :error="errors.name"
                    placeholder="Enter full name (letters and spaces only)" pattern="[a-zA-Z\s]+"
                    title="Letters and spaces only" />
                <TextInput id="b_form_number" v-model="form.b_form_number" label="B-Form Number" required
                    :error="errors.b_form_number" placeholder="12345-6789012-3" pattern="\d{5}-\d{7}-\d{1}"
                    title="Format: 12345-6789012-3" />
                <TextInput id="admission_date" v-model="form.admission_date" label="Admission Date" type="date" required
                    :error="errors.admission_date" placeholder="Select admission date" />
                <TextInput id="date_of_birth" v-model="form.date_of_birth" label="Date of Birth" type="date" required
                    :error="errors.date_of_birth" placeholder="Select date of birth" />
                <SelectInput id="class_id" v-model="form.class_id" label="Class" :options="classes" :required="true"
                    :error="errors.class_id" placeholder="Select class" />
                <SelectInput id="gender" v-model="form.gender" label="Gender" :options="genderOptions" :required="true"
                    :error="errors.gender" placeholder="Select gender" />
                <SelectInput id="class_shift" v-model="form.class_shift" label="Class Shift"
                    :options="classShiftOptions" :required="true" :error="errors.class_shift"
                    placeholder="Select class shift" />
                <TextInput id="previous_school" v-model="form.previous_school" label="Previous School"
                    :error="errors.previous_school" placeholder="Enter previous school (if any)" />
                <SelectInput id="inclusive" v-model="form.inclusive" label="Inclusive" :options="inclusiveOptions"
                    :required="true" :error="errors.inclusive" placeholder="Select inclusivity" />
                <TextInput id="other_inclusive_type" v-model="form.other_inclusive_type" label="Other Inclusive Type"
                    :error="errors.other_inclusive_type" placeholder="Specify if other" />
                <SelectInput id="religion" v-model="form.religion" label="Religion" :options="religionOptions"
                    :required="true" :error="errors.religion" placeholder="Select religion" />
                <!-- Checkboxes in a single row, right-aligned -->
                <div class="flex flex-wrap items-center gap-6 mt-2 ">
                    <CheckboxInput v-model="form.is_orphan" label="Is Orphan" :error="errors.is_orphan" />
                    <CheckboxInput v-model="form.is_bricklin" label="Is Bricklin" :error="errors.is_bricklin" />
                    <CheckboxInput v-model="form.is_qsc" label="Is QSC" :error="errors.is_qsc" />
                </div>
            </div>
            <div class="mt-4">
                <div v-if="mode === 'edit'">
                    <div v-if="profilePhotoUrl">
                        <img :src="profilePhotoUrl" alt="Profile Photo"
                            class="h-24 w-24 object-cover rounded-full border mb-2 mx-auto" />
                    </div>
                    <div v-else class="text-center mb-2">
                        <div
                            class="w-24 h-24 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center mx-auto">
                            <span class="text-gray-500 dark:text-gray-400 text-sm">No Photo</span>
                        </div>
                    </div>
                </div>
                <FileInput id="profile_photo_path" v-model="form.profile_photo_path" />
                <InputError :message="errors.profile_photo_path" />
            </div>
        </div>
        <!-- Family Information Section -->
        <div class="mb-6 p-4 bg-white dark:bg-neutral-800 rounded-lg shadow">
            <h2 class="text-lg font-semibold mb-2 text-green-700 dark:text-green-300 flex items-center">
                <Icon name="users" class="w-5 h-5 mr-2" /> Family Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <TextInput id="father_name" v-model="form.father_name" label="Father Name" required
                    :error="errors.father_name" placeholder="Enter father's name (letters and spaces only)"
                    pattern="[a-zA-Z\s]+" title="Letters and spaces only" />
                <TextInput id="guardian_name" v-model="form.guardian_name" label="Guardian Name"
                    :error="errors.guardian_name" placeholder="Enter guardian's name (if any)" pattern="[a-zA-Z\s]*"
                    title="Letters and spaces only" />
                <TextInput id="father_cnic" v-model="form.father_cnic" label="Father CNIC" required
                    :error="errors.father_cnic" placeholder="12345-6789012-3" pattern="\d{5}-\d{7}-\d{1}"
                    title="Format: 12345-6789012-3" />
                <TextInput id="mother_cnic" v-model="form.mother_cnic" label="Mother CNIC" :error="errors.mother_cnic"
                    placeholder="12345-6789012-3" pattern="\d{5}-\d{7}-\d{1}" title="Format: 12345-6789012-3" />
                <SelectInput id="father_profession" v-model="form.father_profession" label="Father Profession"
                    :options="fatherProfessionOptions" :required="true" :error="errors.father_profession"
                    placeholder="Select father's profession" />
                <TextInput id="no_of_children" v-model="form.no_of_children" label="No of Children" type="number"
                    :error="errors.no_of_children" placeholder="Enter number of children (0-20)" min="0" max="20"
                    title="Number between 0 and 20" />
                <SelectInput id="job_type" v-model="form.job_type" label="Job Type" :options="jobTypeOptions"
                    :error="errors.job_type" placeholder="Select job type" />
                <SelectInput id="father_education" v-model="form.father_education" label="Father Education"
                    :options="educationOptions" :required="true" :error="errors.father_education"
                    placeholder="Select father's education" />
                <SelectInput id="mother_education" v-model="form.mother_education" label="Mother Education"
                    :options="educationOptions" :required="true" :error="errors.mother_education"
                    placeholder="Select mother's education" />
                <SelectInput id="mother_profession" v-model="form.mother_profession" label="Mother Profession"
                    :options="motherProfessionOptions" :required="true" :error="errors.mother_profession"
                    placeholder="Select mother's profession" />
                <SelectInput id="father_income" v-model="form.father_income" label="Father Income"
                    :options="incomeOptions" :required="true" :error="errors.father_income"
                    placeholder="Select father's income" />
                <SelectInput id="mother_income" v-model="form.mother_income" label="Mother Income"
                    :options="motherIncomeOptions" :error="errors.mother_income" placeholder="Select mother's income" />
                <SelectInput id="household_income" v-model="form.household_income" label="Household Income"
                    :options="incomeOptions" :required="true" :error="errors.household_income"
                    placeholder="Select household income" />
                <TextInput id="permanent_address" v-model="form.permanent_address" label="Permanent Address" required
                    :error="errors.permanent_address" placeholder="Enter permanent address (min 10 characters)"
                    minlength="10" maxlength="500" title="Address must be at least 10 characters long" />
                <TextInput id="phone_no" v-model="form.phone_no" label="Phone No" :error="errors.phone_no"
                    placeholder="Enter phone number: e.g. 03001234567 or +923001234567" pattern="^(03\d{9}|\+92\d{10})$"
                    title="Must follow the format: 03001234567 or +923001234567" />
                <TextInput id="mobile_no" v-model="form.mobile_no" label="Mobile No" required :error="errors.mobile_no"
                    placeholder="e.g. 03001234567 or +923001234567" pattern="^(03\d{9}|\+92\d{10})$"
                    title="Must follow the format: 03001234567 or +923001234567" />
            </div>
        </div>
        <div class="mt-6 flex justify-end items-center gap-2">
            <Button type="submit" variant="default" class="h-9">{{ mode === 'edit' ? 'Update Student' : 'Add Student'
            }}</Button>
            <Button type="button" variant="secondary" @click="$emit('cancel')">Cancel</Button>
        </div>
    </form>
</template>

<script setup lang="ts">
import { defineProps, defineEmits, watch, computed } from 'vue';
import TextInput from '@/components/form/TextInput.vue';
import SelectInput from '@/components/form/SelectInput.vue';
import CheckboxInput from '@/components/form/CheckboxInput.vue';
import FileInput from '@/components/form/FileInput.vue';
import Button from '@/components/ui/button/Button.vue';
import InputError from '@/components/InputError.vue';
import Icon from '@/components/Icon.vue';

// Static select option arrays (shared, single source of truth) - all lowercase for backend compatibility
const nationalities = [
    'pakistan', 'india', 'bangladesh', 'afghanistan', 'china', 'saudi arabia', 'united arab emirates', 'united states', 'united kingdom', 'canada', 'australia', 'turkey', 'iran', 'indonesia', 'malaysia', 'egypt', 'south africa', 'germany', 'france', 'italy', 'other'
];
const genders = ['male', 'female', 'other'];
const classShifts = ['morning', 'evening', 'other'];
const inclusives = ['no disability', 'physical', 'visual', 'hearing', 'intellectual', 'other'];
const religions = ['islam', 'christianity', 'hinduism', 'other'];
const fatherProfessions = ['unemployed', 'private/self employed', 'government', 'other'];
const jobTypes = ['private/self employed', 'government', 'other'];
const educations = ['none', 'primary', 'middle', 'matric', 'intermediate', 'graduate', 'post graduate'];
const motherProfessions = ['house wife', 'private/self employed', 'government', 'other'];
const incomes = [
    'income level between rs. 0 - 20,000',
    'income level between rs. 20,001 - 27,000',
    'income level between rs. 27,001 - 35,000',
    'income level between rs. 35,001 - 50,000',
    'income level above rs. 50,000',
];
const motherIncomes = [
    'none',
    'income level between rs. 0 - 20,000',
    'income level between rs. 20,001 - 27,000',
    'income level between rs. 27,001 - 35,000',
    'income level between rs. 35,001 - 50,000',
    'income level above rs. 50,000',
];


const props = defineProps<{
    form: any,
    errors: Record<string, string>,
    mode: 'create' | 'edit',
    classes: Array<{ label: string, value: string | number }>,
    schools: Array<any>,
    existingPhoto?: string,
}>();
const emit = defineEmits(['submit', 'cancel']);

// Remove the onSubmit function from the script section.

watch(
    () => props.schools,
    (newSchools) => {
        if (newSchools.length === 1 && !props.form.school_id) {
            props.form.school_id = newSchools[0].id;
        }
    },
    { immediate: true }
);

const profilePhotoUrl = computed(() => {
    if (props.form.profile_photo_path) {
        // New file selected (File object)
        return URL.createObjectURL(props.form.profile_photo_path);
    } else if (props.existingPhoto) {
        // Existing image from backend
        return `/storage/${props.existingPhoto}`;
    }
    return null;
});

// Computed properties for frontend display with proper capitalization
const nationalityOptions = computed(() =>
    nationalities.map(item => ({
        label: item.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' '),
        value: item
    }))
);

const genderOptions = computed(() =>
    genders.map(item => ({
        label: item.charAt(0).toUpperCase() + item.slice(1),
        value: item
    }))
);

const classShiftOptions = computed(() =>
    classShifts.map(item => ({
        label: item.charAt(0).toUpperCase() + item.slice(1),
        value: item
    }))
);

const inclusiveOptions = computed(() =>
    inclusives.map(item => ({
        label: item.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' '),
        value: item
    }))
);

const religionOptions = computed(() =>
    religions.map(item => ({
        label: item.charAt(0).toUpperCase() + item.slice(1),
        value: item
    }))
);

const fatherProfessionOptions = computed(() =>
    fatherProfessions.map(item => ({
        label: item.split('/').map(part => part.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')).join('/'),
        value: item
    }))
);

const jobTypeOptions = computed(() =>
    jobTypes.map(item => ({
        label: item.split('/').map(part => part.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')).join('/'),
        value: item
    }))
);

const educationOptions = computed(() =>
    educations.map(item => ({
        label: item.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' '),
        value: item
    }))
);

const motherProfessionOptions = computed(() =>
    motherProfessions.map(item => ({
        label: item.split('/').map(part => part.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')).join('/'),
        value: item
    }))
);

const incomeOptions = computed(() =>
    incomes.map(item => ({
        label: item.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' '),
        value: item
    }))
);

const motherIncomeOptions = computed(() =>
    motherIncomes.map(item => ({
        label: item === 'none' ? 'None' : item.split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' '),
        value: item
    }))
);

</script>