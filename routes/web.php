<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Every route here is a LEGACY surface, isolated behind fail-closed flags in
| config/legacy.php via the `legacy:<surface>` middleware:
|
|   legacy:public_web  legacy Blade public pages (retired in favour of Next.js)
|   legacy:admin       legacy custom admin panel (retired in favour of Filament /cms)
|
| When a surface is disabled (the default in staging/production) the gate
| returns a controlled 404 before any controller runs -- no legacy page renders.
| When explicitly enabled for compatibility work, the gate tags every response
| noindex,nofollow. See docs/security/legacy-security-baseline.md.
|
| Filament's CMS (/cms) is registered by CmsPanelProvider, and the health +
| versioned public API are registered in RouteServiceProvider -- none live in
| this file, so disabling these legacy surfaces never affects the current
| platform.
*/

// ═══ Legacy public Blade website (data loaded via Axios from legacy API) ═══
Route::middleware('legacy:public_web')->group(function () {
    Route::get('/', [WebsiteController::class, 'index'])->name('home');
    Route::get('/projects', [WebsiteController::class, 'projects'])->name('projects');
    Route::get('/project/{id}', [WebsiteController::class, 'projectDetail'])->name('project.detail');
    Route::get('/team/{id}', [WebsiteController::class, 'teamDetail'])->name('team.detail');
    Route::get('/service/{id}', [WebsiteController::class, 'serviceDetail'])->name('service.detail');

    // API Documentation (legacy landing)
    Route::get('/api-docs', [LandingController::class, 'index'])->name('api.docs');
});

// ═══ Legacy custom admin panel (superseded by Filament /cms) ═══
Route::middleware('legacy:admin')->group(function () {
    Route::get('/admin', [AdminController::class, 'login'])->name('admin.login');
    Route::post('/admin/login', [AdminController::class, 'authenticate'])
        ->middleware('throttle:5,1')
        ->name('admin.authenticate');
    Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

    Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/teams', [AdminController::class, 'teams'])->name('admin.teams');
        Route::get('/services', [AdminController::class, 'services'])->name('admin.services');
        Route::get('/projects', [AdminController::class, 'projects'])->name('admin.projects');
        Route::get('/about', [AdminController::class, 'about'])->name('admin.about');
        Route::get('/faq', [AdminController::class, 'faq'])->name('admin.faq');
        Route::get('/reviews', [AdminController::class, 'reviews'])->name('admin.reviews');
        Route::get('/videos', [AdminController::class, 'videos'])->name('admin.videos');
        Route::get('/contacts', [AdminController::class, 'contacts'])->name('admin.contacts');
    });

    // Legacy authentication scaffolding. Belongs to the legacy admin surface
    // only; Filament has its own auth at /cms.
    Auth::routes();
});
