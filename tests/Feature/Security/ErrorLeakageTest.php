<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ErrorLeakageTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_store_failure_returns_generic_error_without_exception_details(): void
    {
        // Force a database failure so the controller's catch block runs.
        Schema::drop('reviews');

        $response = $this->postJson('/api/review/store', [
            'name' => 'X',
            'content' => 'Y',
            'rating' => 5,
        ]);

        $response->assertStatus(500);
        $response->assertJson(['status' => 'error', 'message' => 'Review creation failed']);

        // The response body must not include SQL/exception detail.
        $body = $response->getContent();
        $this->assertStringNotContainsStringIgnoringCase('SQLSTATE', $body);
        $this->assertStringNotContainsStringIgnoringCase('no such table', $body);
        $this->assertArrayNotHasKey('error', $response->json());
    }
}
