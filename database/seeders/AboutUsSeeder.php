<?php

namespace Database\Seeders;

use App\Models\About_Us;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AboutUsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if record already exists
        if (About_Us::count() === 0) {
            About_Us::create([
                'company_name' => 'hexa_termenal',
                'company_description' => 'A brief description about your company. Explain what your company does, its mission, and values.',
                'website_url' => 'https://yourcompany.com',
                'foundation_date' => '2020-01-01',
                'contact_email' => 'contact@yourcompany.com',
                'facebook_url' => 'https://facebook.com/yourcompany',
                'instagram_url' => 'https://instagram.com/yourcompany',
                'linkedin_url' => 'https://linkedin.com/company/yourcompany',
                'github_url' => 'https://github.com/yourcompany',
                'company_logo' => 'https://github.com/yourcompany', // You can add a logo path later
            ]);
            
            $this->command->info('About Us record created successfully!');
        } else {
            $this->command->info('About Us record already exists. Skipping...');
        }
    }
}