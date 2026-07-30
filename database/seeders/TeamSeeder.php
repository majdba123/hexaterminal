<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teamMembers = [
            [
                'first_name' => 'Majd',
                'last_name' => 'Bayer',
                'phone' => '+96395027218',
                'email' => 'majd.bayer@hexaterminal.com',
                'photo' => 'https://drive.google.com/uc?export=view&id=1Cx-Am8xV6TkistDc9jM18wbaQ0x_6ZGz',
                'github_url' => 'https://github.com/majdbayer',
                'cv_file' => 'https://docs.google.com/document/d/1VE-6TenLolMeUrTKCTkbxXggtXVbawbl/edit?usp=drive_link&ouid=101154777687743530485&rtpof=true&sd=true',
                'specialization' => 'Software Development & Business Strategy',
                'position' => 'CEO & Founder',
            ],
            [
                'first_name' => 'Mohamad',
                'last_name' => 'Kahal',
                'phone' => '+966500000002',
                'email' => 'mohamad.kahal@hexaterminal.com',
                'photo' => 'https://drive.google.com/uc?export=view&id=1t_RKtQTx-twQfsYSA2B4sifj-K1jG51q',
                'github_url' => 'https://github.com/mohamadkahal',
                'cv_file' => 'https://drive.google.com/file/d/1qP8EEuN2QxWjOJPlzvPGcjmYZt6jGiJg/view?usp=drive_link',
                'specialization' => 'Software Development',
                'position' => 'Senior Frontend Engineer',
            ],
        ];

        foreach ($teamMembers as $member) {
            Team::firstOrCreate(
                ['email' => $member['email']],
                $member
            );
        }

        $this->command->info(count($teamMembers) . ' team members seeded successfully!');
    }
}
