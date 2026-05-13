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
        Schema::create('booking_timeline_events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('event_type', 50);
            $table->string('from_status', 50)->nullable();
            $table->string('to_status', 50)->nullable();
            $table->text('note')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'created_at'], 'idx_btl_booking_created');
            $table->index('event_type', 'idx_btl_event_type');
            $table->index('actor_id', 'idx_btl_actor');

            $table->foreign('booking_id', 'fk_btl_booking')
                ->references('id')->on('bookings')
                ->cascadeOnDelete();
            $table->foreign('actor_id', 'fk_btl_actor')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_timeline_events');
    }
};
