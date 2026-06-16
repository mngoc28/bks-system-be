<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('room_images', function (Blueprint $table): void {
            $table->index(['room_id', 'sort', 'id'], 'room_images_room_sort_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_images', function (Blueprint $table): void {
            $table->dropIndex('room_images_room_sort_id_index');
        });
    }
};
