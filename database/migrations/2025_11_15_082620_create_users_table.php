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
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 150)->unique();
            $table->boolean('is_email_verified')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('verification_token', 255)->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->string('password', 255);
            $table->enum('role',['admin', 'partner','user'])->default('user');
            $table->string('phone', 20)->nullable();
            $table->string('avatar', 255)->nullable();
            $table->string('id_avatar', 255)->nullable();
            $table->tinyInteger('status')->unsigned()->default(0)->comment('0: pending, 1: active, 2: block');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Indexes
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
