<?php

namespace Tests\Feature\AiSeo;

use App\Models\AiGeneration;
use App\Models\Service;
use App\Models\User;
use App\Services\AiSeo\AiSeoException;
use App\Services\AiSeo\AiSeoProviderInterface;
use App\Services\AiSeo\AiSeoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Deterministic stand-in for the real Anthropic provider. */
class FakeAiSeoProvider implements AiSeoProviderInterface
{
    public bool $isAvailable = true;

    public ?AiSeoException $throws = null;

    public string $output = 'A generated SEO title';

    public function name(): string
    {
        return 'fake';
    }

    public function model(): string
    {
        return 'fake-model';
    }

    public function available(): bool
    {
        return $this->isAvailable;
    }

    public function generate(string $systemPrompt, string $userPrompt): array
    {
        if ($this->throws) {
            throw $this->throws;
        }

        return ['output' => $this->output, 'input_tokens' => 100, 'output_tokens' => 20];
    }
}

class AiSeoServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(FakeAiSeoProvider $provider): AiSeoService
    {
        return new AiSeoService($provider);
    }

    public function test_reports_disabled_when_provider_unavailable(): void
    {
        $provider = new FakeAiSeoProvider;
        $provider->isAvailable = false;

        $this->assertFalse($this->service($provider)->enabled());
    }

    public function test_successful_generation_records_full_provenance(): void
    {
        $provider = new FakeAiSeoProvider;
        $target = Service::create(['slug' => 'crm', 'name' => ['en' => 'CRM'], 'summary' => ['en' => 'Manage customers'], 'is_published' => true]);
        $user = User::factory()->create();

        $generation = $this->service($provider)->suggest($target, 'seo_title', 'en', $user);

        $this->assertSame(AiGeneration::STATUS_GENERATED, $generation->status);
        $this->assertSame('A generated SEO title', $generation->output);
        $this->assertSame('fake', $generation->provider);
        $this->assertSame(100, $generation->input_tokens);
        $this->assertSame(20, $generation->output_tokens);
        $this->assertSame($user->id, $generation->generated_by);
        $this->assertNotNull($generation->latency_ms);
    }

    public function test_provider_failure_is_recorded_as_failed_not_thrown(): void
    {
        $provider = new FakeAiSeoProvider;
        $provider->throws = new AiSeoException('boom', 'rate_limited');
        $target = Service::create(['slug' => 'crm', 'name' => ['en' => 'CRM'], 'is_published' => true]);

        $generation = $this->service($provider)->suggest($target, 'seo_title', 'en');

        $this->assertSame(AiGeneration::STATUS_FAILED, $generation->status);
        $this->assertSame('rate_limited', $generation->error_category);
        $this->assertSame('boom', $generation->failure_reason);
    }

    public function test_approving_seo_title_writes_to_seo_meta_but_never_publishes(): void
    {
        $provider = new FakeAiSeoProvider;
        $target = Service::create(['slug' => 'crm', 'name' => ['en' => 'CRM'], 'is_published' => false]);
        $reviewer = User::factory()->create();

        $generation = $this->service($provider)->suggest($target, 'seo_title', 'en');
        $this->service($provider)->approve($generation, $reviewer);

        $this->assertSame(AiGeneration::STATUS_APPROVED, $generation->fresh()->status);
        $this->assertSame($reviewer->id, $generation->fresh()->reviewed_by);
        $this->assertSame('A generated SEO title', $target->fresh()->seo->getTranslation('title', 'en'));
        // Approving an AI SEO suggestion must never publish the underlying content.
        $this->assertFalse($target->fresh()->is_published);
    }

    public function test_advisory_suggestion_types_are_never_applied_to_content(): void
    {
        $provider = new FakeAiSeoProvider;
        $provider->output = 'Suggested internal links: ...';
        $target = Service::create(['slug' => 'crm', 'name' => ['en' => 'CRM'], 'is_published' => true]);
        $reviewer = User::factory()->create();

        $generation = $this->service($provider)->suggest($target, 'internal_links', 'en');
        $this->service($provider)->approve($generation, $reviewer);

        // Approved, but nothing on the target model changed -- it's advisory only.
        $this->assertSame(AiGeneration::STATUS_APPROVED, $generation->fresh()->status);
        $this->assertNull($target->fresh()->seo);
    }

    public function test_rejecting_a_suggestion_records_the_reviewer(): void
    {
        $provider = new FakeAiSeoProvider;
        $target = Service::create(['slug' => 'crm', 'name' => ['en' => 'CRM'], 'is_published' => true]);
        $reviewer = User::factory()->create();

        $generation = $this->service($provider)->suggest($target, 'seo_title', 'en');
        $this->service($provider)->reject($generation, $reviewer, 'Not accurate enough');

        $this->assertSame(AiGeneration::STATUS_REJECTED, $generation->fresh()->status);
        $this->assertSame($reviewer->id, $generation->fresh()->reviewed_by);
        $this->assertSame('Not accurate enough', $generation->fresh()->failure_reason);
    }

    public function test_unknown_suggestion_type_is_rejected(): void
    {
        $provider = new FakeAiSeoProvider;
        $target = Service::create(['slug' => 'crm', 'name' => ['en' => 'CRM'], 'is_published' => true]);

        $this->expectException(\InvalidArgumentException::class);
        $this->service($provider)->suggest($target, 'not_a_real_type', 'en');
    }
}
