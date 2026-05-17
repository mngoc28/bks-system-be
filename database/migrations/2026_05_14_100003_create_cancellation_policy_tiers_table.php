<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cancellation_policy_tiers', function (Blueprint $table): void {
            $table->id();
            $table->string('version', 32)->index();
            $table->string('stay_kind', 10)->index()->comment('short or long');
            $table->integer('hours_before_checkin_min');
            $table->integer('hours_before_checkin_max')->nullable();
            $table->decimal('fee_percent', 5, 2)->nullable();
            $table->decimal('refund_percent', 5, 2)->nullable();
            $table->timestamps();

            $table->foreign('version')
                ->references('version')
                ->on('cancellation_policy_versions')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cancellation_policy_tiers');
    }
};
