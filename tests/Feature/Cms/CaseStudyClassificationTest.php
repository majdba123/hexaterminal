<?php

namespace Tests\Feature\Cms;

use App\Filament\Resources\CaseStudies\Pages\CreateCaseStudy;
use App\Filament\Resources\CaseStudies\Pages\EditCaseStudy;
use App\Models\CaseStudy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CaseStudyClassificationTest extends TestCase
{
    use RefreshDatabase;

    private function makeCmsAdmin(): User
    {
        Role::findOrCreate('admin', 'web');

        $user = User::create([
            'name' => 'CMS Admin',
            'email' => 'case-study-admin@hexaterminal.test',
            'password' => bcrypt('a-long-secure-password'),
        ]);
        $user->assignRole('admin');

        return $user;
    }

    public function test_admin_can_create_case_study_with_valid_or_null_classification(): void
    {
        $admin = $this->makeCmsAdmin();

        Livewire::actingAs($admin)
            ->test(CreateCaseStudy::class)
            ->fillForm([
                'title' => 'ERP rollout',
                'slug' => 'erp-rollout',
                'project_classification' => CaseStudy::CLASSIFICATION_CUSTOM_ERP_CRM,
                'status' => 'draft',
                'sort_order' => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::actingAs($admin)
            ->test(CreateCaseStudy::class)
            ->fillForm([
                'title' => 'Awaiting classification',
                'slug' => 'awaiting-classification',
                'project_classification' => null,
                'status' => 'draft',
                'sort_order' => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('case_studies', [
            'slug' => 'erp-rollout',
            'project_classification' => CaseStudy::CLASSIFICATION_CUSTOM_ERP_CRM,
        ]);
        $this->assertDatabaseHas('case_studies', [
            'slug' => 'awaiting-classification',
            'project_classification' => null,
        ]);
    }

    public function test_admin_rejects_invalid_classification_on_create_and_update(): void
    {
        $admin = $this->makeCmsAdmin();

        Livewire::actingAs($admin)
            ->test(CreateCaseStudy::class)
            ->fillForm([
                'title' => 'Invalid classification',
                'slug' => 'invalid-classification',
                'project_classification' => 'invented_classification',
                'status' => 'draft',
                'sort_order' => 0,
            ])
            ->call('create')
            ->assertHasFormErrors(['project_classification' => 'in']);

        $caseStudy = CaseStudy::create([
            'slug' => 'existing-case-study',
            'title' => ['en' => 'Existing case study'],
            'project_classification' => null,
            'is_published' => false,
        ]);

        Livewire::actingAs($admin)
            ->test(EditCaseStudy::class, ['record' => $caseStudy->id])
            ->fillForm(['project_classification' => 'invented_classification'])
            ->call('save')
            ->assertHasFormErrors(['project_classification' => 'in']);

        $this->assertNull($caseStudy->fresh()->project_classification);
    }
}
