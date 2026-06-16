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
        Schema::table('room_images', function (Blueprint $table) {
            $table->integer('image_type')
                ->default(0)
                ->comment('0: other, 1: main_room, 2: interior, 3: exterior, 4: bathroom, 5: kitchen, 6: balcony, 7: living_room, 8: bedroom, 9: dining_room, 10: garden, 11: parking, 12: entrance, 13: staircase, 14: hallway, 15: office')
                ->change();
        });

        Schema::table('property_images', function (Blueprint $table) {
            $table->integer('image_type')
                ->default(0)
                ->comment('0: other, 1: facade, 2: lobby, 3: hallway, 4: staircase, 5: elevator, 6: parking, 7: shared_facilities, 8: entrance, 9: backyard, 10: surrounding, 11: security, 12: technical_room, 13: reception, 14: common_space, 15: rooftop, 16: basement, 17: other_supp')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('room_images', function (Blueprint $table) {
            $table->integer('image_type')
                ->default(0)
                ->comment('0: other, 1: exterior, 2: interior, 3: bathroom, 4: kitchen')
                ->change();
        });

        Schema::table('property_images', function (Blueprint $table) {
            $table->integer('image_type')
                ->default(0)
                ->comment('0: other, 1: exterior, 2: interior, 3: bathroom, 4: kitchen')
                ->change();
        });
    }
};
