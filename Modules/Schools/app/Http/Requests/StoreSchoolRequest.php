<?php

namespace Modules\Schools\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|min:2|max:255|regex:/^[a-zA-Z0-9\s\-\.]+$/',
            'address' => 'required|string|min:10|max:500',
            'contact' => ['required', 'string', 'regex:/^(03\d{9}|\+92\d{10})$/'],
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'School name is required.',
            'name.min' => 'School name must be at least 2 characters long.',
            'name.max' => 'School name cannot exceed 255 characters.',
            'name.regex' => 'School name can only contain letters, numbers, spaces, hyphens, and periods.',
            'address.min' => 'Address must be at least 10 characters long.',
            'address.max' => 'Address cannot exceed 500 characters.',
            'contact.regex' => 'Contact number must be 7-20 digits and may contain spaces, dashes, plus signs, and parentheses.',
            'logo.image' => 'Logo must be an image file.',
            'logo.mimes' => 'Logo must be a JPEG, PNG, JPG, or GIF file.',
            'logo.max' => 'Logo size must not exceed 1MB.',
            'main_image.image' => 'Main image must be an image file.',
            'main_image.mimes' => 'Main image must be a JPEG, PNG, JPG, or GIF file.',
            'main_image.max' => 'Main image size must not exceed 2MB.',
        ];
    }

    /**
     * Get custom attributes for validation error messages.
     */
    public function attributes(): array
    {
        return [
            'name' => 'school name',
            'address' => 'school address',
            'contact' => 'contact number',
            'logo' => 'school logo',
            'main_image' => 'main image',
        ];
    }
}
