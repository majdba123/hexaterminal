<?php

namespace Tests\Feature;

use Database\Seeders\DemoContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class SeederGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_refuses_to_run_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('blocked in production');

        (new DemoContentSeeder)->run();
    }

    public function test_demo_seeder_runs_outside_production(): void
    {
        $this->assertSame('testing', $this->app->environment());

        (new DemoContentSeeder)->run();

        $this->assertDatabaseHas('industries', ['slug' => 'demo-fintech']);
        $this->assertDatabaseHas('systems', ['slug' => 'demo-ledger-platform']);
    }
}
