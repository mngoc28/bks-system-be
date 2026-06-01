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
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('payment_collected_at')->nullable()->after('updated_at');
            $table->unsignedBigInteger('settlement_period_id')->nullable()->after('payment_collected_at');

            $table->foreign('settlement_period_id', 'fk_bookings_settlement')
                ->references('id')
                ->on('partner_settlement_periods')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign('fk_bookings_settlement');
            $table->dropColumn(['payment_collected_at', 'settlement_period_id']);
        });
    }
};
