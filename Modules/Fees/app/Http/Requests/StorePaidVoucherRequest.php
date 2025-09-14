<?php

namespace Modules\Fees\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaidVoucherRequest extends FormRequest
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
            // 'voucher_number' => ['nullable', 'string', 'max:255'],
            'paid_voucher_image' => 'required|file|image|max:2048',
        ];
    }
    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'paid_voucher_image.file' => 'Voucher must be a valid file.',
            'paid_voucher_image.mimes' => 'Voucher must be a JPG, PNG, or PDF.',
            'paid_voucher_image.max' => 'Voucher file must be under 2MB.',
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     */
    public function attributes(): array
    {
        return [
            'voucher_number' => 'voucher number',
            'paid_voucher_image' => 'voucher image',
        ];
    }
}
