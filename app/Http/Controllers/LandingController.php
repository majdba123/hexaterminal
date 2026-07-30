<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    const CACHE_KEY = 'api_routes_documentation';
    const CACHE_TTL = 3600; // 1 hour

    public function index()
    {
        // Use Redis cache with fallback to default cache driver
        try {
            $cacheStore = config('cache.default') === 'redis' ? 'redis' : null;
            $cache = $cacheStore ? Cache::store($cacheStore) : Cache::store();

            $apiRoutes = $cache->remember(
                self::CACHE_KEY,
                self::CACHE_TTL,
                function () {
                    return $this->getApiRoutes();
                }
            );
        } catch (\Exception $e) {
            // Fallback: use default cache or regenerate
            Log::warning('Cache error, using default store: ' . $e->getMessage());
            $apiRoutes = Cache::remember(
                self::CACHE_KEY,
                self::CACHE_TTL,
                function () {
                    return $this->getApiRoutes();
                }
            );
        }

        $baseUrl = config('app.url');

        return view('landing', [
            'routes' => $apiRoutes,
            'baseUrl' => $baseUrl
        ]);
    }

    private function getApiRoutes()
    {
        // Get routes collection once - this is cached by Laravel
        $routeCollection = Route::getRoutes();
        $allRoutes = $routeCollection->getRoutes();

        // Pre-allocate arrays for better performance
        $processedRoutes = [];
        $methodPriority = ['GET' => 1, 'POST' => 2, 'PUT' => 3, 'DELETE' => 4];

        // Single pass processing - most efficient
        foreach ($allRoutes as $route) {
            $uri = $route->uri();

            // Fast check: skip if not API route (early exit)
            if (!str_starts_with($uri, 'api')) {
                continue;
            }

            // Get methods once
            $methods = array_filter($route->methods(), fn($m) => $m !== 'HEAD');
            if (empty($methods)) {
                continue;
            }

            $primaryMethod = reset($methods);

            // Get middleware once (expensive, but necessary)
            $middleware = $route->gatherMiddleware();

            // Fast boolean checks
            $requiresAuth = in_array('auth:sanctum', $middleware, true) ||
                          in_array('auth', $middleware, true);
            $requiresAdmin = in_array('admin', $middleware, true);

            // Extract prefix efficiently
            $prefix = $this->extractPrefix($uri);

            // Build route data
            $routeData = [
                'method' => $primaryMethod,
                'methods' => array_values($methods),
                'uri' => $uri,
                'name' => $route->getName(),
                'prefix' => $prefix,
                'description' => $this->generateDescription($primaryMethod, $uri, $prefix),
                'requiresAuth' => $requiresAuth,
                'requiresAdmin' => $requiresAdmin,
                'priority' => $methodPriority[$primaryMethod] ?? 99,
            ];

            // Group by prefix
            if (!isset($processedRoutes[$prefix])) {
                $processedRoutes[$prefix] = [];
            }
            $processedRoutes[$prefix][] = $routeData;
        }

        // Sort each group by priority
        foreach ($processedRoutes as $prefix => &$routes) {
            usort($routes, fn($a, $b) => $a['priority'] <=> $b['priority']);
            // Remove priority from final output
            foreach ($routes as &$route) {
                unset($route['priority']);
            }
        }

        return $processedRoutes;
    }

    private function extractPrefix(string $uri): string
    {
        // Fast prefix extraction
        $parts = explode('/', $uri, 3);
        return $parts[1] ?? 'general';
    }

    private function generateDescription($method, $uri, $prefix)
    {
        $action = '';
        if (str_contains($uri, 'index')) {
            $action = 'Get all ' . str_replace('_', ' ', $prefix);
        } elseif (str_contains($uri, 'store')) {
            $action = 'Create a new ' . str_replace('_', ' ', rtrim($prefix, 's'));
        } elseif (str_contains($uri, 'show')) {
            $action = 'Get a specific ' . str_replace('_', ' ', rtrim($prefix, 's'));
        } elseif (str_contains($uri, 'update')) {
            $action = 'Update an existing ' . str_replace('_', ' ', rtrim($prefix, 's'));
        } elseif (str_contains($uri, 'delete') || str_contains($uri, 'destroy')) {
            $action = 'Delete a ' . str_replace('_', ' ', rtrim($prefix, 's'));
        } elseif (str_contains($uri, 'login')) {
            $action = 'Authenticate and get access token';
        } elseif (str_contains($uri, 'user')) {
            $action = 'Get authenticated user information';
        } else {
            $action = 'Access ' . str_replace('_', ' ', $prefix) . ' resource';
        }

        return $action;
    }
}

