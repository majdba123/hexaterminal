<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->type == 1) {
            return $next($request);
        }

        // API requests get JSON response
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['error' => 'You are not authorized'], 401);
        }

        // Web requests get redirected to admin login
        return redirect()->route('admin.login');
    }
}

