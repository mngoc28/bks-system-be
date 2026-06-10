<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Ghi lại thời điểm khách đổi phương thức thanh toán.
            // NULL = chưa bao giờ đổi. Có giá trị = đã đổi 1 lần, không cho đổi nữa.
            $table->timestamp('payment_method_changed_at')->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('payment_method_changed_at');
        });
    }
};
