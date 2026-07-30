<?php

namespace Tests\Feature\Content;

use App\Models\CaseStudy;
use App\Models\Contact_Us;
use App\Models\ContactLead;
use App\Models\Fetures_Project;
use App\Models\Imag_Progect;
use App\Models\Projects;
use App\Models\Review;
use App\Models\Service;
use App\Models\Services;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MigrateLegacyContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_migrates_services_team_reviews_contacts_and_projects(): void
    {
        $legacyService = Services::create([
            'title' => 'Custom ERP Systems',
            'description' => 'We build ERP systems.',
            'image_path' => 'services/erp.png',
        ]);

        Team::create([
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'position' => 'Lead Engineer',
            'specialization' => 'Distributed Systems',
            'email' => 'ada@example.com',
        ]);

        Review::create([
            'name' => 'Happy Client',
            'content' => 'Great work.',
            'rating' => 5,
            'is_approved' => true,
        ]);

        Contact_Us::create([
            'name' => 'Prospect',
            'email' => 'prospect@example.com',
            'phone' => '123',
            'subject' => 'Project inquiry',
            'message' => 'I need a CRM.',
            'status' => 'pending',
        ]);

        $project = Projects::create([
            'title' => 'Acme CRM Rollout',
            'description' => 'Built a CRM for Acme.',
            'service_id' => $legacyService->id,
        ]);
        Imag_Progect::create(['project_id' => $project->id, 'image_path' => 'projects/acme-1.png', 'order' => 1]);
        Fetures_Project::create(['project_id' => $project->id, 'feature_text' => 'Lead pipeline']);

        $this->artisan('hexa:migrate-legacy-content')->assertSuccessful();

        // Legacy tables untouched.
        $this->assertDatabaseCount('services', 1);
        $this->assertDatabaseCount('projects', 1);

        // New tables populated.
        $service = Service::first();
        $this->assertNotNull($service);
        $this->assertSame('Custom ERP Systems', $service->getTranslation('name', 'en'));
        $this->assertTrue($service->is_published);

        $teamMember = TeamMember::first();
        $this->assertSame('Ada', $teamMember->first_name);
        $this->assertSame('Lead Engineer', $teamMember->getTranslation('position', 'en'));

        $testimonial = Testimonial::first();
        $this->assertSame('Happy Client', $testimonial->author_name);
        $this->assertTrue($testimonial->is_approved);

        $lead = ContactLead::first();
        $this->assertSame('Prospect', $lead->name);
        $this->assertSame(ContactLead::STATUS_NEW, $lead->status);
        $this->assertStringContainsString('CRM', $lead->summary);

        $caseStudy = CaseStudy::first();
        $this->assertSame('Acme CRM Rollout', $caseStudy->getTranslation('title', 'en'));
        $this->assertSame($service->id, $caseStudy->service_offering_id);
        $this->assertSame(['projects/acme-1.png'], $caseStudy->gallery);
        $this->assertSame(['Lead pipeline'], $caseStudy->getTranslation('features', 'en'));
    }

    public function test_running_it_twice_does_not_duplicate_rows(): void
    {
        Services::create(['title' => 'Backend Engineering', 'description' => 'APIs.', 'image_path' => 'services/backend.png']);
        Review::create(['name' => 'A', 'content' => 'B', 'rating' => 4, 'is_approved' => false]);
        Contact_Us::create(['name' => 'X', 'email' => 'x@example.com', 'phone' => '1', 'subject' => 'S', 'message' => 'M']);

        $this->artisan('hexa:migrate-legacy-content')->assertSuccessful();
        $this->artisan('hexa:migrate-legacy-content')->assertSuccessful();

        $this->assertDatabaseCount('service_offerings', 1);
        $this->assertDatabaseCount('testimonials', 1);
        $this->assertDatabaseCount('contact_leads', 1);
    }

    public function test_dry_run_writes_nothing(): void
    {
        Services::create(['title' => 'Dry Run Service', 'description' => 'x', 'image_path' => 'services/dry.png']);

        $this->artisan('hexa:migrate-legacy-content', ['--dry-run' => true])->assertSuccessful();

        $this->assertDatabaseCount('service_offerings', 0);
    }
}
