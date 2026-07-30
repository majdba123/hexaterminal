<?php

namespace App\Services\AiSeo;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Anthropic Messages API provider (server-to-server; the key never leaves the
 * backend). Real calls happen ONLY when ANTHROPIC_API_KEY is configured --
 * available() is false otherwise and AiSeoService reports the assistant as
 * disabled instead of faking success.
 *
 * Bounded by design: one short non-streaming request, 3-attempt retry on
 * transient failures (429/5xx/connection), configured timeout, sanitized
 * errors (status + category only -- never the provider response body).
 */
class AnthropicSeoProvider implements AiSeoProviderInterface
{
    private const ENDPOINT = 'https://api.anthropic.com/v1/messages';

    private const API_VERSION = '2023-06-01';

    private const MAX_TOKENS = 1024;

    public function name(): string
    {
        return 'anthropic';
    }

    public function model(): string
    {
        return (string) config('services.ai_seo.model', 'claude-opus-4-8');
    }

    public function available(): bool
    {
        return filled(config('services.ai_seo.anthropic_key'));
    }

    public function generate(string $systemPrompt, string $userPrompt): array
    {
        if (! $this->available()) {
            throw new AiSeoException('AI SEO provider is not configured.', 'disabled');
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => (string) config('services.ai_seo.anthropic_key'),
                'anthropic-version' => self::API_VERSION,
            ])
                ->timeout((int) config('services.ai_seo.timeout', 30))
                ->retry(2, 500, throw: false)
                ->post(self::ENDPOINT, [
                    'model' => $this->model(),
                    'max_tokens' => self::MAX_TOKENS,
                    'system' => $systemPrompt,
                    'messages' => [
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                ]);
        } catch (ConnectionException) {
            throw new AiSeoException('AI provider unreachable.', 'network');
        }

        if ($response->status() === 429) {
            throw new AiSeoException('AI provider rate limited the request.', 'rate_limited');
        }
        if (! $response->successful()) {
            throw new AiSeoException('AI provider returned HTTP '.$response->status().'.', 'http_'.$response->status());
        }

        if ($response->json('stop_reason') === 'refusal') {
            throw new AiSeoException('AI provider declined the request.', 'refusal');
        }

        $text = collect($response->json('content', []))
            ->where('type', 'text')
            ->pluck('text')
            ->implode("\n");

        if (trim($text) === '') {
            throw new AiSeoException('AI provider returned no text output.', 'empty_output');
        }

        return [
            'output' => trim($text),
            'input_tokens' => $response->json('usage.input_tokens'),
            'output_tokens' => $response->json('usage.output_tokens'),
        ];
    }
}
