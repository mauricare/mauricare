<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') === true;
    }

    public function rules(): array
    {
        /** @var User $managedUser */
        $managedUser = $this->route('user');
        $isCareGiver = $managedUser->hasRole('care_giver');
        $isCareSeeker = $managedUser->hasRole('care_seeker');

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($managedUser->id),
            ],
            'age' => ['required', 'integer', 'min:0', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'care_giver_type' => [
                Rule::requiredIf($isCareGiver),
                'nullable',
                Rule::in(['doctor', 'nurse', 'carers', 'physiotherapist', 'other']),
            ],
            'care_for' => [Rule::requiredIf($isCareSeeker), 'nullable', 'string', 'max:255'],
            'care_needs' => [Rule::requiredIf($isCareSeeker), 'nullable', 'string', 'max:1000'],
            'preferred_contact_method' => ['nullable', Rule::in(['phone', 'email'])],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:40'],
            'mobility_level' => ['nullable', 'string', 'max:255'],
            'medical_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
