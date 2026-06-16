<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table): void {
            $table->index('user_id');
            $table->dropUnique(['user_id', 'partner_id', 'booking_id']);
            $table->unique(['user_id', 'partner_id'], 'conversations_user_partner_unique');
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table): void {
            $table->dropUnique('conversations_user_partner_unique');
            $table->unique(['user_id', 'partner_id', 'booking_id']);
            $table->dropIndex(['user_id']);
        });
    }
};
