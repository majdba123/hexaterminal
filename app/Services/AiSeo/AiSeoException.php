<?php

namespace App\Services\AiSeo;

use Exception;

/**
 * Sanitized AI-provider failure. `category` is a stable machine-readable
 * bucket persisted to ai_generations.error_category; the message never
 * contains provider payloads or credentials.
 */
class AiSeoException extends Exception
{
    public function __construct(
        string $message,
        public readonly string $category = 'provider_error',
    ) {
        parent::__construct($message);
    }
}
