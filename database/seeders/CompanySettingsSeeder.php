<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use JsonException;

class CompanySettingsSeeder extends Seeder
{
    private const DATA_FILE = 'database/seeders/data/company_settings_seed_data.json';

    public function run(): void
    {
        $data = $this->seedData();
        $public = $data['confirmed_public_data'];
        $empty = $data['leave_empty_until_confirmed'];
        $leadRecipients = $data['assumptions']['lead_recipients'] ?? [];

        CompanySetting::current()->update([
            'company_name' => $public['company_name'],
            'tagline' => $public['tagline'],
            'description' => $public['description'],
            'email' => $public['email'],
            'phone' => $public['phone'],
            'whatsapp' => $empty['whatsapp'],
            'address' => $empty['address'],
            'social_links' => $empty['social_links'],
            'booking_url' => $empty['booking_url'],
            'lead_recipients' => implode(',', $leadRecipients),
            'default_og_image' => $empty['default_og_image'],
            'analytics_provider' => $empty['analytics_provider'],
            'analytics_site_id' => $empty['analytics_site_id'],
            'footer_note' => $public['footer_note'],
        ]);
    }

    /** @return array<string, mixed> */
    private function seedData(): array
    {
        try {
            $data = json_decode(
                File::get(base_path(self::DATA_FILE)),
                true,
                flags: JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $exception) {
            throw new \RuntimeException('The approved company settings seed data is invalid JSON.', previous: $exception);
        }

        if (! is_array($data)) {
            throw new \RuntimeException('The approved company settings seed data must decode to an object.');
        }

        return $data;
    }
}
