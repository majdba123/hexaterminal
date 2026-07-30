<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Editor-supplied alternative text for the images the public site renders.
 *
 * Every `next/image` on the frontend shipped with a hard-coded `alt=""`
 * because there was nowhere for an editor to put anything else. The only
 * `alt_text` column in the schema is on `imag__progects`, which belongs to the
 * LEGACY Projects layer and never reaches the new public API.
 *
 * `alt=""` is not automatically wrong -- an image that adds nothing beyond
 * adjacent visible text (a card thumbnail inside a link already labelled by
 * its title) *should* be marked decorative, and duplicating the title there
 * only makes screen readers announce it twice. But that judgement belongs to
 * the person placing the image, not to the template. These columns are
 * nullable and the frontend falls back to `alt=""`, so an image stays
 * decorative until somebody deliberately describes it.
 *
 * Translatable (json, per Spatie\Translatable) because the site is bilingual:
 * an Arabic reader needs the Arabic description.
 */
return new class extends Migration
{
    /** table => [image column => new alt column] */
    private const TARGETS = [
        'service_offerings' => ['cover_image' => 'cover_image_alt'],
        'systems' => ['cover_image' => 'cover_image_alt'],
        'case_studies' => ['cover_image' => 'cover_image_alt'],
        'industries' => ['cover_image' => 'cover_image_alt'],
        'articles' => ['cover_image' => 'cover_image_alt'],
        'team_members' => ['photo' => 'photo_alt'],
    ];

    public function up(): void
    {
        foreach (self::TARGETS as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table, $columns) {
                foreach ($columns as $imageColumn => $altColumn) {
                    if (Schema::hasColumn($table, $altColumn)) {
                        continue;
                    }

                    $blueprint->json($altColumn)->nullable()->after($imageColumn);
                }
            });
        }
    }

    public function down(): void
    {
        foreach (self::TARGETS as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table, $columns) {
                foreach ($columns as $altColumn) {
                    if (Schema::hasColumn($table, $altColumn)) {
                        $blueprint->dropColumn($altColumn);
                    }
                }
            });
        }
    }
};
