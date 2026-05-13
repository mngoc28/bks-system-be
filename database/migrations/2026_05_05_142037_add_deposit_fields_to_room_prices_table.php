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
        Schema::table('room_prices', function (Blueprint $table) {
            $table->decimal('deposit_amount', 15, 2)->nullable()->after('price')->comment('Số tiền cọc');
            $table->integer('minimum_stay')->nullable()->after('deposit_amount')->comment('Thời hạn ở tối thiểu (theo đơn vị ngày/tháng của unit)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('room_prices', function (Blueprint $table) {
            $table->dropColumn(['deposit_amount', 'minimum_stay']);
        });
    }
};
