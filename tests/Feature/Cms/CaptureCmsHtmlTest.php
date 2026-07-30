<?php

namespace Tests\Feature\Cms;

use App\Models\User;
use Database\Seeders\DemoContentSeeder;
use Database\Seeders\FounderContentSeeder;
use Database\Seeders\PricingEstimatorFixtureSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Dumps the rendered HTML of every CMS screen to docs/cms-screens/html/.
 *
 * This is a capture tool, not an assertion suite, so it is SKIPPED unless
 * CAPTURE_CMS=1 is set -- it must never slow down or fail a normal run.
 *
 *   CAPTURE_CMS=1 php artisan test --filter=CaptureCmsHtmlTest
 *
 * Why render server-side instead of driving a browser: the panel requires a
 * password and a TOTP code to sign in, and those have to be typed by a person.
 * Laravel's `actingAs()` authenticates inside the test process without any
 * credential existing -- the same mechanism AllResourcesRenderTest already
 * uses to prove these pages return 200. The throwaway admin below lives only
 * inside RefreshDatabase's transaction, so the real dev database is untouched
 * and no account is left behind.
 *
 * The demo seeders run first so the tables and forms are captured with content
 * in them rather than as empty states.
 *
 * frontend/e2e/tools/screenshot-cms-html.ts turns these files into PNGs.
 */
class CaptureCmsHtmlTest extends TestCase
{
    use RefreshDatabase;

    private const OUT = __DIR__.'/../../../docs/cms-screens/html';

    /** Where Filament builds asset URLs from, vs where the app is actually served. */
    private const APP_URL_IN_HTML = 'http://localhost:8000';

    protected function setUp(): void
    {
        parent::setUp();

        if (env('CAPTURE_CMS') !== '1') {
            $this->markTestSkipped('Set CAPTURE_CMS=1 to run the CMS capture tool.');
        }
    }

    /**
     * @return list<string>
     */
    private function routes(): array
    {
        $resources = [
            'services', 'systems', 'industries',
            'case-studies', 'testimonials',
            'articles', 'article-categories', 'article-tags', 'team-members', 'faq-items',
            'engagement-models', 'pricing-profiles', 'estimator-versions',
            'contact-leads', 'cost-estimates',
            'redirects', 'ai-generations',
            'trust-pages', 'public-claims',
        ];

        $routes = ['/cms', '/cms/company-settings'];

        // Deliberately have no create page: cost estimates are produced by the
        // public estimator, and AI generations are produced by the pipeline --
        // AllResourcesRenderTest asserts the latter's absence on purpose.
        $noCreatePage = ['cost-estimates', 'ai-generations'];

        foreach ($resources as $r) {
            $routes[] = "/cms/{$r}";

            if (! in_array($r, $noCreatePage, true)) {
                $routes[] = "/cms/{$r}/create";
            }
        }

        return $routes;
    }

    public function test_capture_every_cms_screen(): void
    {
        $this->seed(RolesSeeder::class);
        // DemoContentSeeder covers Article/ArticleCategory/CaseStudy/Industry/
        // System. FounderContentSeeder is what supplies Service, Testimonial,
        // FaqItem, SeoMeta and CompanySetting -- without it the Services,
        // Testimonials and FAQ screens captured as empty tables, which looked
        // like a seeding gap in the project and was really a gap here.
        $this->seed(DemoContentSeeder::class);
        $this->seed(FounderContentSeeder::class);
        $this->seed(PricingEstimatorFixtureSeeder::class);

        $admin = User::create([
            'name' => 'Capture Bot',
            'email' => 'capture@hexaterminal.test',
            'password' => bcrypt(bin2hex(random_bytes(24))),
        ]);
        $admin->assignRole('admin');

        if (! is_dir(self::OUT)) {
            mkdir(self::OUT, 0o777, true);
        }
        array_map('unlink', glob(self::OUT.'/*.html') ?: []);

        $servedFrom = rtrim(env('CAPTURE_CMS_URL', 'http://127.0.0.1:8010'), '/');
        $captured = [];
        $failed = [];

        foreach ($this->routes() as $i => $route) {
            $response = $this->actingAs($admin)->get($route);

            if ($response->status() !== 200) {
                $failed[$route] = $response->status();

                continue;
            }

            $name = sprintf('%02d-%s', $i, trim(str_replace('/', '_', substr($route, 4)), '_') ?: 'dashboard');

            // Filament emits absolute asset URLs built from APP_URL. The app is
            // being served somewhere else here, so point them at the live
            // origin -- otherwise the screenshots come out unstyled.
            $html = str_replace(self::APP_URL_IN_HTML, $servedFrom, $response->getContent());

            file_put_contents(self::OUT."/{$name}.html", $html);
            $captured[$name] = $route;
        }

        file_put_contents(
            self::OUT.'/index.json',
            json_encode(['servedFrom' => $servedFrom, 'screens' => $captured], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n"
        );

        $this->assertSame([], $failed, 'Some CMS routes did not return 200: '.json_encode($failed));
        $this->assertGreaterThan(35, count($captured), 'Expected the full panel to be captured.');
    }
}
