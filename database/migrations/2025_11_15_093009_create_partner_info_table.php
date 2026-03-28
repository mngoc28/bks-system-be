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
        Schema::create('partner_info', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('province_id');
            $table->unsignedBigInteger('ward_id');
            $table->string('address', 255)->nullable();
            $table->string('company_name', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('website', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('image_1')->nullable();
            $table->string('image_2')->nullable();
            $table->string('image_3')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // indexes
            $table->index('user_id');
            $table->index('company_name');
            $table->index('province_id');
            $table->index('ward_id');

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('restrict');
            $table->foreign('ward_id')->references('id')->on('wards')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partner_info');
    }
};
