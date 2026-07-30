<?php

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Health & Readiness Routes
|--------------------------------------------------------------------------
| Registered in App\Providers\RouteServiceProvider under the /api prefix
| with no rate limiter (see the note there). Responses are safe operational
| status only -- never secrets, credentials, or exception traces.
|
|   GET /api/health         liveness  -- process up, dependency-free, always 200
|   GET /api/health/ready   readiness -- database + cache reachable (503 if not)
|
| Note: this app runs the legacy Laravel HTTP-kernel skeleton, so it has no
| framework-default `/up` route; these endpoints replace it.
*/

Route::get('health', [HealthController::class, 'live'])->name('health.live');
Route::get('health/ready', [HealthController::class, 'ready'])->name('health.ready');
