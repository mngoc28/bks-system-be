<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('partner_settlement_periods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('partner_id');
            $table->date('period_start');
            $table->date('period_end');
            $table->date('issue_date');
            $table->decimal('total_gmv', 15, 2);
            $table->decimal('total_commission', 15, 2);
            $table->decimal('commission_rate', 5, 4)->default(0.0500);
            $table->string('status', 20)->default('draft');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference', 100)->nullable();
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['partner_id', 'period_start', 'period_end'], 'partner_period_unique');
            $table->index(['partner_id', 'status'], 'idx_settlements_partner_status');

            $table->foreign('partner_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('confirmed_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('settlement_line_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('settlement_period_id');
            $table->unsignedBigInteger('booking_id');
            $table->string('booking_code', 32);
            $table->date('checkout_date');
            $table->decimal('room_gmv', 15, 2);
            $table->decimal('services_gmv', 15, 2);
            $table->decimal('total_gmv', 15, 2);
            $table->decimal('commission_amount', 15, 2);
            $table->unsignedTinyInteger('snapshot_status')->default(3);
            $table->timestamps();

            $table->index('settlement_period_id', 'idx_line_items_period');
            $table->index('booking_id', 'idx_line_items_booking');

            $table->foreign('settlement_period_id')->references('id')->on('partner_settlement_periods')->onDelete('cascade');
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('restrict');
        });

        Schema::create('settlement_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('settlement_period_id');
            $table->decimal('amount', 15, 2);
            $table->text('reason');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index('settlement_period_id', 'idx_adjustments_period');

            $table->foreign('settlement_period_id')->references('id')->on('partner_settlement_periods')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('settlement_adjustments');
        Schema::dropIfExists('settlement_line_items');
        Schema::dropIfExists('partner_settlement_periods');
    }
};
