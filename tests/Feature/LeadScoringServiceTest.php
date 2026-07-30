<?php

namespace Tests\Feature;

use App\Models\ContactLead;
use App\Services\LeadScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadScoringServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_richer_lead_scores_higher_than_a_bare_one(): void
    {
        $service = app(LeadScoringService::class);

        $bare = new ContactLead(['intent' => 'general_contact', 'name' => 'A', 'email' => 'a@example.com']);
        $rich = new ContactLead([
            'intent' => 'start_project', 'name' => 'B', 'email' => 'b@example.com',
            'company' => 'Acme', 'company_size' => '50-200', 'budget_range' => '$50k+',
            'timeline' => 'ASAP', 'phone' => '+1', 'consent' => true,
            'summary' => str_repeat('Detailed requirements. ', 20),
        ]);

        $bareResult = $service->score($bare);
        $richResult = $service->score($rich);

        $this->assertGreaterThan($bareResult['score'], $richResult['score']);
        $this->assertSame('low', $bareResult['priority']);
        $this->assertSame('high', $richResult['priority']);
    }

    public function test_score_never_exceeds_the_documented_bounds(): void
    {
        $service = app(LeadScoringService::class);
        $lead = new ContactLead([
            'intent' => 'start_project', 'company' => 'Acme', 'company_size' => 'big',
            'budget_range' => '$1M', 'timeline' => 'now', 'phone' => '1', 'consent' => true,
            'summary' => str_repeat('word ', 200),
        ]);

        $result = $service->score($lead);

        $this->assertGreaterThanOrEqual(0, $result['score']);
        $this->assertLessThanOrEqual(100, $result['score']);
    }

    public function test_link_heavy_summary_is_penalized_not_rejected(): void
    {
        $service = app(LeadScoringService::class);
        $lead = new ContactLead([
            'intent' => 'general_contact',
            'summary' => 'Check http://a.com http://b.com http://c.com http://d.com',
        ]);

        $result = $service->score($lead);

        $this->assertContains('link_heavy_summary', array_column($result['breakdown'], 'factor'));
        $this->assertGreaterThanOrEqual(0, $result['score']); // never negative, never auto-rejects
    }
}
