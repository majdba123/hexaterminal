<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Deterministic production security-readiness gate:
 *
 *   php artisan hexa:security-readiness           table to the console
 *   php artisan hexa:security-readiness --json     machine-readable
 *
 * Inspects configuration only -- never prints secret VALUES, only whether a
 * required secret is present. Exit code is non-zero when any P0 blocker is
 * found (CI-friendly). P1/P2 findings inform but do not fail the gate.
 *
 * Severity is environment-aware: several checks are only blockers in
 * production (e.g. APP_DEBUG, wildcard CORS, insecure cookies).
 */
class SecurityReadiness extends Command
{
    protected $signature = 'hexa:security-readiness {--json : Output machine-readable JSON}';

    protected $description = 'Detect insecure production configuration (debug, legacy surfaces, cookies, CORS, CSP, HSTS, secrets)';

    /** @var array<int, array{severity: string, check: string, status: string, detail: string}> */
    private array $findings = [];

    public function handle(): int
    {
        $isProduction = app()->environment('production');

        // --- Debug -------------------------------------------------------
        $this->assert(
            'P0',
            'app.debug disabled in production',
            ! ($isProduction && config('app.debug')),
            'APP_DEBUG must be false in production (it leaks stack traces).',
        );

        // --- Legacy surfaces fail-closed in production -------------------
        foreach (['public_web', 'admin', 'api'] as $surface) {
            $enabled = (bool) config("legacy.{$surface}");
            $this->assert(
                'P0',
                "legacy.{$surface} disabled in production",
                ! ($isProduction && $enabled),
                'LEGACY_'.strtoupper($surface).'_ENABLED must be off in production.',
                $isProduction ? 'P0' : 'P2',
            );
        }

        // --- Session cookie security ------------------------------------
        $this->assert(
            'P0',
            'secure session cookie in production',
            ! ($isProduction && ! config('session.secure')),
            'SESSION_SECURE_COOKIE must be true in production.',
        );
        $this->assert(
            'P1',
            'session cookie http_only',
            (bool) config('session.http_only'),
            'session.http_only should be true.',
        );
        $this->assert(
            'P1',
            'session SameSite not "none"',
            config('session.same_site') !== 'none' || config('session.secure'),
            'SameSite=none requires Secure cookies.',
        );

        // --- CORS --------------------------------------------------------
        $origins = (array) config('cors.allowed_origins');
        $wildcard = in_array('*', $origins, true) || $origins === [];
        $this->assert(
            'P0',
            'CORS not wildcard in production',
            ! ($isProduction && $wildcard),
            'CORS_ALLOWED_ORIGINS must be an explicit allow-list in production, not "*".',
            $isProduction ? 'P0' : 'P2',
        );

        // --- Site URL ----------------------------------------------------
        $this->assert(
            'P1',
            'application URL configured',
            ! empty(config('app.url')) && config('app.url') !== 'http://localhost',
            'APP_URL should be the real production URL.',
        );

        // --- Revalidation secret present when enabled -------------------
        $revalEnabled = (bool) config('revalidation.enabled');
        $this->assert(
            'P1',
            'revalidation secret present when enabled',
            ! $revalEnabled || ! empty(config('revalidation.secret')),
            'REVALIDATION_SECRET must be set when REVALIDATION_ENABLED is true.',
        );

        // --- CSP state ---------------------------------------------------
        $cspEnforced = (bool) config('security.csp.enforce') && (bool) config('security.csp.enabled');
        $this->assert(
            'P2',
            'CSP enforced (not report-only)',
            $cspEnforced,
            'CSP is Report-Only. See docs/security/content-security-policy.md for the enforce blockers.',
        );

        // --- HSTS in production -----------------------------------------
        $this->assert(
            'P1',
            'HSTS enabled in production',
            ! $isProduction || (bool) config('security.hsts.enabled'),
            'HSTS_ENABLED should be true for HTTPS production.',
        );

        return $this->report();
    }

    /**
     * @param  string  $severity  severity if the assertion FAILS
     */
    private function assert(string $severity, string $check, bool $passed, string $detail, ?string $overrideSeverity = null): void
    {
        $this->findings[] = [
            'severity' => $passed ? 'OK' : ($overrideSeverity ?? $severity),
            'check' => $check,
            'status' => $passed ? 'pass' : 'fail',
            'detail' => $passed ? '' : $detail,
        ];
    }

    private function report(): int
    {
        $p0 = array_values(array_filter($this->findings, fn ($f) => $f['status'] === 'fail' && $f['severity'] === 'P0'));

        if ($this->option('json')) {
            $this->line((string) json_encode([
                'environment' => app()->environment(),
                'blockers' => count($p0),
                'findings' => $this->findings,
            ], JSON_PRETTY_PRINT));

            return $p0 === [] ? self::SUCCESS : self::FAILURE;
        }

        $this->table(
            ['Severity', 'Check', 'Status', 'Detail'],
            array_map(fn ($f) => [$f['severity'], $f['check'], $f['status'], $f['detail']], $this->findings),
        );

        if ($p0 !== []) {
            $this->error(count($p0).' P0 security blocker(s) found — not production-ready.');

            return self::FAILURE;
        }

        $this->info('No P0 security blockers for environment: '.app()->environment());

        return self::SUCCESS;
    }
}
