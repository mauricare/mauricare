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
            'mutate.0.attributes.role' => ['required', 'string', Rule::in(['care_seeker', 'care_giver'])],
            'mutate.0.attributes.first_name' => ['required', 'string', 'max:255'],
            'mutate.0.attributes.last_name' => ['required', 'string', 'max:255'],
            'mutate.0.attributes.email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'mutate.0.attributes.age' => ['required', 'integer', 'min:0', 'max:120'],
            'mutate.0.attributes.phone' => ['required', 'string', 'max:40'],
            'mutate.0.attributes.address' => ['nullable', 'string', 'max:255'],
            'mutate.0.attributes.city' => ['required', 'string', 'max:255'],
            'mutate.0.attributes.care_giver_type' => [
                Rule::requiredIf($role === 'care_giver'),
                'nullable',
                'string',
                Rule::in(['doctor', 'nurse', 'carers', 'physiotherapist', 'other']),
            ],
            'mutate.0.attributes.cv' => [
                Rule::requiredIf($role === 'care_giver'),
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
            'mutate.0.attributes.password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $attributes = $validated['mutate'][0]['attributes'];

        $user = DB::transaction(function () use ($attributes, $request, $role): User {
            $cv = $request->file('mutate.0.attributes.cv');

            $firstName = trim($attributes['first_name']);
            $lastName = trim($attributes['last_name']);

            $user = User::create([
                'name' => trim($firstName.' '.$lastName),
                'email' => $attributes['email'],
                'password' => Hash::make($attributes['password']),
            ]);

            $user->profile()->create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'age' => $attributes['age'],
                'phone' => $attributes['phone'],
                'address' => $attributes['address'] ?? null,
                'city' => $attributes['city'],
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
