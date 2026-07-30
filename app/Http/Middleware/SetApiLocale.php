<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sets the app locale for the duration of a public v1 API request from a
 * `?locale=en|ar` query param, so translatable model accessors
 * (spatie/laravel-translatable) return the right language without every
 * API Resource needing its own locale-picking logic.
 */
class SetApiLocale
{
    private const SUPPORTED = ['en', 'ar'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->query('locale');

        if (is_string($locale) && in_array($locale, self::SUPPORTED, true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
