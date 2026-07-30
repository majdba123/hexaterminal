<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Editorial workflow + auditability for the CMS content types.
 *
 * `status` is the editorial state (draft/in_review/approved/scheduled/
 * published/archived). The pre-existing `is_published` + `published_at`
 * pair remains the public-visibility contract consumed by the Publishable
 * scope and the whole API layer -- App\Models\Concerns\HasEditorialWorkflow
 * keeps the two in sync so existing seeders/tests that only set
 * `is_published` keep working unchanged.
 */
return new class extends Migration
{
    private const TABLES = ['service_offerings', 'systems', 'case_studies', 'industries', 'articles'];

    public function up(): void
    {
        foreach (self::TABLES as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('status', 20)->default('draft')->after('is_published');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->index('status');
            });

            // Backfill: anything already live is 'published', the rest 'draft'.
            DB::table($tableName)->where('is_published', true)->update(['status' => 'published']);
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('created_by');
                $table->dropConstrainedForeignId('updated_by');
                $table->dropConstrainedForeignId('approved_by');
                $table->dropConstrainedForeignId('published_by');
                $table->dropColumn(['status', 'approved_at']);
            });
        }
    }
};
