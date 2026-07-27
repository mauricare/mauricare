<?php

use App\Http\Controllers\CareBookingActionController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
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

    Route::get('messages/unread-count', [MessageController::class, 'unreadCount'])
        ->name('api.messages.unread-count');
    Route::get('messages/contacts', [MessageController::class, 'contacts'])
        ->name('api.messages.contacts');
    Route::get('messages/{user}', [MessageController::class, 'conversation'])
        ->name('api.messages.conversation');
    Route::post('messages/{user}', [MessageController::class, 'store'])
        ->name('api.messages.store');

    Route::get('notifications', [NotificationController::class, 'index'])
        ->name('api.notifications.index');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])
        ->name('api.notifications.mark-all-read');
});
