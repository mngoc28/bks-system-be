<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\NewsletterSubscription;
use App\Models\User;
use App\Mail\NewsletterWelcomeMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

final class NewsletterSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_can_register_new_email_for_coupon(): void
    {
        Mail::fake();

        // Create an active coupon
        $coupon = Coupon::create([
            'code' => 'WELCOME10',
            'type' => 'percent',
            'value' => 10.00,
            'usage_limit' => 100,
            'used_count' => 0,
            'status' => 'active',
        ]);

        $email = 'test.new.guest@example.com';

        $response = $this->postJson('/api/v1/home/coupons/register', [
            'email' => $email,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.code', 'WELCOME10');
        $response->assertJsonPath('data.value', 10);
        $response->assertJsonPath('data.type', 'percent');

        // Assert DB records
        $this->assertDatabaseHas('newsletter_subscriptions', [
            'email' => $email,
            'status' => 'subscribed',
            'coupon_id' => $coupon->id,
        ]);

        // Assert Coupon count incremented
        $this->assertEquals(1, $coupon->fresh()->used_count);

        // Assert Mail was sent
        Mail::assertSent(NewsletterWelcomeMail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });
    }

    public function test_cannot_register_duplicate_email(): void
    {
        Mail::fake();

        $email = 'test.duplicate@example.com';

        // Register once
        NewsletterSubscription::create([
            'email' => $email,
            'status' => 'subscribed',
        ]);

        // Register second time
        $response = $this->postJson('/api/v1/home/coupons/register', [
            'email' => $email,
        ]);

        $response->assertStatus(400);
        $response->assertJsonPath('status', 'error');
        $response->assertJsonPath('message', 'Email này đã được sử dụng để nhận mã ưu đãi. Vui lòng kiểm tra lại hộp thư.');

        Mail::assertNothingSent();
    }

    public function test_invalid_email_validation(): void
    {
        $response = $this->postJson('/api/v1/home/coupons/register', [
            'email' => 'invalid-email-format',
        ]);

        $response->assertStatus(422);
    }

    public function test_admin_can_list_and_manage_newsletter_subscriptions(): void
    {
        // Create standard admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_newsletter_test@gmail.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 1,
            'is_email_verified' => true
        ]);

        // Generate token using the api guard login
        $token = auth('api')->login($admin);

        // Create subscription
        $sub = NewsletterSubscription::create([
            'email' => 'subscriber@test.com',
            'status' => 'subscribed',
        ]);

        // Test List API (Filtered by email to check both list and search logic)
        $response = $this->getJson('/api/v1/admin/newsletter-subscriptions?email=' . $sub->email, [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonCount(1, 'data.items');
        $response->assertJsonPath('data.items.0.email', $sub->email);

        // Test Update Status API
        $updateResponse = $this->putJson("/api/v1/admin/newsletter-subscriptions/{$sub->id}/status", [
            'status' => 'unsubscribed',
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $updateResponse->assertStatus(200);
        $this->assertEquals('unsubscribed', $sub->fresh()->status);

        // Test Delete API
        $deleteResponse = $this->deleteJson("/api/v1/admin/newsletter-subscriptions/{$sub->id}", [], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('newsletter_subscriptions', [
            'id' => $sub->id,
        ]);
    }
}
