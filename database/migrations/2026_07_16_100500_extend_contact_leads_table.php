<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Turns the single "Start a Project" intake into the unified lead system:
 * intent-typed forms, richer qualification fields, marketing attribution,
 * lead operations (assignment/follow-up), and a transparent deterministic
 * score. `status` widens from the original 5-value enum to a string column
 * so the pipeline can gain reviewing/discovery_scheduled/proposal/spam/
 * archived without another schema rebuild.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_leads', function (Blueprint $table) {
            $table->string('status', 30)->default('new')->change();

            $table->string('intent', 40)->default('start_project')->after('id');
            $table->string('whatsapp', 30)->nullable()->after('phone');
            $table->string('company_size', 50)->nullable()->after('company');
            $table->string('role_title')->nullable()->after('company_size');
            $table->string('industry', 100)->nullable()->after('project_type');
            $table->string('preferred_contact_method', 20)->nullable();
            $table->boolean('consent')->default(false);
            $table->string('landing_page', 500)->nullable()->after('source_page');
            $table->timestamp('first_touch_at')->nullable();
            $table->foreignId('requested_service_id')->nullable()->constrained('service_offerings')->nullOnDelete();
            $table->foreignId('requested_system_id')->nullable()->constrained('systems')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('follow_up_at')->nullable();
            $table->text('qualification_summary')->nullable();
            $table->unsignedTinyInteger('score')->nullable();
            $table->json('score_breakdown')->nullable();

            $table->index('intent');
            $table->index('follow_up_at');
        });
    }

    public function down(): void
    {
        Schema::table('contact_leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('requested_service_id');
            $table->dropConstrainedForeignId('requested_system_id');
            $table->dropConstrainedForeignId('assigned_to');
            $table->dropColumn([
                'intent', 'whatsapp', 'company_size', 'role_title', 'industry',
                'preferred_contact_method', 'consent', 'landing_page',
                'first_touch_at', 'follow_up_at', 'qualification_summary',
                'score', 'score_breakdown',
            ]);
        });
    }
};
