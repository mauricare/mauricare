<?php

use App\Enums\BookingStatus;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProfileController;
use App\Models\Review;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Inertia;

Route::get('/', function () {
    $testimonials = Review::query()
        ->whereNotNull('comment')
        ->where('comment', '!=', '')
        ->whereHas('booking', fn ($query) => $query->where('status', BookingStatus::Closed->value))
        ->with([
            'reviewer:id,name',
            'booking:id,care_type',
        ])
        ->latest()
        ->limit(9)
        ->get()
        ->map(function (Review $review): array {
            $nameParts = preg_split('/\s+/', trim($review->reviewer->name), -1, PREG_SPLIT_NO_EMPTY);
            $firstName = $nameParts[0] ?? 'Care seeker';
            $lastInitial = isset($nameParts[1]) ? ' '.Str::upper(Str::substr($nameParts[1], 0, 1)).'.' : '';
            $initials = collect($nameParts)
                ->take(2)
                ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
                ->implode('');

            return [
                'id' => $review->id,
                'name' => $firstName.$lastInitial,
                'initials' => $initials ?: 'CS',
                'rating' => $review->rating,
                'text' => $review->comment,
                'role' => Str::headline($review->booking->care_type).' client',
            ];
        });

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'testimonials' => $testimonials,
    ]);
});

Route::get('/dashboard', function (Request $request) {
    if ($request->user()->hasRole('admin')) {
        return Inertia::render('AdminDashboard');
    }

    $isCareGiver = $request->user()->hasRole('care_giver')
        || $request->user()->careGiverProfile()->exists();

    return Inertia::render($isCareGiver ? 'CareGiverDashboard' : 'Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/account-verification', function () {
    return Inertia::render('AccountVerification');
})->middleware('auth')->name('account.verification');

Route::post('/contact', [ContactController::class, 'store'])->name('contact.submit');

Route::middleware('auth')->group(function () {
    Route::prefix('impersonation')->name('impersonation.')->group(function (): void {
        Route::middleware('admin')->group(function (): void {
            Route::get('users', [ImpersonationController::class, 'users'])->name('users');
            Route::post('start/{user}', [ImpersonationController::class, 'start'])->name('start');
        });

        Route::post('stop', [ImpersonationController::class, 'stop'])->name('stop');
    });

    Route::post('/support/contact', [ContactController::class, 'support'])->name('support.contact.submit');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
