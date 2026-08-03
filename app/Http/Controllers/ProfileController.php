<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $role = $user->getRoleNames()->first();
        $roleProfile = match ($role) {
            'care_giver' => $user->careGiverProfile,
            'care_seeker' => $user->careSeekerProfile,
            'agency' => $user->agencyProfile,
            default => null,
        };
        $documentType = match ($role) {
            'care_giver' => 'cv',
            'agency' => 'agency_license',
            default => null,
        };
        $document = $documentType
            ? $user->documents()->where('type', $documentType)->latest()->first()
            : null;

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'profile' => $user->profile,
            'role' => $role,
            'roleProfile' => $roleProfile,
            'document' => $document ? [
                'name' => $document->original_name,
                'url' => route('documents.download', $document),
            ] : null,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        $role = $user->getRoleNames()->first();

        $user->email = $validated['email'];

        if ($role === 'agency') {
            $user->name = $validated['agency_name'];
        } else {
            $user->name = trim($validated['first_name'].' '.($validated['last_name'] ?? '')) ?: $user->name;
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $user->profile()->updateOrCreate([], [
            'first_name' => $validated['first_name'] ?? $validated['contact_person'],
            'last_name' => $validated['last_name'] ?? '',
            'age' => $validated['age'] ?? null,
            'phone' => $validated['phone'],
            'address' => $validated['agency_address'] ?? $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
        ]);

        if ($role === 'care_giver') {
            $user->careGiverProfile()->updateOrCreate([], [
                'type' => $validated['care_giver_type'],
            ]);

            $this->replaceDocument($request, $user, 'cv', 'care-giver-cvs');
        }

        if ($role === 'care_seeker') {
            $user->careSeekerProfile()->updateOrCreate([], [
                'care_for' => $validated['care_for'],
                'care_needs' => $validated['care_needs'],
                'preferred_contact_method' => $validated['preferred_contact_method'] ?? null,
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
                'mobility_level' => $validated['mobility_level'] ?? null,
                'medical_notes' => $validated['medical_notes'] ?? null,
            ]);
        }

        if ($role === 'agency') {
            $user->agencyProfile()->updateOrCreate([], [
                'agency_name' => $validated['agency_name'],
                'contact_person' => $validated['contact_person'],
                'agency_address' => $validated['agency_address'],
                'services_offered' => $validated['services_offered'],
            ]);

            $this->replaceDocument($request, $user, 'agency_license', 'agency-licenses');
        }

        return Redirect::route('profile.edit');
    }

    private function replaceDocument(Request $request, User $user, string $type, string $directory): void
    {
        if (! $request->hasFile($type)) {
            return;
        }

        $file = $request->file($type);
        $existingDocuments = $user->documents()->where('type', $type)->get();

        foreach ($existingDocuments as $document) {
            Storage::disk($document->disk)->delete($document->path);
            $document->delete();
        }

        $user->documents()->create([
            'type' => $type,
            'disk' => 'local',
            'path' => $file->store($directory, 'local'),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => [
                'required',
                File::image()
                    ->types(['jpg', 'jpeg', 'png', 'webp'])
                    ->max(5 * 1024),
            ],
        ]);

        $request->user()
            ->addMediaFromRequest('avatar')
            ->toMediaCollection('avatar');

        return Redirect::route('profile.edit');
    }

    public function destroyAvatar(Request $request): RedirectResponse
    {
        $request->user()->clearMediaCollection('avatar');

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
