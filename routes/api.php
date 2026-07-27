<?php

use App\Http\Controllers\CareBookingActionController;
use App\Rest\Controllers\CareBookingsController;
use App\Rest\Controllers\UsersController;
use Illuminate\Support\Facades\Route;
use Lomkit\Rest\Facades\Rest;

Rest::resource('users', UsersController::class)->only(['mutate']);

Route::middleware('auth:sanctum')->group(function (): void {
    Rest::resource('users', UsersController::class)->except(['mutate']);
    Rest::resource('care-bookings', CareBookingsController::class);

    Route::post('care-bookings/{careBooking}/assign', [CareBookingActionController::class, 'assign'])
        ->name('api.care-bookings.assign');
    Route::post('care-bookings/{careBooking}/complete-visit', [CareBookingActionController::class, 'completeVisit'])
        ->name('api.care-bookings.complete-visit');
    Route::post('care-bookings/{careBooking}/confirm-payment', [CareBookingActionController::class, 'confirmPayment'])
        ->name('api.care-bookings.confirm-payment');
    Route::post('care-bookings/{careBooking}/close', [CareBookingActionController::class, 'close'])
        ->name('api.care-bookings.close');
});
