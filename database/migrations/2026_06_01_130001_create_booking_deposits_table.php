<?php

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
    public function up()
    {
        Schema::create('booking_deposits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->decimal('amount', 15, 2)->comment('Số tiền cọc cần đóng');
            $table->string('status', 50)->default('pending')->comment('Trạng thái đặt cọc: pending, payment_submitted, held_in_escrow, confirmed_by_partner, refunded, forfeited');
            $table->string('receipt_path')->nullable()->comment('Đường dẫn ảnh biên lai chuyển tiền');
            $table->timestamps();

            // Indexes and foreign keys
            $table->index('booking_id');
            $table->index('status');
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_deposits');
    }
};
