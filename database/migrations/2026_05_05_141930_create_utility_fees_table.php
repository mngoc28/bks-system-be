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
        Schema::create('utility_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->string('fee_type')->comment('electricity, water, internet, parking, management, other');
            $table->enum('calc_method', ['index', 'fixed', 'person'])->default('fixed')->comment('Tính theo số, khoán, hoặc theo người');
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->boolean('is_included')->default(false)->comment('Đã bao gồm trong tiền nhà hay chưa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('utility_fees');
    }
};
