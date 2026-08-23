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
            TrustPagesSeeder::class,
            TeamMembersSeeder::class,
            EngagementModelsSeeder::class,
            FaqItemsSeeder::class,
            SystemsSeeder::class,
            HexaPortfolioSeeder::class,
            RakezContentSeeder::class,
            VetoraSystemUseCaseSeeder::class,
            VetoraCaseStudySeeder::class,
            MalikCaseStudySeeder::class,
            IndustriesSeeder::class,
        ]);
    }
}
