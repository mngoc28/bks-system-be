<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table): void {
            $table->index(['property_id', 'id'], 'rooms_property_id_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table): void {
            $table->dropIndex('rooms_property_id_id_index');
        });
    }
};
