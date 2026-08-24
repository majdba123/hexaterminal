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

        // Re-run only the idempotent Hexa portfolio content so Yusuf's approved
        // portrait is copied through TeamMemberSeedImageSynchronizer to the
        // Laravel public disk, exactly like the existing founder portrait flow.
        app(HexaPortfolioSeeder::class)->run();

        $member = TeamMember::query()->where('slug', 'yusuf-jojeh')->first();

        if (! $member) {
            return;
        }

        // Persist the storage-relative path. TeamMemberResource turns this into
        // https://api.hexaterminal.com/storage/team/yusuf-jojeh.webp in production.
        $member->photo = 'team/yusuf-jojeh.webp';
        $member->save();
    }

    public function down(): void
    {
        // Non-destructive content migration.
    }
};
