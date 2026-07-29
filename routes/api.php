<?php

use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\CareBookingActionController;
use App\Http\Controllers\CareGiverProfileController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReviewController;
use App\Rest\Controllers\CareBookingsController;
use App\Rest\Controllers\UsersController;
use Illuminate\Support\Facades\Route;
use Lomkit\Rest\Facades\Rest;

Rest::resource('users', UsersController::class)->only(['mutate']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::prefix('admin')->middleware('admin')->name('api.admin.')->group(function (): void {
        Route::get('care-seekers', [AdminUserController::class, 'careSeekers'])->name('care-seekers.index');
        Route::get('care-givers', [AdminUserController::class, 'careGivers'])->name('care-givers.index');
        Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::patch('users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::patch('users/{user}/status', [AdminUserController::class, 'updateStatus'])->name('users.status');
        Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        Route::get('bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
        Route::patch('bookings/{careBooking}/cancel', [AdminBookingController::class, 'cancel'])->name('bookings.cancel');
    });

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
    Route::post('care-bookings/{careBooking}/review', [ReviewController::class, 'store'])
        ->name('api.care-bookings.review');
    Route::patch('reviews/{review}', [ReviewController::class, 'update'])
        ->name('api.reviews.update');
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])
        ->name('api.reviews.destroy');
    Route::get('care-givers/{careGiver}/profile', [CareGiverProfileController::class, 'show'])
        ->name('api.care-givers.profile');
    Route::get('reviews/received', [ReviewController::class, 'received'])
        ->name('api.reviews.received');

    Route::get('messages/unread-count', [MessageController::class, 'unreadCount'])
        ->name('api.messages.unread-count');
    Route::get('messages/contacts', [MessageController::class, 'contacts'])
        ->name('api.messages.contacts');
    Route::get('messages/{user}', [MessageController::class, 'conversation'])
        ->name('api.messages.conversation');
    Route::post('messages/{user}', [MessageController::class, 'store'])
        ->name('api.messages.store');
    Route::patch('messages/{message}', [MessageController::class, 'update'])
        ->name('api.messages.update');
    Route::delete('messages/{message}', [MessageController::class, 'destroy'])
        ->name('api.messages.destroy');

    Route::get('notifications', [NotificationController::class, 'index'])
        ->name('api.notifications.index');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])
        ->name('api.notifications.mark-all-read');
});
