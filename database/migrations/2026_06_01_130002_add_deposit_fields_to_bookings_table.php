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
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('deposit_amount', 15, 2)->nullable()->after('price_id')->comment('Số tiền cọc thực tế của đơn booking');
            $table->string('deposit_status', 50)->nullable()->after('deposit_amount')->comment('Trạng thái cọc đồng bộ từ bảng booking_deposits');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['deposit_amount', 'deposit_status']);
        });
    }
};
