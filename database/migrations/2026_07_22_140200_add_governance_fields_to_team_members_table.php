<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Team publication governance: a member must have explicit
 * `publication_consent` in addition to `is_published` before the public
 * scope exposes them (see App\Models\TeamMember::scopePublished). Existing
 * rows that are already `is_published = true` are backfilled with consent so
 * current behaviour/tests are unaffected; new rows default to no consent
 * (fail closed) until an editor explicitly grants it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->json('expertise')->nullable()->after('specialization');
            $table->json('languages')->nullable()->after('expertise');
            $table->string('location')->nullable()->after('languages');
            $table->boolean('publication_consent')->default(false)->after('is_published');
            $table->boolean('is_founder')->default(false)->after('publication_consent');
            $table->boolean('seo_eligible')->default(false)->after('is_founder');
            $table->boolean('person_jsonld_eligible')->default(false)->after('seo_eligible');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('next_review_at')->nullable();
        });

        DB::table('team_members')->where('is_published', true)->update(['publication_consent' => true]);
    }

    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->dropColumn([
                'expertise', 'languages', 'location', 'publication_consent',
                'is_founder', 'seo_eligible', 'person_jsonld_eligible',
                'reviewed_at', 'next_review_at',
            ]);
        });
    }
};
