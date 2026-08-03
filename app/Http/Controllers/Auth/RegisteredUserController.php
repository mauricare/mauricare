<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register', [
            'privacyNoticeVersion' => config('privacy.notice_version'),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'role' => 'required|string|in:care_seeker,care_giver,agency',
            'first_name' => 'required_unless:role,agency|nullable|string|max:255',
            'last_name' => 'required_unless:role,agency|nullable|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'age' => 'required_unless:role,agency|nullable|integer|min:0|max:120',
            'phone' => 'required|string|max:40',
            'address' => 'nullable|string|max:255',
            'city' => 'required_unless:role,agency|nullable|string|max:255',
            'care_giver_type' => 'required_if:role,care_giver|nullable|string|in:doctor,nurse,carers,physiotherapist,other',
            'cv' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'care_for' => 'required_if:role,care_seeker|nullable|string|max:255',
            'care_needs' => 'required_if:role,care_seeker|nullable|string|max:1000',
            'preferred_contact_method' => 'nullable|string|in:phone,email',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:40',
            'mobility_level' => 'nullable|string|max:255',
            'medical_notes' => 'nullable|string|max:1000',
            'agency_name' => 'required_if:role,agency|nullable|string|max:255',
            'contact_person' => 'required_if:role,agency|nullable|string|max:255',
            'agency_address' => 'required_if:role,agency|nullable|string|max:255',
            'services_offered' => 'required_if:role,agency|nullable|string|max:1000',
            'agency_license' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'privacy_notice_version' => ['required', 'string', 'in:'.config('privacy.notice_version')],
            'privacy_notice_accepted' => ['accepted'],
            'health_data_consent' => ['required_if:role,care_seeker', 'accepted'],
            'data_subject_authority_confirmed' => ['required_if:role,care_seeker', 'accepted'],
        ]);

        $cv = $request->file('cv');
        $agencyLicense = $request->file('agency_license');

        $firstName = $request->role === 'agency'
            ? $request->string('contact_person')->trim()->toString()
            : $request->string('first_name')->trim()->toString();
        $lastName = $request->role === 'agency'
            ? ''
            : $request->string('last_name')->trim()->toString();
        $name = $request->role === 'agency'
            ? $request->string('agency_name')->trim()->toString()
            : trim($firstName.' '.$lastName);
        $name = $name ?: $request->email;

        $user = User::create([
            'name' => $name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->profile()->create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'age' => $request->age,
            'phone' => $request->phone,
            'address' => $request->agency_address ?? $request->address,
            'city' => $request->city,
        ]);

        if ($request->role === 'care_giver') {
            $user->careGiverProfile()->create([
                'type' => $request->care_giver_type,
            ]);

            if ($cv) {
                $user->documents()->create([
                    'type' => 'cv',
                    'disk' => 'local',
                    'path' => $cv->store('care-giver-cvs', 'local'),
                    'original_name' => $cv->getClientOriginalName(),
                    'mime_type' => $cv->getClientMimeType(),
                    'size' => $cv->getSize(),
                ]);
            }
        }

        if ($request->role === 'care_seeker') {
            $user->careSeekerProfile()->create([
                'care_for' => $request->care_for,
                'care_needs' => $request->care_needs,
                'preferred_contact_method' => $request->preferred_contact_method,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'mobility_level' => $request->mobility_level,
                'medical_notes' => $request->medical_notes,
            ]);
        }

        if ($request->role === 'agency') {
            $user->agencyProfile()->create([
                'agency_name' => $request->agency_name,
                'contact_person' => $request->contact_person,
                'agency_address' => $request->agency_address,
                'services_offered' => $request->services_offered,
            ]);

            if ($agencyLicense) {
                $user->documents()->create([
                    'type' => 'agency_license',
                    'disk' => 'local',
                    'path' => $agencyLicense->store('agency-licenses', 'local'),
                    'original_name' => $agencyLicense->getClientOriginalName(),
                    'mime_type' => $agencyLicense->getClientMimeType(),
                    'size' => $agencyLicense->getSize(),
                ]);
            }
        }

        $user->assignRole(Role::findOrCreate($request->role, 'web'));

        $user->privacyAcceptances()->create([
            'notice_version' => $request->privacy_notice_version,
            'notice_accepted_at' => now(),
            'health_data_consent_at' => $request->role === 'care_seeker' ? now() : null,
            'data_subject_authority_confirmed_at' => $request->role === 'care_seeker' ? now() : null,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
        ]);

        event(new Registered($user));

        Auth::login($user);

        if ($user->hasRole('agency') || $user->agencyProfile()->exists()) {
            return redirect(route('account.verification', absolute: false));
        }

        return redirect(route('dashboard', absolute: false));
    }
}
