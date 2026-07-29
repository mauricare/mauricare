<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUpdateUserRequest;
use App\Models\CareBooking;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    public function careSeekers(Request $request): JsonResponse
    {
        return $this->indexForRole($request, 'care_seeker');
    }

    public function careGivers(Request $request): JsonResponse
    {
        return $this->indexForRole($request, 'care_giver');
    }

    public function show(User $user): JsonResponse
    {
        $this->ensureManagedUser($user);
        $user->load(['profile', 'careSeekerProfile', 'careGiverProfile', 'media']);

        return response()->json(['data' => $this->serializeUser($user, true)]);
    }

    public function update(AdminUpdateUserRequest $request, User $user): JsonResponse
    {
        $role = $this->ensureManagedUser($user);
        $validated = $request->validated();

        DB::transaction(function () use ($user, $role, $validated): void {
            $user->update([
                'name' => trim($validated['first_name'].' '.$validated['last_name']),
                'email' => $validated['email'],
            ]);

            $user->profile()->updateOrCreate([], [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'age' => $validated['age'],
                'phone' => $validated['phone'],
                'address' => $validated['address'] ?? null,
                'city' => $validated['city'],
            ]);

            if ($role === 'care_giver') {
                $user->careGiverProfile()->updateOrCreate([], [
                    'type' => $validated['care_giver_type'],
                ]);
            } else {
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
        });

        $user->load(['profile', 'careSeekerProfile', 'careGiverProfile', 'media']);

        return response()->json([
            'message' => 'User updated successfully.',
            'data' => $this->serializeUser($user, true),
        ]);
    }

    public function updateStatus(Request $request, User $user): JsonResponse
    {
        $role = $this->ensureManagedUser($user);
        $validated = $request->validate(['is_active' => ['required', 'boolean']]);

        $relation = $role === 'care_giver'
            ? $user->careGiverProfile()
            : $user->careSeekerProfile();

        $profile = $relation->first();
        abort_unless($profile, 422, 'The user does not have the required role profile.');
        $profile->update(['is_active' => $validated['is_active']]);

        return response()->json([
            'message' => $validated['is_active'] ? 'User activated.' : 'User deactivated.',
            'is_active' => $profile->is_active,
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        abort_if($request->user()->is($user), 422, 'You cannot delete your own account.');
        $this->ensureManagedUser($user);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }

    private function indexForRole(Request $request, string $role): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);
        $search = trim($validated['search'] ?? '');

        $users = User::role($role)
            ->with(['profile', 'careSeekerProfile', 'careGiverProfile', 'media'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('profile', function (Builder $profileQuery) use ($search): void {
                            $profileQuery->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->orWhere('city', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('id')
            ->paginate($validated['per_page'] ?? 10);

        return response()->json([
            'data' => collect($users->items())
                ->map(fn (User $user): array => $this->serializeUser($user))
                ->values(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    private function ensureManagedUser(User $user): string
    {
        if ($user->hasRole('care_giver')) {
            return 'care_giver';
        }

        if ($user->hasRole('care_seeker')) {
            return 'care_seeker';
        }

        abort(404);
    }

    private function serializeUser(User $user, bool $detailed = false): array
    {
        $role = $user->hasRole('care_giver') ? 'care_giver' : 'care_seeker';
        $roleProfile = $role === 'care_giver'
            ? $user->careGiverProfile
            : $user->careSeekerProfile;

        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar_url' => $user->avatar_url,
            'role' => $role,
            'is_active' => (bool) ($roleProfile?->is_active ?? false),
            'created_at' => $user->created_at?->toISOString(),
            'profile' => [
                'first_name' => $user->profile?->first_name,
                'last_name' => $user->profile?->last_name,
                'age' => $user->profile?->age,
                'phone' => $user->profile?->phone,
                'address' => $user->profile?->address,
                'city' => $user->profile?->city,
            ],
        ];

        if ($detailed) {
            $data['role_profile'] = $roleProfile?->toArray() ?? [];

            $bookingCounts = CareBooking::query()
                ->where($role === 'care_giver' ? 'care_giver_id' : 'user_id', $user->id)
                ->selectRaw('status, count(*) as aggregate')
                ->groupBy('status')
                ->pluck('aggregate', 'status');

            $data['booking_counts'] = collect(BookingStatus::cases())
                ->mapWithKeys(fn (BookingStatus $status): array => [
                    $status->value => (int) ($bookingCounts[$status->value] ?? 0),
                ]);
            $data['booking_total'] = $data['booking_counts']->sum();

            if ($role === 'care_giver') {
                $reviews = $user->reviewsReceived()
                    ->with('reviewer.media')
                    ->latest('id')
                    ->get(['id', 'reviewer_id', 'rating', 'comment', 'created_at']);

                $data['average_rating'] = $reviews->isEmpty()
                    ? null
                    : round((float) $reviews->avg('rating'), 1);
                $data['review_count'] = $reviews->count();
                $data['reviews'] = $reviews->map(fn ($review): array => [
                    'id' => $review->id,
                    'reviewer_name' => $review->reviewer->name,
                    'reviewer_avatar_url' => $review->reviewer->avatar_url,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at?->toISOString(),
                ])->values();
            }
        }

        return $data;
    }
}
