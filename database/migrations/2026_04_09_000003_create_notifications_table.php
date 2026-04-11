<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $col) {
            $col->id();
            $col->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $col->string('title');
            $col->text('message');
            $col->string('type')->default('info'); // info, success, warning, error
            $col->boolean('is_read')->default(false);
            $col->string('link')->nullable();
            $col->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
