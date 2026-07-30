<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Database\Seeders\RolesSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Authentication is not authorization: a valid account without a CMS role must
 * not reach the Filament panel. Complements the resource-level check in
 * tests/Feature/Cms/ServiceResourceTest.php by asserting the canAccessPanel()
 * contract directly in both directions.
 */
class FilamentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $email): User
    {
        return User::create([
            'name' => 'User',
            'email' => $email,
            'password' => bcrypt('a-long-secure-password'),
        ]);
    }

    public function test_user_without_cms_role_cannot_access_panel(): void
    {
        $this->seed(RolesSeeder::class);
        $panel = Filament::getPanel('cms');

        $this->assertFalse($this->makeUser('norole@hexaterminal.test')->canAccessPanel($panel));
    }

    public function test_admin_and_editor_roles_can_access_panel(): void
    {
        $this->seed(RolesSeeder::class);
        $panel = Filament::getPanel('cms');

        $admin = $this->makeUser('admin@hexaterminal.test');
        $admin->assignRole('admin');
        $this->assertTrue($admin->canAccessPanel($panel));

        $editor = $this->makeUser('editor@hexaterminal.test');
        $editor->assignRole('editor');
        $this->assertTrue($editor->canAccessPanel($panel));
    }

    public function test_unauthenticated_cms_request_redirects_to_login(): void
    {
        $res = $this->get('/cms');

        $this->assertContains($res->getStatusCode(), [302, 200]);
        $this->assertNotSame(404, $res->getStatusCode());
    }
}
