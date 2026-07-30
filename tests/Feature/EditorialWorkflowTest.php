<?php

namespace Tests\Feature;

use App\Models\ContentActivity;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditorialWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_setting_status_published_derives_is_published_true(): void
    {
        $service = Service::create(['slug' => 'a', 'name' => ['en' => 'A'], 'status' => 'published']);

        $this->assertTrue($service->fresh()->is_published);
        $this->assertNotNull($service->fresh()->published_at);
    }

    public function test_setting_status_draft_derives_is_published_false(): void
    {
        $service = Service::create(['slug' => 'b', 'name' => ['en' => 'B'], 'status' => 'draft']);

        $this->assertFalse($service->fresh()->is_published);
    }

    public function test_setting_is_published_true_without_status_derives_published_status(): void
    {
        $service = Service::create(['slug' => 'c', 'name' => ['en' => 'C'], 'is_published' => true]);

        $this->assertSame('published', $service->fresh()->status);
    }

    public function test_scheduled_content_is_hidden_until_the_future_date_passes(): void
    {
        $service = Service::create([
            'slug' => 'd', 'name' => ['en' => 'D'],
            'status' => 'scheduled', 'published_at' => now()->addDay(),
        ]);

        $this->assertTrue($service->fresh()->is_published);
        $this->getJson('/api/v1/public/services/d')->assertNotFound();
    }

    public function test_audit_stamps_are_recorded_for_an_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $service = Service::create(['slug' => 'e', 'name' => ['en' => 'E'], 'status' => 'draft']);
        $this->assertSame($user->id, $service->created_by);
        $this->assertSame($user->id, $service->updated_by);

        $service->update(['status' => 'approved']);
        $this->assertSame($user->id, $service->fresh()->approved_by);
        $this->assertNotNull($service->fresh()->approved_at);

        $service->update(['status' => 'published']);
        $this->assertSame($user->id, $service->fresh()->published_by);
    }

    public function test_audit_stamps_stay_null_without_an_authenticated_user(): void
    {
        $service = Service::create(['slug' => 'f', 'name' => ['en' => 'F'], 'status' => 'draft']);

        $this->assertNull($service->created_by);
        $this->assertNull($service->updated_by);
    }

    public function test_content_activity_is_logged_on_create_and_status_change(): void
    {
        $service = Service::create(['slug' => 'g', 'name' => ['en' => 'G'], 'status' => 'draft']);
        $service->update(['status' => 'published']);

        $this->assertDatabaseHas('content_activities', [
            'subject_type' => $service->getMorphClass(),
            'subject_id' => $service->id,
            'action' => 'created',
        ]);
        $this->assertDatabaseHas('content_activities', [
            'subject_type' => $service->getMorphClass(),
            'subject_id' => $service->id,
            'action' => 'status_changed',
        ]);
    }

    public function test_content_activity_never_stores_field_values(): void
    {
        $service = Service::create(['slug' => 'h', 'name' => ['en' => 'Secret Name'], 'status' => 'draft']);
        $service->update(['name' => ['en' => 'Updated Name']]);

        $activity = ContentActivity::where('subject_id', $service->id)->where('action', 'updated')->first();

        $this->assertNotNull($activity);
        $this->assertStringNotContainsString('Secret Name', json_encode($activity->details));
        $this->assertStringNotContainsString('Updated Name', json_encode($activity->details));
        $this->assertContains('name', $activity->details['fields']);
    }
}
