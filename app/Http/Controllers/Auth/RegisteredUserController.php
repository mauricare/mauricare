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
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'role' => 'required|string|in:care_seeker,care_giver',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'age' => 'required|integer|min:0|max:120',
            'phone' => 'required|string|max:40',
            'address' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'care_giver_type' => 'required_if:role,care_giver|nullable|string|in:doctor,nurse,carers,physiotherapist,other',
            'cv' => 'required_if:role,care_giver|nullable|file|mimes:pdf,doc,docx|max:5120',
            'care_for' => 'required_if:role,care_seeker|nullable|string|max:255',
            'care_needs' => 'required_if:role,care_seeker|nullable|string|max:1000',
            'preferred_contact_method' => 'nullable|string|in:phone,email',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:40',
            'mobility_level' => 'nullable|string|max:255',
            'medical_notes' => 'nullable|string|max:1000',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $cvPath = $request->role === 'care_giver' && $request->hasFile('cv')
            ? $request->file('cv')->store('care-giver-cvs', 'public')
            : null;

        $firstName = $request->string('first_name')->trim()->toString();
        $lastName = $request->string('last_name')->trim()->toString();
        $name = trim($firstName.' '.$lastName) ?: $request->email;

        $user = User::create([
            'name' => $name,
            'email' => $request->email,
            'first_name' => $firstName ?: null,
            'last_name' => $lastName ?: null,
            'age' => $request->age,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'care_giver_type' => $request->role === 'care_giver' ? $request->care_giver_type : null,
            'cv_path' => $cvPath,
            'care_for' => $request->role === 'care_seeker' ? $request->care_for : null,
            'care_needs' => $request->role === 'care_seeker' ? $request->care_needs : null,
            'preferred_contact_method' => $request->role === 'care_seeker' ? $request->preferred_contact_method : null,
            'emergency_contact_name' => $request->role === 'care_seeker' ? $request->emergency_contact_name : null,
            'emergency_contact_phone' => $request->role === 'care_seeker' ? $request->emergency_contact_phone : null,
            'mobility_level' => $request->role === 'care_seeker' ? $request->mobility_level : null,
            'medical_notes' => $request->role === 'care_seeker' ? $request->medical_notes : null,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole(Role::findOrCreate($request->role, 'web'));

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
