<?php

namespace Modules\ClassesSections\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClassRequest extends FormRequest
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
        $classId = $this->route('class');
        return [
            'name' => 'required|string|max:255|unique:classes,name,' . $classId
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Class name is required.',
            'name.max' => 'Class name cannot exceed 255 characters.',
            'name.unique' => 'This class name is already taken.',
        ];
    }

    /**
     * Get custom attributes for validation error messages.
     */
    public function attributes(): array
    {
        return [
            'name' => 'class name',
        ];
    }
}
