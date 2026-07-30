<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting database seeding...');
        $this->command->newLine();

        $this->call([
            // 0. Roles (must exist before UsersTableSeeder assigns one)
            RolesSeeder::class,

            // 1. Users (admin account)
            UsersTableSeeder::class,

            // 2. About Us (single company record)
            AboutUsSeeder::class,

            // 3. Team members
            TeamSeeder::class,

            // 4. Services (must be before Projects due to foreign key)
            ServicesSeeder::class,

            // 5. Projects with images & features (depends on Services)
            ProjectsSeeder::class,

            // 6. FAQs
            FAQSeeder::class,

            // 7. Reviews
            ReviewSeeder::class,

            // 8. Videos
            VideoSeeder::class,

            // 9. Contact Us messages
            ContactUsSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('✅ Database seeding completed successfully!');
    }
}
