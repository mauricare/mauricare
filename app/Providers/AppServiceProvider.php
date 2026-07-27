<?php

namespace App\Providers;

use App\Listeners\SendCareBookingNotifications;
use App\Models\CareBooking;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

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
        Vite::prefetch(concurrency: 3);

        CareBooking::updated(new SendCareBookingNotifications);
    }
}
