<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_tourist_spot_maps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('tourist_spot_id')->constrained('tourist_spots')->cascadeOnDelete();
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->unsignedInteger('travel_time_minutes');
            $table->unsignedInteger('priority_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->string('source_type', 30)->default('estimated');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['room_id', 'tourist_spot_id'], 'idx_room_tourist_spot_maps_room_spot');
            $table->index(['room_id', 'is_primary', 'priority_order'], 'idx_room_tourist_spot_maps_room_primary_priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_tourist_spot_maps');
    }
};