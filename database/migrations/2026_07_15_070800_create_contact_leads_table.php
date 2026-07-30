<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Richer successor to the legacy `contact__us` table (kept as-is),
     * built for the "Start a Project" qualified-lead flow.
     */
    public function up(): void
    {
        Schema::create('contact_leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('country')->nullable();
            $table->string('project_type')->nullable();
            $table->string('system_type')->nullable();
            $table->string('budget_range')->nullable();
            $table->string('timeline')->nullable();
            $table->text('summary')->nullable();
            $table->text('pain_points')->nullable();
            $table->string('source_page')->nullable();
            $table->string('referrer')->nullable();
            $table->json('utm')->nullable();
            $table->string('locale', 5)->nullable();
            $table->enum('status', ['new', 'contacted', 'qualified', 'won', 'lost'])->default('new');
            $table->enum('priority', ['low', 'normal', 'high'])->default('normal');
            $table->text('notes')->nullable();
            // Traceability back to the legacy `contact__us` row this was migrated from, if any.
            $table->unsignedBigInteger('legacy_contact_id')->nullable()->unique();
            $table->timestamps();

            $table->index(['status', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_leads');
    }
};
