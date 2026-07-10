<?php

namespace App\Rest\Controllers;

use App\Models\User;
use App\Rest\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    /**
     * The resource the controller corresponds to.
     *
     * @var class-string<\Lomkit\Rest\Http\Resource>
     */
    public static $resource = \App\Rest\Resources\UserResource::class;

    /**
     * Create users through the public REST mutation endpoint.
     */
    public function mutate(Request $request): JsonResponse
    {
        $role = $request->input('mutate.0.attributes.role');

        $validated = $request->validate([
            'mutate' => ['required', 'array', 'size:1'],
            'mutate.0.operation' => ['required', 'string', Rule::in(['create'])],
            'mutate.0.attributes' => ['required', 'array'],
            'mutate.0.attributes.role' => ['required', 'string', Rule::in(['care_seeker', 'care_giver', 'agency'])],
            'mutate.0.attributes.first_name' => [Rule::requiredIf($role !== 'agency'), 'nullable', 'string', 'max:255'],
            'mutate.0.attributes.last_name' => [Rule::requiredIf($role !== 'agency'), 'nullable', 'string', 'max:255'],
            'mutate.0.attributes.email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'mutate.0.attributes.age' => [Rule::requiredIf($role !== 'agency'), 'nullable', 'integer', 'min:0', 'max:120'],
            'mutate.0.attributes.phone' => ['required', 'string', 'max:40'],
            'mutate.0.attributes.address' => ['nullable', 'string', 'max:255'],
            'mutate.0.attributes.city' => [Rule::requiredIf($role !== 'agency'), 'nullable', 'string', 'max:255'],
            'mutate.0.attributes.care_giver_type' => [
                Rule::requiredIf($role === 'care_giver'),
                'nullable',
                'string',
                Rule::in(['doctor', 'nurse', 'carers', 'physiotherapist', 'other']),
            ],
            'mutate.0.attributes.cv' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx',
                'max:5120',
            ],
            'mutate.0.attributes.care_for' => [Rule::requiredIf($role === 'care_seeker'), 'nullable', 'string', 'max:255'],
            'mutate.0.attributes.care_needs' => [Rule::requiredIf($role === 'care_seeker'), 'nullable', 'string', 'max:1000'],
            'mutate.0.attributes.preferred_contact_method' => ['nullable', 'string', Rule::in(['phone', 'email'])],
            'mutate.0.attributes.emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'mutate.0.attributes.emergency_contact_phone' => ['nullable', 'string', 'max:40'],
            'mutate.0.attributes.mobility_level' => ['nullable', 'string', 'max:255'],
            'mutate.0.attributes.medical_notes' => ['nullable', 'string', 'max:1000'],
            'mutate.0.attributes.agency_name' => [Rule::requiredIf($role === 'agency'), 'nullable', 'string', 'max:255'],
            'mutate.0.attributes.contact_person' => [Rule::requiredIf($role === 'agency'), 'nullable', 'string', 'max:255'],
            'mutate.0.attributes.agency_address' => [Rule::requiredIf($role === 'agency'), 'nullable', 'string', 'max:255'],
            'mutate.0.attributes.services_offered' => [Rule::requiredIf($role === 'agency'), 'nullable', 'string', 'max:1000'],
            'mutate.0.attributes.agency_license' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png,doc,docx',
                'max:5120',
            ],
            'mutate.0.attributes.password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [], [
            'mutate.0.attributes.role' => 'role',
            'mutate.0.attributes.first_name' => 'first name',
            'mutate.0.attributes.last_name' => 'last name',
            'mutate.0.attributes.email' => 'email',
            'mutate.0.attributes.age' => 'age',
            'mutate.0.attributes.phone' => 'phone',
            'mutate.0.attributes.address' => 'address',
            'mutate.0.attributes.city' => 'city',
            'mutate.0.attributes.care_giver_type' => 'caregiver type',
            'mutate.0.attributes.cv' => 'CV',
            'mutate.0.attributes.care_for' => 'care for',
            'mutate.0.attributes.care_needs' => 'care needs',
            'mutate.0.attributes.preferred_contact_method' => 'preferred contact method',
            'mutate.0.attributes.emergency_contact_name' => 'emergency contact name',
            'mutate.0.attributes.emergency_contact_phone' => 'emergency contact phone',
            'mutate.0.attributes.mobility_level' => 'mobility level',
            'mutate.0.attributes.medical_notes' => 'medical notes',
            'mutate.0.attributes.agency_name' => 'agency name',
            'mutate.0.attributes.contact_person' => 'contact person',
            'mutate.0.attributes.agency_address' => 'agency address',
            'mutate.0.attributes.services_offered' => 'services offered',
            'mutate.0.attributes.agency_license' => 'agency license',
            'mutate.0.attributes.password' => 'password',
        ]);

        $attributes = $validated['mutate'][0]['attributes'];

        $user = DB::transaction(function () use ($attributes, $request, $role): User {
            $cv = $request->file('mutate.0.attributes.cv');
            $agencyLicense = $request->file('mutate.0.attributes.agency_license');

            $firstName = trim($attributes['first_name'] ?? $attributes['contact_person'] ?? '');
            $lastName = trim($attributes['last_name'] ?? '');
            $name = $role === 'agency'
                ? trim($attributes['agency_name'])
                : trim($firstName.' '.$lastName);

            $user = User::create([
                'name' => $name,
                'email' => $attributes['email'],
                'password' => Hash::make($attributes['password']),
            ]);

            $user->profile()->create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'age' => $attributes['age'] ?? null,
                'phone' => $attributes['phone'],
                'address' => $attributes['agency_address'] ?? $attributes['address'] ?? null,
                'city' => $attributes['city'] ?? null,
            ]);

            if ($role === 'care_giver') {
                $user->careGiverProfile()->create([
                    'type' => $attributes['care_giver_type'],
                ]);

                if ($cv) {
                    $user->documents()->create([
                        'type' => 'cv',
                        'disk' => 'public',
                        'path' => $cv->store('care-giver-cvs', 'public'),
                        'original_name' => $cv->getClientOriginalName(),
                        'mime_type' => $cv->getClientMimeType(),
                        'size' => $cv->getSize(),
                    ]);
                }
            }

            if ($role === 'care_seeker') {
                $user->careSeekerProfile()->create([
                    'care_for' => $attributes['care_for'],
                    'care_needs' => $attributes['care_needs'],
                    'preferred_contact_method' => $attributes['preferred_contact_method'] ?? null,
                    'emergency_contact_name' => $attributes['emergency_contact_name'] ?? null,
                    'emergency_contact_phone' => $attributes['emergency_contact_phone'] ?? null,
                    'mobility_level' => $attributes['mobility_level'] ?? null,
                    'medical_notes' => $attributes['medical_notes'] ?? null,
                ]);
            }

            if ($role === 'agency') {
                $user->agencyProfile()->create([
                    'agency_name' => $attributes['agency_name'],
                    'contact_person' => $attributes['contact_person'],
                    'agency_address' => $attributes['agency_address'],
                    'services_offered' => $attributes['services_offered'],
                ]);

                if ($agencyLicense) {
                    $user->documents()->create([
                        'type' => 'agency_license',
                        'disk' => 'public',
                        'path' => $agencyLicense->store('agency-licenses', 'public'),
                        'original_name' => $agencyLicense->getClientOriginalName(),
                        'mime_type' => $agencyLicense->getClientMimeType(),
                        'size' => $agencyLicense->getSize(),
                    ]);
                }
            }

            $user->assignRole(Role::findOrCreate($role, 'web'));

            event(new Registered($user));

            return $user;
        });

        return response()->json([
            'data' => [
                'created' => [$user->id],
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ],
        ], 201);
    }
}
