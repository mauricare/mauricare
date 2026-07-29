<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
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
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
