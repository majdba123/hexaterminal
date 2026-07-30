<?php

namespace Tests\Feature\Security;

use App\Filament\Support\Slugs;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Slugs are interpolated into generated sitemap XML that has no escaping of
 * its own (Next concatenates `<loc>${url}</loc>` directly), and into public
 * URLs and canonical/alternate link targets. `Str::slug()` on the title is
 * only a create-time default -- the field stays editable -- so the format has
 * to be validated. See App\Filament\Support\Slugs.
 */
class SlugValidationTest extends TestCase
{
    private function passes(?string $slug): bool
    {
        $rules = Slugs::input()->getValidationRules();

        return Validator::make(['slug' => $slug], ['slug' => $rules])->passes();
    }

    /** @return array<string, array{string}> */
    public static function validSlugs(): array
    {
        return [
            'simple' => ['crm-platform'],
            'single word' => ['services'],
            'digits' => ['erp-2024'],
            'numeric only' => ['404'],
            'many segments' => ['a-b-c-d-e'],
        ];
    }

    /** @return array<string, array{string}> */
    public static function rejectedSlugs(): array
    {
        return [
            // The actual attack: breaks out of <loc> and appends attacker entries.
            'sitemap xml injection' => ['a</loc></url><url><loc>https://attacker.tld/phish'],
            'angle brackets' => ['a<b'],
            'uppercase' => ['CRM-Platform'],
            'spaces' => ['crm platform'],
            'leading hyphen' => ['-crm'],
            'trailing hyphen' => ['crm-'],
            'doubled hyphen' => ['crm--platform'],
            'slash' => ['crm/platform'],
            'path traversal' => ['../../etc/passwd'],
            'quote' => ['crm"platform'],
            'ampersand' => ['crm&platform'],
            'percent encoded' => ['crm%2Fplatform'],
            'underscore' => ['crm_platform'],
            'dot' => ['crm.platform'],
        ];
    }

    #[DataProvider('validSlugs')]
    public function test_accepts_well_formed_slugs(string $slug): void
    {
        $this->assertTrue($this->passes($slug), "expected [{$slug}] to be accepted");
    }

    #[DataProvider('rejectedSlugs')]
    public function test_rejects_malformed_slugs(string $slug): void
    {
        $this->assertFalse($this->passes($slug), "expected [{$slug}] to be rejected");
    }

    /**
     * Some resources (e.g. Engagement Models) auto-generate the slug from the
     * title when left blank, so the rule must skip empty values rather than
     * reject them. An empty slug never reaches the sitemap.
     */
    public function test_allows_blank_so_auto_generation_still_works(): void
    {
        $this->assertTrue($this->passes(null));
        $this->assertTrue($this->passes(''));
    }
}
