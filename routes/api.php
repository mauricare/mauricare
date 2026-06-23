<?php

use App\Rest\Controllers\UsersController;
use Illuminate\Support\Facades\Route;
use Lomkit\Rest\Facades\Rest;

Route::middleware('auth:sanctum')->group(function (): void {
    Rest::resource('users', UsersController::class);
});
