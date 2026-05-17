<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_cancellation_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('requester_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason_code', 50)->index();
            $table->text('reason_text')->nullable();
            $table->string('status', 20)->comment('pending, approved, rejected, withdrawn');
            $table->string('idempotency_key', 64)->nullable();
            $table->unsignedTinyInteger('previous_booking_status');
            $table->string('policy_version_snapshot', 32)->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('resolved_at')->nullable()->index();
            $table->foreignId('resolved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('partner_decision_note')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'idempotency_key'], 'idx_bcr_booking_idempotency');
            $table->index(['booking_id', 'status', 'requested_at'], 'idx_bcr_booking_status_requested');
            $table->index(['status', 'requested_at'], 'idx_bcr_status_requested');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_cancellation_requests');
    }
};
