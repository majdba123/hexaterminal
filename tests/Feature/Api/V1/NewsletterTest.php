<?php

namespace Tests\Feature\Api\V1;

use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscribing_creates_an_active_record_with_consent_timestamp(): void
    {
        $this->postJson('/api/v1/public/newsletter', ['email' => 'lead@example.com', 'locale' => 'en'])
            ->assertCreated();

        $subscriber = NewsletterSubscriber::where('email', 'lead@example.com')->firstOrFail();
        $this->assertSame(NewsletterSubscriber::STATUS_ACTIVE, $subscriber->status);
        $this->assertNotNull($subscriber->consent_at);
    }

    public function test_resubscribing_is_idempotent(): void
    {
        $this->postJson('/api/v1/public/newsletter', ['email' => 'dup@example.com'])->assertCreated();
        $this->postJson('/api/v1/public/newsletter', ['email' => 'dup@example.com'])->assertCreated();

        $this->assertSame(1, NewsletterSubscriber::where('email', 'dup@example.com')->count());
    }

    public function test_unsubscribed_email_is_not_silently_reactivated(): void
    {
        NewsletterSubscriber::create([
            'email' => 'gone@example.com',
            'locale' => 'en',
            'status' => NewsletterSubscriber::STATUS_UNSUBSCRIBED,
        ]);

        // Returns success (no enumeration signal) but does not resurrect the subscription.
        $this->postJson('/api/v1/public/newsletter', ['email' => 'gone@example.com'])->assertCreated();

        $this->assertSame(
            NewsletterSubscriber::STATUS_UNSUBSCRIBED,
            NewsletterSubscriber::where('email', 'gone@example.com')->value('status'),
        );
    }

    public function test_honeypot_field_silently_rejects_without_saving(): void
    {
        $this->postJson('/api/v1/public/newsletter', ['email' => 'bot@example.com', 'website' => 'http://spam.test'])
            ->assertCreated();

        $this->assertDatabaseCount('newsletter_subscribers', 0);
    }

    public function test_invalid_email_is_rejected(): void
    {
        $this->postJson('/api/v1/public/newsletter', ['email' => 'not-an-email'])
            ->assertStatus(422);
    }
}
