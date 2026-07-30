<?php

namespace App\Services\AiSeo;

use App\Models\AiGeneration;
use App\Models\Article;
use App\Models\CaseStudy;
use App\Models\ContentActivity;
use App\Models\Industry;
use App\Models\Service;
use App\Models\System;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Human-reviewed AI SEO assistant. Every generation is stored as an
 * AiGeneration row with full provenance (provider, model, prompt version,
 * tokens, cost, latency, who asked) and moves through
 * pending -> generating -> generated -> approved|rejected|failed.
 *
 * Safety invariants (enforced here, not by convention):
 *  - NOTHING is ever auto-published. approve() only writes the reviewed text
 *    into the target's SeoMeta override fields (seo_title/meta_description);
 *    it never touches is_published/status on any content.
 *  - Advisory suggestion types (outlines, FAQs, internal links, social
 *    snippets, answer sections) are never applied automatically at all --
 *    approving them just records the editorial decision; a human copies
 *    what they want.
 *  - Prompts forbid fabrication (metrics, customers, testimonials, awards,
 *    pricing, history, capabilities); the editor remains responsible for
 *    factual review before approving.
 *  - Without credentials the service reports disabled -- no fake output.
 */
class AiSeoService
{
    public const PROMPT_VERSION = 'v1';

    /** Suggestion type => [system prompt id, user-prompt template]. */
    public const TYPES = [
        'seo_title', 'meta_description', 'page_summary', 'article_outline',
        'faq_candidates', 'internal_links', 'social_snippet', 'answer_section',
    ];

    /** Types whose approved output is applied to the target's SeoMeta. */
    private const APPLIABLE = ['seo_title', 'meta_description'];

    /**
     * Rough USD per-million-token pricing for cost estimation (input, output).
     * Estimation only -- invoices come from the provider.
     */
    private const PRICING_PER_MTOK = [
        'claude-opus-4-8' => [5.0, 25.0],
        'claude-sonnet-5' => [3.0, 15.0],
        'claude-haiku-4-5' => [1.0, 5.0],
    ];

    private const SYSTEM_PROMPT_ID = 'hexa-seo-assistant-v1';

    private const SYSTEM_PROMPT = <<<'PROMPT'
You are an SEO assistant for Hexa Terminal, a software company building SaaS platforms, CRM/ERP systems, backend infrastructure, and AI-assisted operational systems for clients in the GCC and beyond.

Rules you must never break:
- NEVER invent facts: no metrics, customer names, testimonials, awards, pricing, company history, or technical capabilities that are not in the provided content.
- Base every suggestion ONLY on the content provided in the request.
- Write for buyers of business software; precise and concrete, never generic agency language ("innovative solutions", "digital transformation", "turning ideas into reality").
- Respond with the suggestion text only -- no preamble, no commentary, no markdown fences.
- When asked for Arabic, write natural professional Arabic, not a literal translation.
PROMPT;

    public function __construct(private AiSeoProviderInterface $provider) {}

    public function enabled(): bool
    {
        return $this->provider->available();
    }

    /**
     * Generate one suggestion for a content record + locale + type, recording
     * full provenance. Returns the AiGeneration row in status generated|failed.
     */
    public function suggest(Model $target, string $type, string $locale, ?User $user = null): AiGeneration
    {
        if (! in_array($type, self::TYPES, true)) {
            throw new \InvalidArgumentException("Unknown AI SEO suggestion type [{$type}].");
        }

        $userPrompt = $this->buildUserPrompt($this->assertSupportedTarget($target), $type, $locale);

        $generation = AiGeneration::create([
            'provider' => $this->provider->name(),
            'model' => $this->provider->model(),
            'prompt_version' => self::PROMPT_VERSION,
            'system_prompt_id' => self::SYSTEM_PROMPT_ID,
            'target_type' => $target->getMorphClass(),
            'target_id' => (string) $target->getKey(),
            'field' => $type,
            'locale' => $locale,
            'input_hash' => hash('sha256', $userPrompt),
            'status' => AiGeneration::STATUS_GENERATING,
            'generated_by' => $user?->id,
        ]);

        $startedAt = microtime(true);

        try {
            $result = $this->provider->generate(self::SYSTEM_PROMPT, $userPrompt);

            $generation->update([
                'status' => AiGeneration::STATUS_GENERATED,
                'output' => $result['output'],
                'input_tokens' => $result['input_tokens'],
                'output_tokens' => $result['output_tokens'],
                'estimated_cost_usd' => $this->estimateCost($result['input_tokens'], $result['output_tokens']),
                'latency_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);
        } catch (AiSeoException $e) {
            $generation->update([
                'status' => AiGeneration::STATUS_FAILED,
                'failure_reason' => $e->getMessage(),
                'error_category' => $e->category,
                'latency_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);
        }

        return $generation->refresh();
    }

    /**
     * Human approval. The reviewer may have edited the output first (pass
     * $finalOutput). Applies ONLY SeoMeta title/description for appliable
     * types; advisory types are recorded as approved and left to the editor.
     * Never publishes anything.
     */
    public function approve(AiGeneration $generation, User $reviewer, ?string $finalOutput = null): void
    {
        $output = trim($finalOutput ?? (string) $generation->output);
        if ($output === '') {
            throw new \InvalidArgumentException('Cannot approve an empty AI output.');
        }

        $generation->update([
            'status' => AiGeneration::STATUS_APPROVED,
            'output' => $output,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        if (in_array($generation->field, self::APPLIABLE, true)) {
            $this->applyToSeoMeta($generation, $output);
        }

        ContentActivity::record($generation, 'ai_approved', [
            'type' => $generation->field,
            'locale' => $generation->locale,
            'target' => $generation->target_type.'#'.$generation->target_id,
        ]);
    }

    public function reject(AiGeneration $generation, User $reviewer, ?string $reason = null): void
    {
        $generation->update([
            'status' => AiGeneration::STATUS_REJECTED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'failure_reason' => $reason,
        ]);
    }

    private function applyToSeoMeta(AiGeneration $generation, string $output): void
    {
        $targetClass = $generation->target_type;
        $target = $targetClass::find($generation->target_id);
        if (! $target || ! method_exists($target, 'seo')) {
            return;
        }

        $seo = $target->seo()->firstOrNew([]);
        $column = $generation->field === 'seo_title' ? 'title' : 'description';
        $seo->setTranslation($column, $generation->locale ?? 'en', $output);
        $seo->save();
    }

    private function estimateCost(?int $inputTokens, ?int $outputTokens): ?float
    {
        $pricing = self::PRICING_PER_MTOK[$this->provider->model()] ?? null;
        if ($pricing === null || $inputTokens === null || $outputTokens === null) {
            return null;
        }

        return round(($inputTokens * $pricing[0] + $outputTokens * $pricing[1]) / 1_000_000, 4);
    }

    /**
     * Fail closed on any content type this assistant doesn't know how to
     * summarize -- narrows to the concrete union so the private helpers
     * below can rely on real column/translation access.
     */
    private function assertSupportedTarget(Model $target): Service|System|CaseStudy|Industry|Article
    {
        if ($target instanceof Service || $target instanceof System || $target instanceof CaseStudy
            || $target instanceof Industry || $target instanceof Article) {
            return $target;
        }

        throw new \InvalidArgumentException('Unsupported AI SEO target type: '.get_class($target));
    }

    /** Build the user prompt from REAL target content only. */
    private function buildUserPrompt(Service|System|CaseStudy|Industry|Article $target, string $type, string $locale): string
    {
        $language = $locale === 'ar' ? 'Arabic' : 'English';
        $context = $this->targetContext($target, $locale);

        $instruction = match ($type) {
            'seo_title' => "Write one SEO title (max 60 characters) in {$language} for this page.",
            'meta_description' => "Write one meta description (140-160 characters) in {$language} for this page.",
            'page_summary' => "Write a 2-3 sentence page summary in {$language}.",
            'article_outline' => "Suggest a practical article outline (H2/H3 headings) in {$language} based on this content.",
            'faq_candidates' => "Suggest 3-5 realistic buyer questions (with short answers drawn ONLY from this content) in {$language}.",
            'internal_links' => "Suggest which of this site's content types (services, systems, case studies, industries, articles) this page should link to and why, in {$language}. Do not invent URLs.",
            'social_snippet' => "Write one LinkedIn-style social post snippet (max 300 characters) in {$language} for this page.",
            'answer_section' => "Write a direct, extractable answer (2-4 sentences) in {$language} to the main buyer question this page addresses.",
            default => throw new \InvalidArgumentException("Unknown AI SEO suggestion type [{$type}]."),
        };

        return $instruction."\n\n--- PAGE CONTENT ---\n".$context;
    }

    private function targetContext(Service|System|CaseStudy|Industry|Article $target, string $locale): string
    {
        $fields = ['name', 'title', 'tagline', 'summary', 'short_description', 'excerpt', 'description', 'full_description', 'problem', 'solution', 'body'];
        $parts = [];

        foreach ($fields as $field) {
            if (! array_key_exists($field, $target->getAttributes())) {
                continue;
            }
            $value = $target->getTranslation($field, $locale, false);
            if (is_string($value) && trim($value) !== '') {
                $parts[] = strtoupper($field).":\n".mb_substr(strip_tags($value), 0, 2000);
            }
        }

        return $parts === [] ? '(no content provided)' : implode("\n\n", $parts);
    }
}
