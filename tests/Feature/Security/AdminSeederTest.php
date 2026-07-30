<?php

namespace Tests\Feature\Security;

use Database\Seeders\RolesSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // UsersTableSeeder assigns the 'admin' role, which must exist first.
        $this->seed(RolesSeeder::class);
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
            \App\Models\User::where('email', 'boss@hexaterminal.test')->first()->hasRole('admin')
        );
    }
}
