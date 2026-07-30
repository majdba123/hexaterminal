<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PragmaRX\Google2FAQRCode\Google2FA;
use Tests\TestCase;

/**
 * TOTP (authenticator-app) multi-factor authentication, backed by
 * Filament's first-party Filament\Auth\MultiFactor\App\AppAuthentication
 * provider (App\Models\User implements its two contracts; wired in
 * App\Providers\Filament\CmsPanelProvider). Verifies the full real
 * contract: secret/recovery-code storage never leaks, admins are required
 * to have it enabled, a genuine TOTP code (computed the same way an
 * authenticator app would) verifies correctly, and recovery codes are
 * single-use.
 */
class FilamentMfaTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        $this->seed(RolesSeeder::class);
        $user = User::create([
            'name' => 'Admin',
            'email' => 'mfa-admin@hexaterminal.test',
            'password' => bcrypt('a-long-secure-password'),
        ]);
        $user->assignRole('admin');

        return $user;
    }

    public function test_panel_has_app_authentication_registered(): void
    {
        $panel = Filament::getPanel('cms');

        $this->assertTrue($panel->hasMultiFactorAuthentication());
        $this->assertArrayHasKey('app', $panel->getMultiFactorAuthenticationProviders());
    }

    public function test_mfa_is_required_for_admins_but_not_other_roles(): void
    {
        $this->seed(RolesSeeder::class);
        $panel = Filament::getPanel('cms');

        $admin = $this->makeAdmin();
        $this->actingAs($admin);
        $this->assertTrue($panel->isMultiFactorAuthenticationRequired());

        $editor = User::create(['name' => 'Editor', 'email' => 'editor-mfa@hexaterminal.test', 'password' => bcrypt('a-long-secure-password')]);
        $editor->assignRole('editor');
        $this->actingAs($editor);
        $this->assertFalse($panel->isMultiFactorAuthenticationRequired());
    }

    public function test_secret_and_recovery_codes_are_never_serialized(): void
    {
        $admin = $this->makeAdmin();
        $admin->saveAppAuthenticationSecret('SOMESECRETKEYVALUE');
        $admin->saveAppAuthenticationRecoveryCodes(['recovery-code-one', 'recovery-code-two']);

        $array = $admin->fresh()->toArray();

        $this->assertArrayNotHasKey('app_authentication_secret', $array);
        $this->assertArrayNotHasKey('app_authentication_recovery_codes', $array);
    }

    public function test_secret_is_encrypted_at_rest(): void
    {
        $admin = $this->makeAdmin();
        $admin->saveAppAuthenticationSecret('SOMESECRETKEYVALUE');

        $rawColumnValue = DB::table('users')->where('id', $admin->id)->value('app_authentication_secret');

        $this->assertStringNotContainsString('SOMESECRETKEYVALUE', (string) $rawColumnValue);
        $this->assertSame('SOMESECRETKEYVALUE', $admin->fresh()->getAppAuthenticationSecret());
    }

    public function test_provider_reports_not_enabled_until_a_secret_is_saved(): void
    {
        $admin = $this->makeAdmin();
        $provider = app(AppAuthentication::class);

        $this->assertFalse($provider->isEnabled($admin));

        $admin->saveAppAuthenticationSecret($provider->generateSecret());
        $this->assertTrue($provider->isEnabled($admin->fresh()));
    }

    public function test_a_genuine_totp_code_verifies_and_a_wrong_one_does_not(): void
    {
        $admin = $this->makeAdmin();
        $provider = app(AppAuthentication::class);
        $secret = $provider->generateSecret();
        $admin->saveAppAuthenticationSecret($secret);

        $google2fa = app(Google2FA::class);
        $validCode = $google2fa->getCurrentOtp($secret);

        $this->assertTrue($provider->verifyCode($validCode, $secret));
        $this->assertFalse($provider->verifyCode('000000', $secret));
    }

    public function test_recovery_code_is_single_use(): void
    {
        $admin = $this->makeAdmin();
        $provider = app(AppAuthentication::class);
        $codes = $provider->generateRecoveryCodes();
        $provider->saveRecoveryCodes($admin, $codes);

        $firstCode = $codes[0];

        $this->assertTrue($provider->verifyRecoveryCode($firstCode, $admin->fresh()));
        // Consumed -- using the same code again must fail.
        $this->assertFalse($provider->verifyRecoveryCode($firstCode, $admin->fresh()));
    }
}
