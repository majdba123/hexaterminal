<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
            ServicesSeeder::class,
            CompanySettingsSeeder::class,
            TeamMembersSeeder::class,
            EngagementModelsSeeder::class,
            FaqItemsSeeder::class,
            SystemsSeeder::class,
            VetoraSystemUseCasesSeeder::class,
            MalikCaseStudySeeder::class,
            IndustriesSeeder::class,
        ]);
    }
}
