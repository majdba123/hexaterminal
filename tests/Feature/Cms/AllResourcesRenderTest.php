<?php

namespace Tests\Feature\Cms;

use App\Models\Article;
use App\Models\CaseStudy;
use App\Models\EngagementModel;
use App\Models\Industry;
use App\Models\PricingProfile;
use App\Models\Service;
use App\Models\System;
use App\Models\TeamMember;
use App\Models\TrustPage;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AllResourcesRenderTest extends TestCase
{
    use RefreshDatabase;

    private function makeCmsAdmin(): User
    {
        $this->seed(RolesSeeder::class);

        $user = User::create([
            'name' => 'CMS Admin',
            'email' => 'cms-admin@hexaterminal.test',
            'password' => bcrypt('a-long-secure-password'),
        ]);
        $user->assignRole('admin');

        return $user;
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function resourceListRoutes(): array
    {
        return [
            'services' => ['/cms/services'],
            'systems' => ['/cms/systems'],
            'case-studies' => ['/cms/case-studies'],
            'industries' => ['/cms/industries'],
            'articles' => ['/cms/articles'],
            'team-members' => ['/cms/team-members'],
            'testimonials' => ['/cms/testimonials'],
            'faq-items' => ['/cms/faq-items'],
            'contact-leads' => ['/cms/contact-leads'],
            'redirects' => ['/cms/redirects'],
            'ai-generations' => ['/cms/ai-generations'],
            'trust-pages' => ['/cms/trust-pages'],
            'public-claims' => ['/cms/public-claims'],
            // Added after an audit found six resources with no render
            // coverage at all: every one of these is registered in the
            // sidebar, so a fatal in any of them was a page a real editor
            // could reach while the suite stayed green.
            'article-categories' => ['/cms/article-categories'],
            'article-tags' => ['/cms/article-tags'],
            'engagement-models' => ['/cms/engagement-models'],
            'pricing-profiles' => ['/cms/pricing-profiles'],
            'estimator-versions' => ['/cms/estimator-versions'],
            'cost-estimates' => ['/cms/cost-estimates'],
        ];
    }

    #[DataProvider('resourceListRoutes')]
    public function test_resource_list_page_renders(string $path): void
    {
        $admin = $this->makeCmsAdmin();

        $this->actingAs($admin)->get($path)->assertOk();
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function resourceCreateRoutes(): array
    {
        return [
            'services' => ['/cms/services/create'],
            'systems' => ['/cms/systems/create'],
            'case-studies' => ['/cms/case-studies/create'],
            'industries' => ['/cms/industries/create'],
            'articles' => ['/cms/articles/create'],
            'team-members' => ['/cms/team-members/create'],
            'testimonials' => ['/cms/testimonials/create'],
            'faq-items' => ['/cms/faq-items/create'],
            'contact-leads' => ['/cms/contact-leads/create'],
            'redirects' => ['/cms/redirects/create'],
            'trust-pages' => ['/cms/trust-pages/create'],
            'public-claims' => ['/cms/public-claims/create'],
            'article-categories' => ['/cms/article-categories/create'],
            'article-tags' => ['/cms/article-tags/create'],
            'engagement-models' => ['/cms/engagement-models/create'],
            'pricing-profiles' => ['/cms/pricing-profiles/create'],
            'estimator-versions' => ['/cms/estimator-versions/create'],
        ];
    }

    #[DataProvider('resourceCreateRoutes')]
    public function test_resource_create_page_renders(string $path): void
    {
        $admin = $this->makeCmsAdmin();

        $this->actingAs($admin)->get($path)->assertOk();
    }

    public function test_ai_generation_create_page_does_not_exist(): void
    {
        $admin = $this->makeCmsAdmin();

        $this->actingAs($admin)->get('/cms/ai-generations/create')->assertNotFound();
    }

    public function test_dashboard_renders(): void
    {
        $admin = $this->makeCmsAdmin();

        $this->actingAs($admin)->get('/cms')->assertOk();
    }

    /**
     * Edit pages for every resource carrying the shared PreviewAction
     * (see App\Filament\Support\PreviewAction) must still render -- this is
     * where a wiring mistake (bad closure signature, missing config, etc.)
     * would surface, since the list/create tests above never hit
     * getHeaderActions() on the Edit page.
     */
    public function test_edit_pages_with_preview_action_render(): void
    {
        $admin = $this->makeCmsAdmin();

        $engagementModel = EngagementModel::create(['slug' => 'em', 'title' => ['en' => 'EM']]);

        $records = [
            'services' => Service::create(['slug' => 's1', 'name' => ['en' => 'S1']]),
            'systems' => System::create(['slug' => 'sy1', 'name' => ['en' => 'Sy1'], 'type' => 'saas_product']),
            'case-studies' => CaseStudy::create(['slug' => 'cs1', 'title' => ['en' => 'CS1']]),
            'industries' => Industry::create(['slug' => 'i1', 'name' => ['en' => 'I1']]),
            'articles' => Article::create(['slug' => 'a1', 'title' => ['en' => 'A1']]),
            'team-members' => TeamMember::create(['slug' => 't1', 'first_name' => 'T1']),
            'trust-pages' => TrustPage::create([
                'slug' => 'tp1', 'page_type' => 'process', 'title' => ['en' => 'TP1'],
            ]),
            'pricing-profiles' => PricingProfile::create([
                'priceable_type' => EngagementModel::class,
                'priceable_id' => $engagementModel->id,
                'currency' => 'USD',
                'min_amount' => 1000,
                'max_amount' => 2000,
            ]),
            'engagement-models' => $engagementModel,
        ];

        foreach ($records as $resource => $record) {
            $this->actingAs($admin)
                ->get("/cms/{$resource}/{$record->getKey()}/edit")
                ->assertOk();
        }
    }
}
