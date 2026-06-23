<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\JobController;
use App\Http\Controllers\Admin\ApprenticeshipController;
use App\Http\Controllers\Admin\MarketplaceController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SubscriptionController;

/*
|--------------------------------------------------------------------------
| Admin Auth Routes (guest only)
|--------------------------------------------------------------------------
*/
// 1. Déclaration de la route absolue exigée par le moteur natif de Laravel
Route::get('admin/reset-password/{token}', [AuthController::class, 'showReset'])
    ->middleware('guest:admin')
    ->name('password.reset'); // 🔥 Strictement sans le préfixe "admin."

// 2. Votre groupe d'authentification existant pour le reste des actions
Route::middleware('guest:admin')->prefix('admin')->name('admin.')->group(function () {

    // Login
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

    // Forgot password
    Route::get('/forgot-password', [AuthController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendReset'])->name('password.email');

    // Reset password (Action de soumission POST uniquement)
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Admin Panel Routes (auth + admin middleware)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:admin', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ── Dashboard ──────────────────────────────────────────────────────────
    Route::get('/',           [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard',  [DashboardController::class, 'index'])->name('dashboard.alt');
    Route::get('/export',     [DashboardController::class, 'export'])->name('export');
    Route::get('/search',     [DashboardController::class, 'search'])->name('search');
    Route::get('/activity',   [DashboardController::class, 'activity'])->name('activity');

    // ── Users ──────────────────────────────────────────────────────────────
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/',                   [UserController::class, 'index'])      ->name('index');  // admin.users
        Route::get('/export',             [UserController::class, 'export'])     ->name('export');
        Route::get('/{user}',             [UserController::class, 'show'])       ->name('show');
        Route::delete('/{user}',          [UserController::class, 'destroy'])    ->name('destroy');
        Route::patch('/{user}/suspend',   [UserController::class, 'suspend'])    ->name('suspend');
        Route::patch('/{user}/deactivate',[UserController::class, 'deactivate'])->name('deactivate');
        Route::patch('/{user}/reactivate',[UserController::class, 'reactivate'])->name('reactivate');
    });
    // Shorthand used in sidebar route() helpers
    Route::get('/users', [UserController::class, 'index'])->name('users');

    // ── Jobs ───────────────────────────────────────────────────────────────
    Route::prefix('jobs')->name('jobs.')->group(function () {
        Route::get('/',              [JobController::class, 'index'])  ->name('index');
        Route::get('/export',        [JobController::class, 'export']) ->name('export');
        Route::get('/{job}',         [JobController::class, 'show'])   ->name('show');
        Route::delete('/{job}',      [JobController::class, 'destroy'])->name('destroy');
        Route::patch('/{job}/close', [JobController::class, 'close'])  ->name('close');
        Route::patch('/{job}/reopen',[JobController::class, 'reopen']) ->name('reopen');
    });
    Route::get('/jobs', [JobController::class, 'index'])->name('jobs');

    // ── Apprenticeships ────────────────────────────────────────────────────
    Route::prefix('apprenticeships')->name('apprenticeships.')->group(function () {
        Route::get('/',                        [ApprenticeshipController::class, 'index'])        ->name('index');
        Route::get('/export',                  [ApprenticeshipController::class, 'export'])       ->name('export');
        Route::get('/opportunity/{id}',        [ApprenticeshipController::class, 'show'])         ->name('show');
        Route::delete('/opportunity/{id}',     [ApprenticeshipController::class, 'destroy'])      ->name('destroy');
        Route::patch('/opportunity/{id}/close',[ApprenticeshipController::class, 'close'])        ->name('close');
        Route::patch('/opportunity/{id}/reopen',[ApprenticeshipController::class, 'reopen'])      ->name('reopen');
        Route::get('/applicant/{id}',          [ApprenticeshipController::class, 'showApplicant'])->name('applicant');
    });
    Route::get('/apprenticeships', [ApprenticeshipController::class, 'index'])->name('apprenticeships');

    // ── Marketplace ────────────────────────────────────────────────────────
    Route::prefix('marketplace')->name('marketplace.')->group(function () {
        Route::get('/',                       [MarketplaceController::class, 'index'])     ->name('index');
        Route::get('/export',                 [MarketplaceController::class, 'export'])    ->name('export');
        Route::get('/{listing}',              [MarketplaceController::class, 'show'])      ->name('show');
        Route::delete('/{listing}',           [MarketplaceController::class, 'destroy'])   ->name('destroy');
        Route::patch('/{listing}/deactivate', [MarketplaceController::class, 'deactivate'])->name('deactivate');
        Route::patch('/{listing}/activate',   [MarketplaceController::class, 'activate'])  ->name('activate');
        Route::patch('/{listing}/flag',       [MarketplaceController::class, 'flag'])      ->name('flag');
    });
    Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace');

    // ── Reviews ────────────────────────────────────────────────────────────
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/',          [ReviewController::class, 'index'])  ->name('index');
        Route::get('/{review}',  [ReviewController::class, 'show'])   ->name('show');
        Route::delete('/{review}',[ReviewController::class, 'destroy'])->name('destroy');
    });
    Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews');

    // ── Subscriptions ──────────────────────────────────────────────────────
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/',               [SubscriptionController::class, 'index'])  ->name('index');
        Route::get('/{sub}',          [SubscriptionController::class, 'show'])   ->name('show');
        Route::patch('/{sub}/cancel', [SubscriptionController::class, 'cancel']) ->name('cancel');
    });
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions');

    // ── Settings (placeholder) ─────────────────────────────────────────────
    Route::get('/settings', fn() => view('admin.settings'))->name('settings');
});
