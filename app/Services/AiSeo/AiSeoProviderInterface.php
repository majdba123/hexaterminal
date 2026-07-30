<?php

namespace App\Services\AiSeo;

/**
 * Bounded provider contract for the AI SEO assistant. Implementations run a
 * single prompt and return the raw text + usage; everything else (provenance,
 * review workflow, applying approved output) lives in AiSeoService.
 */
interface AiSeoProviderInterface
{
    public function name(): string;

    public function model(): string;

    /** True only when the provider is fully configured (credentials present). */
    public function available(): bool;

    /**
     * Run one generation.
     *
     * @return array{output: string, input_tokens: int|null, output_tokens: int|null}
     *
     * @throws AiSeoException on any provider failure (sanitized message)
     */
    public function generate(string $systemPrompt, string $userPrompt): array;
}
