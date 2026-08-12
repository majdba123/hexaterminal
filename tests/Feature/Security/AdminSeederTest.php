<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_bootstraps_only_the_admin_account(): void
    {
        config([
            'app.admin_email' => 'bootstrap-admin@hexaterminal.test',
            'app.admin_password' => 'a-long-secure-password',
        ]);

        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'bootstrap-admin@hexaterminal.test')->firstOrFail();
        $this->assertTrue($admin->hasRole('admin'));
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('roles', 1);
        $this->assertDatabaseCount('systems', 0);
        $this->assertDatabaseCount('case_studies', 0);
        $this->assertDatabaseCount('projects', 0);
        $this->assertDatabaseCount('service_offerings', 0);
        $this->assertDatabaseCount('industries', 0);
        $this->assertDatabaseCount('articles', 0);
        $this->assertDatabaseCount('testimonials', 0);
        $this->assertDatabaseCount('faqs', 0);
        $this->assertDatabaseCount('engagement_models', 0);
    }

    public function test_seeder_creates_no_user_when_credentials_missing(): void
    {
        config(['app.admin_email' => null, 'app.admin_password' => null]);

        (new UsersTableSeeder)->run();

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseMissing('users', ['email' => 'admin@example.com']);
    }

    public function test_seeder_never_creates_the_old_default_account(): void
    {
        config(['app.admin_email' => null, 'app.admin_password' => null]);

        (new UsersTableSeeder)->run();

        $this->assertDatabaseMissing('users', ['email' => 'admin@example.com']);
    }

    public function test_seeder_rejects_short_passwords(): void
    {
        config(['app.admin_email' => 'boss@hexaterminal.test', 'app.admin_password' => 'short']);

        $this->expectException(\RuntimeException::class);

        (new UsersTableSeeder)->run();
    }

    public function test_seeder_creates_admin_from_config(): void
    {
        config([
            'app.admin_email' => 'boss@hexaterminal.test',
            'app.admin_password' => 'a-long-secure-password',
        ]);

        (new UsersTableSeeder)->run();

        $this->assertDatabaseHas('users', ['email' => 'boss@hexaterminal.test', 'type' => 1]);
        $this->assertTrue(
            User::where('email', 'boss@hexaterminal.test')->first()->hasRole('admin')
        );
    }
}
