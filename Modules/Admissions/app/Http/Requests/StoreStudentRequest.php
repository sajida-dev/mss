<?php

namespace Modules\Admissions\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $genderValues = ['male', 'female', 'other'];
        $classShiftValues = ['morning', 'evening', 'other'];
        $inclusiveValues = ['no disability', 'physical', 'visual', 'hearing', 'intellectual', 'other'];
        $religionValues = ['islam', 'christianity', 'hinduism', 'other'];
        $fatherProfessionValues = ['unemployed', 'private/self employed', 'government', 'other'];
        $jobTypeValues = ['private/self employed', 'government', 'other'];
        $educationValues = ['none', 'primary', 'middle', 'matric', 'intermediate', 'graduate', 'post graduate'];
        $motherProfessionValues = ['house wife', 'private/self employed', 'government', 'other'];
        $incomeLevels = [
            'income level between rs. 0 - 20,000',
            'income level between rs. 20,001 - 27,000',
            'income level between rs. 27,001 - 35,000',
            'income level between rs. 35,001 - 50,000',
            'income level above rs. 50,000',
        ];
        $motherIncomes = [
            'none',
            'income level between rs. 0 - 20,000',
            'income level between rs. 20,001 - 27,000',
            'income level between rs. 27,001 - 35,000',
            'income level between rs. 35,001 - 50,000',
            'income level above rs. 50,000',
        ];
        return [
            'class_id' => 'required|exists:classes,id',
            'nationality' => 'required|string',
            'registration_number' => ['required', 'string', 'unique:students,registration_number', 'regex:/^[A-Z0-9]{6,12}$/'],
            'name' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[a-zA-Z\s]+$/'],
            'b_form_number' => ['required', 'string', 'unique:students,b_form_number', 'regex:/^\d{5}-\d{7}-\d{1}$/'],
            'admission_date' => 'required|date|before_or_equal:today',
            'date_of_birth' => 'required|date|before:today',
            'gender' => ['required', 'string', Rule::in($genderValues)],
            'class_shift' => ['required', 'string', Rule::in($classShiftValues)],
            'previous_school' => 'nullable|string|max:200',
            'inclusive' => ['required', 'string', Rule::in($inclusiveValues)],
            'other_inclusive_type' => 'nullable|string|max:100',
            'religion' => ['required', 'string', Rule::in($religionValues)],
            'is_bricklin' => 'nullable|boolean',
            'is_orphan' => 'nullable|boolean',
            'is_qsc' => 'nullable|boolean',
            'profile_photo_path' => 'nullable|file|image|max:2048',
            'father_name' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[a-zA-Z\s]+$/'],
            'guardian_name' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z\s]*$/'],
            'father_cnic' => ['required', 'string', 'regex:/^\d{5}-\d{7}-\d{1}$/'],
            'mother_cnic' => ['nullable', 'string', 'regex:/^\d{5}-\d{7}-\d{1}$/'],
            'father_profession' => ['required', 'string', Rule::in($fatherProfessionValues)],
            'no_of_children' => 'nullable|integer|min:0|max:20',
            'job_type' => ['nullable', 'string', Rule::in($jobTypeValues)],
            'father_education' =>  ['required', 'string', Rule::in($educationValues)],
            'mother_education' => ['required', 'string', Rule::in($educationValues)],
            'mother_profession' => ['required', 'string', Rule::in($motherProfessionValues)],
            'father_income' => ['required', 'string', Rule::in($incomeLevels)],
            'mother_income' => ['nullable', 'string', Rule::in($motherIncomes)],
            'household_income' => ['required', 'string', Rule::in($incomeLevels)],
            'permanent_address' => 'required|string|min:10|max:500',
            'phone_no' => ['nullable', 'string', 'regex:/^(03\d{9}|\+92\d{10})$/'], // e.g., 03001234567 or +923001234567
            'mobile_no' => ['required', 'string', 'regex:/^(03\d{9}|\+92\d{10})$/'], // e.g., 03001234567 or +923001234567
            'admission_fee' => ['nullable', 'numeric'],
            'due_date' => ['required_with:admission_fee', 'date'],
        ];
    }

    public function withValidator($validator)
    {
        // Require other_inclusive_type only if inclusive is 'Other'
        $validator->sometimes('other_inclusive_type', 'required|string|max:100', function ($input) {
            return $input->inclusive === 'Other';
        });

        // Custom validation: date_of_birth must be before admission_date
        $validator->after(function ($validator) {
            $dob = strtotime($this->date_of_birth);
            $admission = strtotime($this->admission_date);
            if ($dob !== false && $admission !== false && $dob >= $admission) {
                $validator->errors()->add('date_of_birth', 'Date of birth must be before admission date.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'school_id.exists' => 'Selected school does not exist.',
            'class_id.required' => 'Class selection is required.',
            'class_id.exists' => 'Selected class does not exist.',
            'nationality.required' => 'Nationality is required.',
            'registration_number.required' => 'Registration number is required.',
            'registration_number.unique' => 'This registration number is already taken.',
            'registration_number.regex' => 'Registration number must be 6-12 characters long and contain only letters and numbers.',
            'name.required' => 'Student name is required.',
            'name.min' => 'Student name must be at least 2 characters long.',
            'name.max' => 'Student name cannot exceed 100 characters.',
            'name.regex' => 'Name must contain only letters and spaces.',
            'b_form_number.required' => 'B-Form number is required.',
            'b_form_number.unique' => 'This B-Form number is already taken.',
            'b_form_number.regex' => 'B-Form number must be in format: 12345-6789012-3',
            'admission_date.required' => 'Admission date is required.',
            'admission_date.before_or_equal' => 'Admission date cannot be in the future.',
            'date_of_birth.required' => 'Date of birth is required.',
            'date_of_birth.before' => 'Date of birth must be before today.',
            'gender.required' => 'Gender is required.',
            'gender.in' => 'Please select a valid gender.',
            'class_shift.required' => 'Class shift is required.',
            'class_shift.in' => 'Please select a valid class shift.',
            'previous_school.max' => 'Previous school name cannot exceed 200 characters.',
            'inclusive.required' => 'Inclusive status is required.',
            'inclusive.in' => 'Please select a valid inclusive status.',
            'other_inclusive_type.required' => 'Other inclusive type is required when inclusive is Other.',
            'other_inclusive_type.max' => 'Other inclusive type cannot exceed 100 characters.',
            'religion.required' => 'Religion is required.',
            'religion.in' => 'Please select a valid religion.',
            'profile_photo_path.image' => 'Profile photo must be an image file.',
            'profile_photo_path.max' => 'Profile photo size must not exceed 2MB.',
            'father_name.required' => 'Father name is required.',
            'father_name.min' => 'Father name must be at least 2 characters long.',
            'father_name.max' => 'Father name cannot exceed 100 characters.',
            'father_name.regex' => 'Father name must contain only letters and spaces.',
            'guardian_name.max' => 'Guardian name cannot exceed 100 characters.',
            'guardian_name.regex' => 'Guardian name must contain only letters and spaces.',
            'father_cnic.required' => 'Father CNIC is required.',
            'father_cnic.regex' => 'Father CNIC must be in format: 12345-6789012-3',
            'mother_cnic.regex' => 'Mother CNIC must be in format: 12345-6789012-3',
            'father_profession.required' => 'Father profession is required.',
            'father_profession.in' => 'Please select a valid father profession.',
            'no_of_children.min' => 'Number of children cannot be negative.',
            'no_of_children.max' => 'Number of children cannot exceed 20.',
            'job_type.in' => 'Please select a valid job type.',
            'father_education.required' => 'Father education is required.',
            'father_education.in' => 'Please select a valid father education level.',
            'mother_education.required' => 'Mother education is required.',
            'mother_education.in' => 'Please select a valid mother education level.',
            'mother_profession.required' => 'Mother profession is required.',
            'mother_profession.in' => 'Please select a valid mother profession.',
            'father_income.required' => 'Father income is required.',
            'father_income.in' => 'Please select a valid father income level.',
            'mother_income.in' => 'Please select a valid mother income level.',
            'household_income.required' => 'Household income is required.',
            'household_income.in' => 'Please select a valid household income level.',
            'permanent_address.required' => 'Permanent address is required.',
            'permanent_address.min' => 'Permanent address must be at least 10 characters long.',
            'permanent_address.max' => 'Permanent address cannot exceed 500 characters.',
            'phone_no.regex' => 'Phone number must be in the format: 03001234567 or +923001234567.',
            'mobile_no.required' => 'Mobile number is required.',
            'mobile_no.regex' => 'Mobile number must be in the format: 03001234567 or +923001234567.',
        ];
    }

    public function attributes(): array
    {
        return [
            'class_id' => 'class',
            'registration_number' => 'registration number',
            'b_form_number' => 'B-Form number',
            'admission_date' => 'admission date',
            'date_of_birth' => 'date of birth',
            'class_shift' => 'class shift',
            'previous_school' => 'previous school',
            'other_inclusive_type' => 'other inclusive type',
            'profile_photo_path' => 'profile photo',
            'father_name' => 'father name',
            'guardian_name' => 'guardian name',
            'father_cnic' => 'father CNIC',
            'mother_cnic' => 'mother CNIC',
            'father_profession' => 'father profession',
            'no_of_children' => 'number of children',
            'job_type' => 'job type',
            'father_education' => 'father education',
            'mother_education' => 'mother education',
            'mother_profession' => 'mother profession',
            'father_income' => 'father income',
            'mother_income' => 'mother income',
            'household_income' => 'household income',
            'permanent_address' => 'permanent address',
            'phone_no' => 'phone number',
            'mobile_no' => 'mobile number',
        ];
    }
}
