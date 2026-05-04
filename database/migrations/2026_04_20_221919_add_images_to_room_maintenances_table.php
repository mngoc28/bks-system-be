<?php

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
        Schema::table('room_maintenances', function (Blueprint $table) {
            $table->json('images')->nullable()->after('description')->comment('Danh sách URL hình ảnh sự cố');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_maintenances', function (Blueprint $table) {
            $table->dropColumn('images');
        });
    }
};
