<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table): void {
            $table->index('user_id', 'properties_user_id_index');
        });

        Schema::table('property_images', function (Blueprint $table): void {
            $table->index(['property_id', 'sort', 'id'], 'property_images_property_sort_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table): void {
            $table->dropIndex('properties_user_id_index');
        });

        Schema::table('property_images', function (Blueprint $table): void {
            $table->dropIndex('property_images_property_sort_id_index');
        });
    }
};
