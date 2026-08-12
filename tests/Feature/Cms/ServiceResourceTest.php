<?php

namespace Tests\Feature\Cms;

use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ServiceResourceTest extends TestCase
{
    use RefreshDatabase;

    private function makeCmsAdmin(): User
    {
        Role::findOrCreate('admin', 'web');

        $user = User::create([
            'name' => 'CMS Admin',
            'email' => 'cms-admin@hexaterminal.test',
            'password' => bcrypt('a-long-secure-password'),
        ]);
        $user->assignRole('admin');

        return $user;
    }

    public function test_non_role_user_cannot_access_the_cms(): void
    {
        $user = User::create([
            'name' => 'Rando',
            'email' => 'rando@hexaterminal.test',
            'password' => bcrypt('a-long-secure-password'),
        ]);

        $this->actingAs($user)->get('/cms/services')->assertForbidden();
    }

    public function test_admin_can_list_services(): void
    {
        $admin = $this->makeCmsAdmin();
        Service::create(['name' => ['en' => 'Backend Engineering'], 'is_published' => true]);

        $this->actingAs($admin)->get('/cms/services')->assertOk();
    }

    public function test_admin_can_create_a_service(): void
    {
        $admin = $this->makeCmsAdmin();

        Livewire::actingAs($admin)
            ->test(CreateService::class)
            ->fillForm([
                'name' => 'AI Workflows',
                'slug' => 'ai-workflows',
                'is_published' => true,
                'sort_order' => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('service_offerings', ['slug' => 'ai-workflows']);
        $this->assertSame('AI Workflows', Service::where('slug', 'ai-workflows')->first()->getTranslation('name', 'en'));
    }

    public function test_admin_can_edit_a_service_in_a_second_locale(): void
    {
        $admin = $this->makeCmsAdmin();
        $service = Service::create(['slug' => 'crm-systems', 'name' => ['en' => 'CRM Systems'], 'is_published' => true, 'sort_order' => 0]);

        Livewire::actingAs($admin)
            ->test(EditService::class, ['record' => $service->id])
            ->set('activeLocale', 'ar')
            ->fillForm(['name' => 'أنظمة إدارة علاقات العملاء'])
            ->call('save')
            ->assertHasNoFormErrors();

        $service->refresh();
        $this->assertSame('CRM Systems', $service->getTranslation('name', 'en'));
        $this->assertSame('أنظمة إدارة علاقات العملاء', $service->getTranslation('name', 'ar'));
    }
}
