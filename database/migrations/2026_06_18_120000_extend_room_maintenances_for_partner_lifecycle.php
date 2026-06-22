<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_maintenances', function (Blueprint $table): void {
            $table->foreignId('room_block_id')
                ->nullable()
                ->after('status')
                ->constrained('room_blocks')
                ->nullOnDelete();

            $table->boolean('block_calendar')
                ->default(true)
                ->after('room_block_id');

            $table->string('source', 30)
                ->default('partner')
                ->after('block_calendar');

            $table->string('cancellation_reason', 500)
                ->nullable()
                ->after('source');

            $table->timestamp('started_at')->nullable()->after('cancellation_reason');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            $table->timestamp('cancelled_at')->nullable()->after('completed_at');

            $table->index(
                ['property_id', 'status', 'maintenance_type', 'start_time'],
                'idx_room_maintenances_partner_scope'
            );
        });
    }

    public function down(): void
    {
        Schema::table('room_maintenances', function (Blueprint $table): void {
            $table->dropForeign(['room_block_id']);
            $table->dropIndex('idx_room_maintenances_partner_scope');
            $table->dropColumn([
                'room_block_id',
                'block_calendar',
                'source',
                'cancellation_reason',
                'started_at',
                'completed_at',
                'cancelled_at',
            ]);
        });
    }
};
