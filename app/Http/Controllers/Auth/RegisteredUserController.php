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
            'role' => 'required|string|in:patient,caregiver',
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'phone' => 'required|string|max:40',
            'address' => 'nullable|string|max:255',
            'care_for' => 'required_if:role,patient|nullable|string|max:255',
            'care_needs' => 'required_if:role,patient|nullable|string|max:1000',
            'experience_years' => 'required_if:role,caregiver|nullable|integer|min:0|max:60',
            'qualification' => 'required_if:role,caregiver|nullable|string|max:255',
            'availability' => 'required_if:role,caregiver|nullable|string|max:255',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'phone' => $request->phone,
            'address' => $request->address,
            'care_for' => $request->role === 'patient' ? $request->care_for : null,
            'care_needs' => $request->role === 'patient' ? $request->care_needs : null,
            'experience_years' => $request->role === 'caregiver' ? $request->experience_years : null,
            'qualification' => $request->role === 'caregiver' ? $request->qualification : null,
            'availability' => $request->role === 'caregiver' ? $request->availability : null,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
