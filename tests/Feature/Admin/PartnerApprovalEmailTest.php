<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\PartnerInfo;
use App\Enums\Status;
use App\Mail\PartnerApprovedMail;
use App\Mail\PartnerRejectedMail;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PartnerApprovalEmailTest extends TestCase
{
    use DatabaseMigrations;

    protected function signInAdmin()
    {
        $this->artisan('migrate:refresh');
        $this->seed();

        $response = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@gmail.com',
            'password' => '123456a!',
        ]);
        $response->assertStatus(200);
        $data = $response->json('data');
        return $data['token'];
    }

    public function test_admin_can_approve_partner_and_send_email()
    {
        Mail::fake();

        $token = $this->signInAdmin();

        // Create a partner user pending approval
        $partner = User::create([
            'name' => 'Test Partner Approval',
            'email' => 'partner_approved_test@gmail.com',
            'password' => bcrypt('password123'),
            'role' => 'partner',
            'status' => Status::PENDING_APPROVAL->value,
            'is_email_verified' => true,
        ]);

        PartnerInfo::create([
            'user_id' => $partner->id,
            'company_name' => 'Approved Corp',
            'province_id' => 1,
            'ward_id' => 1,
            'created_by' => $partner->id,
            'updated_by' => $partner->id,
        ]);

        $response = $this->postJson("/api/v1/admin/partners/{$partner->id}/verify", [
            'action' => 'approve',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        
        // Assert the partner status is updated to ACTIVE
        $partner->refresh();
        $this->assertEquals(Status::ACTIVE->value, $partner->status);

        // Assert Mail was sent
        Mail::assertSent(PartnerApprovedMail::class, function ($mail) use ($partner) {
            return $mail->hasTo($partner->email) &&
                   $mail->name === $partner->name &&
                   $mail->email === $partner->email;
        });
    }

    public function test_admin_can_reject_partner_and_send_email()
    {
        Mail::fake();

        $token = $this->signInAdmin();

        // Create a partner user pending approval
        $partner = User::create([
            'name' => 'Test Partner Rejection',
            'email' => 'partner_rejected_test@gmail.com',
            'password' => bcrypt('password123'),
            'role' => 'partner',
            'status' => Status::PENDING_APPROVAL->value,
            'is_email_verified' => true,
        ]);

        PartnerInfo::create([
            'user_id' => $partner->id,
            'company_name' => 'Rejected Corp',
            'province_id' => 1,
            'ward_id' => 1,
            'created_by' => $partner->id,
            'updated_by' => $partner->id,
        ]);

        $rejectionReason = 'Hồ sơ thiếu chứng nhận đăng ký kinh doanh hợp lệ.';

        $response = $this->postJson("/api/v1/admin/partners/{$partner->id}/verify", [
            'action' => 'reject',
            'rejection_reason' => $rejectionReason,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);

        // Assert the partner status is updated to REJECTED
        $partner->refresh();
        $this->assertEquals(Status::REJECTED->value, $partner->status);

        // Assert rejection reason is saved
        $partnerInfo = PartnerInfo::where('user_id', $partner->id)->first();
        $this->assertEquals($rejectionReason, $partnerInfo->rejection_reason);

        // Assert Mail was sent
        Mail::assertSent(PartnerRejectedMail::class, function ($mail) use ($partner, $rejectionReason) {
            return $mail->hasTo($partner->email) &&
                   $mail->name === $partner->name &&
                   $mail->email === $partner->email &&
                   $mail->rejection_reason === $rejectionReason;
        });
    }
}
