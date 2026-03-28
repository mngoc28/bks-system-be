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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('room_id');
            $table->unsignedBigInteger('price_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->tinyInteger('status')->unsigned()->default(0);
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // indexes
            $table->index('user_id');
            $table->index('room_id');
            $table->index('price_id');
            $table->index('status');
            $table->index('start_date');
            $table->index(['user_id', 'status']);
            $table->index(['room_id', 'start_date']);
            $table->index(['room_id', 'price_id']);

            // foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('restrict');
            $table->foreign('price_id')->references('id')->on('room_prices')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookings');
    }
};
