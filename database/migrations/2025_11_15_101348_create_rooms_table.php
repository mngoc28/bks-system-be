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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id');
            $table->string('title', 255);
            $table->string('room_number', 50)->nullable();
            $table->decimal('deposit', 12, 2)->nullable();
            $table->decimal('area', 10, 2);
            $table->integer('floor_number')->default(1);
            $table->integer('people')->unsigned()->default(1);
            $table->tinyInteger('room_type')->unsigned()->default(1);
            $table->boolean('status')->default(false);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Indexes
            $table->index('property_id');
            $table->index('status');

            // Foreign keys
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rooms');
    }
};
