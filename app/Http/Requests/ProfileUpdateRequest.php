<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $role = $this->user()->getRoleNames()->first();
        $isAgency = $role === 'agency';
        $isPerson = in_array($role, ['care_giver', 'care_seeker'], true);

        return [
            'first_name' => [Rule::requiredIf(! $isAgency), 'nullable', 'string', 'max:255'],
            'last_name' => [Rule::requiredIf($isPerson), 'nullable', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'age' => [Rule::requiredIf($isPerson), 'nullable', 'integer', 'min:0', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => [Rule::requiredIf($isPerson), 'nullable', 'string', 'max:255'],
            'care_giver_type' => [
                Rule::requiredIf($role === 'care_giver'),
                'nullable',
                'string',
                Rule::in(['doctor', 'nurse', 'carers', 'physiotherapist', 'other']),
            ],
            'cv' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'care_for' => [Rule::requiredIf($role === 'care_seeker'), 'nullable', 'string', 'max:255'],
            'care_needs' => [Rule::requiredIf($role === 'care_seeker'), 'nullable', 'string', 'max:1000'],
            'preferred_contact_method' => ['nullable', 'string', Rule::in(['phone', 'email'])],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:40'],
            'mobility_level' => ['nullable', 'string', 'max:255'],
            'medical_notes' => ['nullable', 'string', 'max:1000'],
            'agency_name' => [Rule::requiredIf($isAgency), 'nullable', 'string', 'max:255'],
            'contact_person' => [Rule::requiredIf($isAgency), 'nullable', 'string', 'max:255'],
            'agency_address' => [Rule::requiredIf($isAgency), 'nullable', 'string', 'max:255'],
            'services_offered' => [Rule::requiredIf($isAgency), 'nullable', 'string', 'max:1000'],
            'agency_license' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
        ];
    }
}
