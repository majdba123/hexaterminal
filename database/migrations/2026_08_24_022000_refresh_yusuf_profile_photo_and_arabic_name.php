<?php

use App\Models\TeamMember;
use Database\Seeders\HexaPortfolioSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        app(HexaPortfolioSeeder::class)->run();

        $member = TeamMember::query()->where('slug', 'yusuf-jojeh')->first();
        if (! $member) {
            return;
        }

        $bioAr = $member->getTranslation('bio', 'ar', false);
        $altAr = $member->getTranslation('photo_alt', 'ar', false);

        if (is_string($bioAr) && $bioAr !== '') {
            $member->setTranslation('bio', 'ar', str_replace('يوسف محمد جوجيه', 'يوسف محمد جوجه', $bioAr));
        }

        if (is_string($altAr) && $altAr !== '') {
            $member->setTranslation('photo_alt', 'ar', str_replace('يوسف محمد جوجيه', 'يوسف محمد جوجه', $altAr));
        }

        $member->photo = '/team/yusuf-jojeh.webp';
        $member->save();
    }

    public function down(): void
    {
        // Non-destructive content migration.
    }
};
