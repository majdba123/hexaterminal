<?php

namespace App\Observers;

class TestimonialObserver extends ClearsPublicApiCache
{
    protected function resourceName(): string
    {
        return 'testimonials';
    }

    protected function extraListSuffixes(): array
    {
        return ['featured:0', 'featured:1'];
    }
}
