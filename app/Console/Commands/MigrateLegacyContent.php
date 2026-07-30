<?php

namespace App\Console\Commands;

use App\Models\CaseStudy;
use App\Models\Contact_Us;
use App\Models\ContactLead;
use App\Models\Projects;
use App\Models\Redirect;
use App\Models\Review;
use App\Models\Service;
use App\Models\Services;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * One-way, idempotent copy from the legacy portfolio tables into the
 * new content model. Legacy tables and models are never modified or
 * deleted by this command -- they keep serving the old Blade frontend
 * until cutover (see docs/migration/legacy-to-nextjs.md).
 *
 * Idempotent via unique legacy_*_id columns / slug lookups: running
 * this command multiple times updates existing migrated rows instead
 * of duplicating them.
 */
class MigrateLegacyContent extends Command
{
    protected $signature = 'hexa:migrate-legacy-content {--dry-run : Report what would change without writing anything}';

    protected $description = 'Copy legacy Services/Team/Review/Contact_Us/Projects data into the new content model tables';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->migrateServices($dryRun);
        $this->migrateTeam($dryRun);
        $this->migrateTestimonials($dryRun);
        $this->migrateContactLeads($dryRun);
        $this->migrateCaseStudies($dryRun);
        $this->migrateStaticRedirects($dryRun);

        $this->info($dryRun ? 'Dry run complete -- nothing written.' : 'Legacy content migration complete.');

        return self::SUCCESS;
    }

    private function migrateServices(bool $dryRun): void
    {
        $legacyServices = Services::all();
        $this->line("Services: {$legacyServices->count()} legacy rows found.");

        if ($dryRun) {
            return;
        }

        DB::transaction(function () use ($legacyServices) {
            foreach ($legacyServices as $legacy) {
                $slug = Str::slug($legacy->title);

                Service::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => ['en' => $legacy->title],
                        'description' => $legacy->description ? ['en' => $legacy->description] : null,
                        'cover_image' => $legacy->image_path,
                        'is_published' => true,
                        'published_at' => $legacy->created_at,
                    ]
                );

                // Legacy public route: routes/web.php Route::get('/service/{id}', ...).
                $this->upsertRedirect("/service/{$legacy->id}", "/en/services/{$slug}");
            }
        });
    }

    private function migrateTeam(bool $dryRun): void
    {
        $legacyTeam = Team::all();
        $this->line("Team: {$legacyTeam->count()} legacy rows found.");

        if ($dryRun) {
            return;
        }

        DB::transaction(function () use ($legacyTeam) {
            foreach ($legacyTeam as $legacy) {
                $slugSource = trim($legacy->first_name.' '.$legacy->last_name) ?: "team-{$legacy->id}";

                TeamMember::updateOrCreate(
                    ['slug' => Str::slug($slugSource)],
                    [
                        'first_name' => $legacy->first_name,
                        'last_name' => $legacy->last_name,
                        'position' => $legacy->position ? ['en' => $legacy->position] : null,
                        'specialization' => $legacy->specialization,
                        'email' => $legacy->email,
                        'phone' => $legacy->phone,
                        'photo' => $legacy->photo,
                        'github_url' => $legacy->github_url,
                        'cv_file' => $legacy->cv_file,
                        'is_published' => true,
                    ]
                );

                // Legacy public route: routes/web.php Route::get('/team/{id}', ...).
                // No individual team member page exists in the new site (Phase 5
                // scope was a single /about team section), so this points at the
                // closest real destination rather than fabricating a detail page.
                $this->upsertRedirect("/team/{$legacy->id}", '/en/about');
            }
        });
    }

    private function migrateTestimonials(bool $dryRun): void
    {
        $legacyReviews = Review::all();
        $this->line("Reviews: {$legacyReviews->count()} legacy rows found.");

        if ($dryRun) {
            return;
        }

        DB::transaction(function () use ($legacyReviews) {
            foreach ($legacyReviews as $legacy) {
                Testimonial::updateOrCreate(
                    ['legacy_review_id' => $legacy->id],
                    [
                        'author_name' => $legacy->name,
                        'content' => ['en' => $legacy->content],
                        'rating' => $legacy->rating,
                        'given_at' => $legacy->review_date,
                        // Preserve the existing moderation decision exactly.
                        'is_approved' => (bool) $legacy->is_approved,
                    ]
                );
            }
        });
    }

    private function migrateContactLeads(bool $dryRun): void
    {
        $legacyContacts = Contact_Us::all();
        $this->line("Contact messages: {$legacyContacts->count()} legacy rows found.");

        if ($dryRun) {
            return;
        }

        $statusMap = [
            'pending' => ContactLead::STATUS_NEW,
            'in_progress' => ContactLead::STATUS_CONTACTED,
            'completed' => ContactLead::STATUS_QUALIFIED,
        ];

        DB::transaction(function () use ($legacyContacts, $statusMap) {
            foreach ($legacyContacts as $legacy) {
                ContactLead::updateOrCreate(
                    ['legacy_contact_id' => $legacy->id],
                    [
                        'name' => $legacy->name,
                        'email' => $legacy->email,
                        'phone' => $legacy->phone,
                        'summary' => trim(($legacy->subject ? $legacy->subject.': ' : '').$legacy->message),
                        // contact__us.status is a DB enum('pending','in_progress','completed'),
                        // so this lookup is always exhaustive -- no fallback needed.
                        'status' => $statusMap[$legacy->status],
                    ]
                );
            }
        });
    }

    private function migrateCaseStudies(bool $dryRun): void
    {
        $legacyProjects = Projects::with(['images', 'features', 'service'])->get();
        $this->line("Projects: {$legacyProjects->count()} legacy rows found.");

        if ($dryRun) {
            return;
        }

        DB::transaction(function () use ($legacyProjects) {
            foreach ($legacyProjects as $legacy) {
                $serviceOffering = $legacy->service
                    ? Service::where('slug', Str::slug($legacy->service->title))->first()
                    : null;

                $images = $legacy->images->pluck('image_path')->filter()->values()->all();
                $featureTexts = $legacy->features->pluck('feature_text')->filter()->values()->all();

                $caseStudy = CaseStudy::updateOrCreate(
                    ['legacy_project_id' => $legacy->id],
                    [
                        'title' => ['en' => $legacy->title],
                        'context' => $legacy->description ? ['en' => $legacy->description] : null,
                        'features' => $featureTexts ? ['en' => $featureTexts] : null,
                        'project_url' => $legacy->project_url,
                        'video_url' => $legacy->video_url,
                        'cover_image' => $images[0] ?? null,
                        'gallery' => $images ?: null,
                        'service_offering_id' => $serviceOffering?->id,
                        'is_published' => true,
                        'published_at' => $legacy->created_at,
                    ]
                );

                // Legacy public route: routes/web.php Route::get('/project/{id}', ...).
                $this->upsertRedirect("/project/{$legacy->id}", "/en/case-studies/{$caseStudy->slug}");
            }
        });
    }

    private function migrateStaticRedirects(bool $dryRun): void
    {
        // Legacy public routes with no per-row ID: routes/web.php
        // Route::get('/projects', ...) -- the list page, not a detail page.
        $staticMap = [
            '/projects' => '/en/case-studies',
        ];

        $this->line('Static redirects: '.count($staticMap).' mapped.');

        if ($dryRun) {
            return;
        }

        foreach ($staticMap as $from => $to) {
            $this->upsertRedirect($from, $to);
        }
    }

    private function upsertRedirect(string $from, string $to): void
    {
        Redirect::updateOrCreate(
            ['from_path' => $from],
            ['to_path' => $to, 'status_code' => 301, 'is_active' => true]
        );
    }
}
