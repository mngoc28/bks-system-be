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
     * Adds Partner Portal 360 audit/KPI columns and indexes to bookings.
     * All new columns are nullable so existing booking flows are not affected.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->timestamp('confirmed_at')->nullable()->after('status')
                ->comment('Time when partner confirmed the booking, used for time-to-confirm KPI');
            $table->timestamp('cancelled_at')->nullable()->after('confirmed_at')
                ->comment('Time when booking was cancelled');
            $table->text('cancellation_reason')->nullable()->after('cancelled_at')
                ->comment('Reason provided when partner cancels');
            $table->timestamp('no_show_at')->nullable()->after('cancellation_reason')
                ->comment('Time when partner marks booking as no-show');
            $table->string('source', 50)->nullable()->after('no_show_at')
                ->comment('Booking source: web, partner_portal, future_ota');

            $table->index('confirmed_at', 'idx_bookings_confirmed_at');
            $table->index('cancelled_at', 'idx_bookings_cancelled_at');
            $table->index(['status', 'created_at'], 'idx_bookings_status_created_at');
            $table->index(
                ['room_id', 'start_date', 'end_date', 'status'],
                'idx_bookings_room_dates_status'
            );
            $table->index('source', 'idx_bookings_source');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropIndex('idx_bookings_confirmed_at');
            $table->dropIndex('idx_bookings_cancelled_at');
            $table->dropIndex('idx_bookings_status_created_at');
            $table->dropIndex('idx_bookings_room_dates_status');
            $table->dropIndex('idx_bookings_source');

            $table->dropColumn([
                'confirmed_at',
                'cancelled_at',
                'cancellation_reason',
                'no_show_at',
                'source',
            ]);
        });
    }
};
