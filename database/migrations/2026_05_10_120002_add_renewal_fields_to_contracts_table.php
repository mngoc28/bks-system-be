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
     * Adds renewal/termination columns to contracts to support long-term lifecycle.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table): void {
            $table->timestamp('renewal_reminder_at')->nullable()
                ->comment('Reminder marker for partner that long-term contract is approaching expiry');
            $table->timestamp('terminated_at')->nullable()
                ->comment('When the contract was terminated');
            $table->text('termination_reason')->nullable()
                ->comment('Reason for termination');

            $table->index('renewal_reminder_at', 'idx_contracts_renewal_reminder');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table): void {
            $table->dropIndex('idx_contracts_renewal_reminder');
            $table->dropColumn([
                'renewal_reminder_at',
                'terminated_at',
                'termination_reason',
            ]);
        });
    }
};
