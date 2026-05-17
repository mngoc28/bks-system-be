<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $afterColumn = Schema::hasColumn('bookings', 'source') ? 'source' : 'status';

        Schema::table('bookings', function (Blueprint $table) use ($afterColumn): void {
            $table->timestamp('pending_cancellation_since')->nullable()->after($afterColumn)
                ->comment('When guest cancel-request moved booking to pending_cancellation');
            $table->string('cancellation_policy_version', 32)->nullable()->after('pending_cancellation_since');
            $table->string('client_local_id', 64)->nullable()->after('cancellation_policy_version');
            $table->string('client_fingerprint', 64)->nullable()->after('client_local_id');

            $table->index('pending_cancellation_since', 'idx_bookings_pending_cancellation_since');
            $table->index('client_local_id', 'idx_bookings_client_local_id');
            $table->index('client_fingerprint', 'idx_bookings_client_fingerprint');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropIndex('idx_bookings_pending_cancellation_since');
            $table->dropIndex('idx_bookings_client_local_id');
            $table->dropIndex('idx_bookings_client_fingerprint');

            $table->dropColumn([
                'pending_cancellation_since',
                'cancellation_policy_version',
                'client_local_id',
                'client_fingerprint',
            ]);
        });
    }
};
