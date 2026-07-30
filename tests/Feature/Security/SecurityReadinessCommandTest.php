<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

/**
 * Behaviour of `php artisan hexa:security-readiness`: it must fail closed (P0,
 * non-zero exit) on an insecure production configuration and pass on a safe one.
 */
class SecurityReadinessCommandTest extends TestCase
{
    public function test_reports_no_blockers_in_local_environment(): void
    {
        $this->artisan('hexa:security-readiness')
            ->assertExitCode(0);
    }

    public function test_flags_debug_and_legacy_as_p0_in_production(): void
    {
        app()->detectEnvironment(fn () => 'production');
        config([
            'app.debug' => true,
            'legacy.api' => true,
            'legacy.admin' => true,
        ]);

        $this->artisan('hexa:security-readiness')
            ->assertExitCode(1);
    }

    public function test_json_output_lists_blocker_count(): void
    {
        app()->detectEnvironment(fn () => 'production');
        config(['app.debug' => true]);

        $this->artisan('hexa:security-readiness --json')
            ->assertExitCode(1);
    }

    public function test_never_prints_secret_values(): void
    {
        config([
            'revalidation.enabled' => true,
            'revalidation.secret' => 'super-secret-value',
        ]);

        $this->artisan('hexa:security-readiness')
            ->doesntExpectOutputToContain('super-secret-value')
            ->assertExitCode(0);
    }
}
