<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Use raw SQL to modify column to TINYINT (no need to drop/recreate index)
        DB::statement('ALTER TABLE users MODIFY status TINYINT UNSIGNED DEFAULT 0 COMMENT "0: pending, 1: active, 2: blocked"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert to ENUM using raw SQL
        DB::statement('ALTER TABLE users MODIFY status ENUM("0", "1", "2") DEFAULT "0" COMMENT "0: pending, 1: active, 2: block"');
    }
};
