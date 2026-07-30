<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApplyImpersonation
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasSession()) {
            return $next($request);
        }

        $impersonatedUserId = $request->session()->get('impersonated_user_id');

        $webGuard = Auth::guard('web');

        if (! $impersonatedUserId || ! $webGuard->check()) {
            return $next($request);
        }

        $authorizationGuard = Auth::guard();
        $administrator = $webGuard->user();
        $authorizationUser = $authorizationGuard->user();
        $userResolver = Auth::userResolver();
        $impersonatedUser = User::query()->find($impersonatedUserId);

        if (! $impersonatedUser) {
            $request->session()->forget([
                'impersonator_id',
                'impersonated_user_id',
            ]);

            return $next($request);
        }

        $webGuard->setUser($impersonatedUser);
        $authorizationGuard->setUser($impersonatedUser);
        Auth::resolveUsersUsing(fn (): User => $impersonatedUser);
        $request->setUserResolver(fn (): User => $impersonatedUser);

        try {
            return $next($request);
        } finally {
            $webGuard->setUser($administrator);
            $authorizationGuard->setUser($authorizationUser);
            Auth::resolveUsersUsing($userResolver);
            $request->setUserResolver(fn (): User => $administrator);
        }
    }
}
