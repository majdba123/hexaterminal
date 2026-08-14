<?php

namespace Tests\Feature;

use App\Models\CaseStudy;
use Database\Seeders\WebsitePreviewSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsitePreviewProofReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_preview_erp_and_platform_case_studies_exercise_the_business_process_model_without_client_claims(): void
    {
        app(WebsitePreviewSeeder::class)->run();

        $erp = CaseStudy::where('project_classification', CaseStudy::CLASSIFICATION_CUSTOM_ERP_CRM)->firstOrFail();
        $platform = CaseStudy::where('project_classification', CaseStudy::CLASSIFICATION_WEB_MOBILE_PLATFORM)->firstOrFail();

        foreach ([$erp, $platform] as $caseStudy) {
            $this->assertNotNull($caseStudy->context);
            $this->assertNotNull($caseStudy->problem);
            $this->assertNotNull($caseStudy->constraints);
            $this->assertNotNull($caseStudy->solution);
            $this->assertNotNull($caseStudy->architecture);
            $this->assertNotNull($caseStudy->features);
            $this->assertNotNull($caseStudy->outcomes);
            $this->assertNotNull($caseStudy->service_offering_id);
            $this->assertNotNull($caseStudy->system_id);
            $this->assertNull($caseStudy->client_name);
            $this->assertNull($caseStudy->project_url);
            $this->assertStringContainsString('Preview', $caseStudy->getTranslation('outcomes', 'en'));
        }

        $this->assertSame('custom-erp-crm-systems', $erp->serviceOffering?->slug);
        $this->assertSame('web-platforms-mobile-applications', $platform->serviceOffering?->slug);
    }
}
