<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConfirmPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount_paid' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'payment_reference' => [
                'nullable',
                'string',
                'max:100',
                Rule::requiredIf(fn () => $this->input('payment_method') === PaymentMethod::Juice->value),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_reference.required' => 'A transaction reference is required for Juice payments.',
        ];
    }
}
