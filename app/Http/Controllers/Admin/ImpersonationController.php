<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function users(Request $request): JsonResponse
    {
        abort_if($request->session()->has('impersonator_id'), 409, 'End the current impersonation first.');

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($validated['search'] ?? '');

        $users = User::query()
            ->whereKeyNot($request->user()->getKey())
            ->with(['roles:id,name', 'media'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'email']);

        return response()->json([
            'data' => $users->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->values(),
                'avatar_url' => $user->avatar_url,
            ]),
        ]);
    }

    public function start(Request $request, User $user): RedirectResponse
    {
        abort_if($request->session()->has('impersonator_id'), 409, 'End the current impersonation first.');
        abort_if($request->user()->is($user), 422, 'You cannot impersonate your own account.');

        $request->session()->put([
            'impersonator_id' => $request->user()->getKey(),
            'impersonated_user_id' => $user->getKey(),
        ]);
        $request->session()->forget('auth.password_confirmed_at');

        if ($user->hasRole('agency') || $user->agencyProfile()->exists()) {
            return redirect()->route('account.verification');
        }

        return redirect()->route('dashboard');
    }

    public function stop(Request $request): RedirectResponse
    {
        $impersonatorId = $request->session()->get('impersonator_id');

        abort_unless($impersonatorId, 403, 'No active impersonation session.');

        $administrator = User::query()->find($impersonatorId);

        if (! $administrator?->hasRole('admin')) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('status', 'The administrator account is no longer available.');
        }

        $request->session()->forget([
            'impersonator_id',
            'impersonated_user_id',
            'auth.password_confirmed_at',
        ]);
        Auth::guard('web')->setUser($administrator);
        $request->setUserResolver(fn (): User => $administrator);

        return redirect()->route('dashboard');
    }
}
