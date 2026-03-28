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
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('province_id');
            $table->unsignedBigInteger('ward_id');
            $table->string('name', 255);
            $table->string('address_detail', 255)->nullable();
            $table->integer('number_of_floors')->default(1);
            $table->integer('number_of_units')->default(0);
            $table->integer('year_built')->nullable();
            $table->tinyInteger('building_type')->unsigned()->nullable()->comment('1: apartment building, 2: building, 3: villa, 4: townhouse, 5: serviced apartment, 6: boarding house, 7: hotel, 8: office, 9: other');
            $table->decimal('area', 10, 2)->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Indexes
            $table->index('province_id');
            $table->index('ward_id');
            $table->index('name');
            $table->index(['province_id', 'ward_id']);

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
        Schema::dropIfExists('buildings');
    }
};
