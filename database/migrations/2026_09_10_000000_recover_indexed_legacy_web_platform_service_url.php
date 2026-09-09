<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const FROM = '/en/services/ttoyr-ttbykat-aloyb';

    private const TO = '/en/services/web-platforms-mobile-applications';

    public function up(): void
    {
        if (app()->environment('testing') || ! Schema::hasTable('redirects')) {
            return;
        }

        $now = now();

        DB::table('redirects')->updateOrInsert(
            ['from_path' => self::FROM],
            [
                'to_path' => self::TO,
                'status_code' => 301,
                'is_active' => true,
                'hit_count' => 0,
                'last_hit_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        );
    }

    public function down(): void
    {
        // This repairs an externally indexed public URL. Removing that redirect
        // on rollback would reintroduce the SEO defect, so the recovery mapping
        // intentionally remains in place unless it is explicitly superseded.
    }
};
