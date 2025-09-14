<?php

namespace Modules\ClassesSections\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\ClassesSections\App\Models\Section;

class StoreSectionRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:1',
                function ($attribute, $value, $fail) {
                    $exists = Section::where('name', strtoupper($value))
                        ->whereNull('deleted_at')
                        ->exists();
                    if ($exists) {
                        $fail('The section name has already been taken.');
                    }
                }
            ]
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Section name is required.',
            'name.max' => 'Section name must be a single character.',
        ];
    }

    /**
     * Get custom attributes for validation error messages.
     */
    public function attributes(): array
    {
        return [
            'name' => 'section name',
        ];
    }
}
