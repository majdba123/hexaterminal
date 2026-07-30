<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Seed the initial admin user.
     *
     * Credentials are read from the environment (ADMIN_EMAIL / ADMIN_PASSWORD).
     * No default credentials are ever created: if the variables are missing,
     * seeding the admin user is skipped (with a warning) instead of falling
     * back to a well-known email/password pair.
     */
    public function run(): void
    {
        $email = config('app.admin_email');
        $password = config('app.admin_password');

        if (empty($email) || empty($password)) {
            $message = 'ADMIN_EMAIL / ADMIN_PASSWORD not set — skipping admin user seeding. '
                .'Set both variables in .env and re-run `php artisan db:seed --class=UsersTableSeeder` to create the admin account.';

            if (app()->environment('production')) {
                throw new \RuntimeException($message);
            }

            $this->command?->warn($message);

            return;
        }



        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => config('app.admin_name', 'Administrator'),
                'password' => Hash::make($password),
                // Legacy /admin panel authorization (until cutover).
                'type' => 1,
            ]
        );

        // New /cms (Filament) panel authorization.
        $user->assignRole('admin');

        // Never echo credentials.
        $this->command?->info('Admin user ensured for the configured ADMIN_EMAIL.');
    }
}
