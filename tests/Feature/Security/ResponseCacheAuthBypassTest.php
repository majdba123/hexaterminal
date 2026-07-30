<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ResponseCacheAuthBypassTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Regression test for a global-middleware ordering bug: ResponseCache ran
     * in the `web` group, which executes BEFORE route-level `auth`/`admin`
     * middleware. A cache hit returned immediately without calling $next(),
     * so once a real admin's dashboard response was cached, any unauthenticated
     * visitor requesting the same URL within the TTL received the cached
     * admin page without ever passing the auth check.
     */
    public function test_unauthenticated_request_to_admin_route_is_never_served_from_cache(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'cache-admin@hexaterminal.test',
            'password' => bcrypt('a-long-secure-password'),
            'type' => 1,
        ]);

        // A real admin visits the dashboard — if caching were still unsafe,
        // this would populate the shared response cache for the URL.
        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();

        // An unauthenticated visitor must be redirected to login (not served
        // a cached 200 copy of the admin page). The `auth` middleware's
        // default redirect target is the generic `login` route.
        $this->forgetAuthenticatedUser();
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect(route('login'));
    }

    public function test_admin_route_response_is_not_written_to_the_cache_store(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'cache-admin2@hexaterminal.test',
            'password' => bcrypt('a-long-secure-password'),
            'type' => 1,
        ]);

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();

        $key = 'response_cache:'.md5(url('/admin/dashboard'));
        $this->assertFalse(Cache::has($key), 'Auth-gated route must never populate the shared response cache.');
    }

    private function forgetAuthenticatedUser(): void
    {
        // Ensure the next request carries no auth state from actingAs().
        $this->app['auth']->forgetGuards();
    }
}
