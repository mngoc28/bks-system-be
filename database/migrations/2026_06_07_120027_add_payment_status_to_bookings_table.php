<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
            $table->string('payment_status', 50)->default('unpaid')->after('status')->comment('Trạng thái thanh toán: unpaid, partially_paid, paid, refunded');
        });

        // Backfill existing confirmed or completed bookings as 'paid'
        DB::table('bookings')->whereIn('status', [1, 3])->update(['payment_status' => 'paid']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });
    }
};
