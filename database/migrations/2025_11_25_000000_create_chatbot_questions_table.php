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
        Schema::create('chatbot_questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('content');
            $table->integer('type');
            $table->integer('position_x')->default(0);
            $table->integer('position_y')->default(0);
            $table->nullableTimestamps();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->boolean('is_start_node')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_questions');
    }
};
