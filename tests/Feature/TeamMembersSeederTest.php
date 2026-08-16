<?php

namespace Tests\Feature;

use App\Models\CompanySetting;
use App\Models\Service;
use App\Models\TeamMember;
use App\Services\TeamMemberSeedImageSynchronizer;
use Database\Seeders\TeamMembersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeamMembersSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_one_real_published_founder_with_public_photo_idempotently(): void
    {
        Storage::fake('public');
        config(['app.public_media_url' => 'https://api.hexaterminal.test']);

        $this->seed(TeamMembersSeeder::class);

        $this->assertDatabaseCount('team_members', 1);

        $member = TeamMember::where('slug', 'majd-bayer')->firstOrFail();

        $this->assertSame('Majd', $member->first_name);
        $this->assertSame('Bayer', $member->last_name);
        $this->assertSame('Founder & Software Engineer', $member->getTranslation('position', 'en'));
        $this->assertSame('المؤسس ومهندس برمجيات', $member->getTranslation('position', 'ar'));
        $this->assertSame(
            'Portrait of Majd Bayer, Founder and Software Engineer at HexaTerminal.',
            $member->getTranslation('photo_alt', 'en'),
        );
        $this->assertSame(
            'صورة مجد باير، مؤسس HexaTerminal ومهندس برمجيات.',
            $member->getTranslation('photo_alt', 'ar'),
        );
        $this->assertSame('Backend-Focused Full Stack Software Engineering', $member->specialization);
        $this->assertSame('majdbayer77@gmail.com', $member->email);
        $this->assertSame('+963935027218', $member->phone);
        $this->assertSame('Damascus, Syria', $member->location);
        $this->assertSame('team/majd-bayer.jpg', $member->photo);
        $this->assertSame(['Arabic', 'English'], $member->languages);
        $this->assertContains('Custom ERP & CRM Systems', $member->expertise);
        $this->assertSame('https://github.com/majdba123', $member->github_url);
        $this->assertNull($member->linkedin_url);
        $this->assertNull($member->cv_file);
        $this->assertTrue($member->is_published);
        $this->assertTrue($member->publication_consent);
        $this->assertTrue($member->is_founder);
        $this->assertTrue($member->seo_eligible);
        $this->assertTrue($member->person_jsonld_eligible);
        $this->assertTrue($member->isPersonJsonLdEligible());
        $this->assertSame(1, $member->sort_order);

        Storage::disk('public')->assertExists('team/majd-bayer.jpg');

        $this->getJson('/api/v1/public/team')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'majd-bayer')
            ->assertJsonPath('data.0.photo', 'https://api.hexaterminal.test/storage/team/majd-bayer.jpg')
            ->assertJsonPath('data.0.person_jsonld_eligible', true)
            ->assertJsonMissingPath('data.0.email')
            ->assertJsonMissingPath('data.0.phone');

        $this->getJson('/api/v1/public/team/majd-bayer')
            ->assertOk()
            ->assertJsonPath('data.photo', 'https://api.hexaterminal.test/storage/team/majd-bayer.jpg')
            ->assertJsonPath('data.github_url', 'https://github.com/majdba123')
            ->assertJsonPath('data.linkedin_url', null);

        $this->seed(TeamMembersSeeder::class);

        $this->assertDatabaseCount('team_members', 1);
        $this->assertCount(1, Storage::disk('public')->allFiles('team'));
    }

    public function test_fresh_seed_keeps_services_and_company_settings_and_adds_only_one_team_member(): void
    {
        config([
            'app.admin_email' => 'bootstrap-admin@hexaterminal.test',
            'app.admin_password' => 'a-long-secure-password',
        ]);

        Storage::fake('public');

        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->assertDatabaseCount('service_offerings', 3);
        $this->assertDatabaseCount('company_settings', 1);
        $this->assertDatabaseCount('team_members', 1);
        $this->assertDatabaseCount('systems', 1);
        $this->assertDatabaseCount('industries', 0);
        $this->assertDatabaseCount('case_studies', 0);
        $this->assertDatabaseCount('testimonials', 0);
        $this->assertSame(Service::CORE_SERVICE_SLUGS, Service::query()->orderBy('sort_order')->pluck('slug')->all());
        $this->assertSame('majdbayer77@gmail.com', CompanySetting::current()->email);
        Storage::disk('public')->assertExists('team/majd-bayer.jpg');
    }

    public function test_photo_sync_does_not_duplicate_files_when_rerun_without_creating_rows(): void
    {
        Storage::fake('public');

        app(TeamMemberSeedImageSynchronizer::class)->sync('images/majd-bayer.jpg', 'team/majd-bayer.jpg');
        app(TeamMemberSeedImageSynchronizer::class)->sync('images/majd-bayer.jpg', 'team/majd-bayer.jpg');

        $this->assertDatabaseCount('team_members', 0);
        $this->assertSame(['team/majd-bayer.jpg'], Storage::disk('public')->allFiles('team'));
    }
}
