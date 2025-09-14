<?php

namespace Modules\Fees\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_id' => ['required', 'exists:classes,id'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'type' => ['required', 'in:admission,monthly,papers,installments'],
            'fee_items' => ['required', 'array', 'min:1'],
            'fee_items.*.type' => ['required', 'in:tuition,library,security,admission,sports,papers,transport'],
            'fee_items.*.amount' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
        ];
    }

    public function messages(): array
    {
        return [
            'due_date.required' => 'Due date is required.',
            'due_date.date' => 'Due date must be a valid date.',
            'due_date.after_or_equal' => 'Due date must be today or in the future.',
            'type.required' => 'Fee type is required.',
            'type.in' => 'Fee type must be admission, monthly, papers, or installments.',
            'fee_items.required' => 'At least one fee item is required.',
            'fee_items.array' => 'Fee items must be an array.',
            'fee_items.min' => 'At least one fee item is required.',
            'fee_items.*.type.required' => 'Each fee item must have a type.',
            'fee_items.*.type.in' => 'Invalid fee item type.',
            'fee_items.*.amount.required' => 'Each fee item must have an amount.',
            'fee_items.*.amount.numeric' => 'Fee item amount must be numeric.',
            'fee_items.*.amount.min' => 'Fee item amount must be at least 0.01.',
            'fee_items.*.amount.max' => 'Fee item amount may not exceed 999,999.99.',
        ];
    }

    public function attributes(): array
    {
        return [
            'class_id' => 'class',
            'due_date' => 'due date',
            'type' => 'fee type',
            'fee_items' => 'fee items',
            'fee_items.*.type' => 'fee item type',
            'fee_items.*.amount' => 'fee item amount',
        ];
    }
}
