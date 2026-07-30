<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $impersonatorId = $request->session()->get('impersonator_id');
        $impersonator = $impersonatorId
            ? User::query()->find($impersonatorId)
            : null;

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    ...$user->toArray(),
                    'avatar_url' => $user->avatar_url,
                    'care_giver_is_active' => $user->hasRole('care_giver')
                        ? (bool) $user->careGiverProfile?->is_active
                        : null,
                    'care_seeker_is_active' => $user->hasRole('care_seeker')
                        ? (bool) $user->careSeekerProfile?->is_active
                        : null,
                ] : null,
                'roles' => $user?->getRoleNames() ?? [],
                'impersonation' => [
                    'active' => (bool) $impersonatorId,
                    'administrator' => $impersonator ? [
                        'id' => $impersonator->id,
                        'name' => $impersonator->name,
                    ] : null,
                ],
            ],
        ];
    }
}
