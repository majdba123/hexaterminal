<?php

namespace Tests\Feature\Security;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewModerationTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => 'admin-test@hexaterminal.test',
            'password' => bcrypt('secret-password-123'),
            'type' => 1,
        ]);
    }

    public function test_public_review_index_excludes_unapproved_reviews(): void
    {
        Review::create(['name' => 'Approved Guy', 'content' => 'Visible', 'rating' => 5, 'is_approved' => true]);
        Review::create(['name' => 'Pending Guy', 'content' => 'Hidden', 'rating' => 4, 'is_approved' => false]);

        $response = $this->getJson('/api/review/index');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('Approved Guy'));
        $this->assertFalse($names->contains('Pending Guy'), 'Unapproved review leaked to the public.');
    }

    public function test_public_paginated_review_index_excludes_unapproved_reviews(): void
    {
        Review::create(['name' => 'Approved Guy', 'content' => 'Visible', 'rating' => 5, 'is_approved' => true]);
        Review::create(['name' => 'Pending Guy', 'content' => 'Hidden', 'rating' => 4, 'is_approved' => false]);

        $response = $this->getJson('/api/review/index?page=1&per_page=50');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertFalse($names->contains('Pending Guy'), 'Unapproved review leaked via pagination.');
    }

    public function test_public_review_show_hides_unapproved_review(): void
    {
        $pending = Review::create(['name' => 'Pending', 'content' => 'Hidden', 'rating' => 3, 'is_approved' => false]);

        $this->getJson('/api/review/show/'.$pending->id)->assertNotFound();
    }

    public function test_admin_token_sees_unapproved_reviews_for_moderation(): void
    {
        Review::create(['name' => 'Pending Guy', 'content' => 'Hidden', 'rating' => 4, 'is_approved' => false]);

        $admin = $this->makeAdmin();
        $token = $admin->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/review/index?page=1&per_page=50');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');
        $this->assertTrue($names->contains('Pending Guy'), 'Admin moderation view lost access to pending reviews.');
    }

    public function test_public_store_cannot_self_approve(): void
    {
        $response = $this->postJson('/api/review/store', [
            'name' => 'Sneaky',
            'content' => 'Approve me instantly',
            'rating' => 5,
            'is_approved' => true, // must be ignored
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('reviews', ['name' => 'Sneaky', 'is_approved' => false]);
        $this->assertDatabaseMissing('reviews', ['name' => 'Sneaky', 'is_approved' => true]);
    }

    public function test_anonymous_cannot_update_or_delete_reviews(): void
    {
        $review = Review::create(['name' => 'A', 'content' => 'B', 'rating' => 5, 'is_approved' => false]);

        $this->putJson('/api/review/update/'.$review->id, ['is_approved' => true])->assertUnauthorized();
        $this->deleteJson('/api/review/delete/'.$review->id)->assertUnauthorized();
    }
}
