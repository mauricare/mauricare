<?php

namespace App\Providers;

use App\Listeners\SendCareBookingNotifications;
use App\Models\CareBooking;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Password::defaults(fn (): Password => Password::min(12)
            ->mixedCase()
            ->numbers()
            ->symbols());

        Vite::prefetch(concurrency: 3);

        CareBooking::updated(new SendCareBookingNotifications);
    }
}
