<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('pet_policy', 20)->default('not_allowed')->after('description');
            $table->text('pet_policy_note')->nullable()->after('pet_policy');

            $table->time('standard_checkin_start')->default('14:00:00')->after('pet_policy_note');
            $table->time('standard_checkout_end')->default('12:00:00')->after('standard_checkin_start');
            $table->string('checkin_method', 30)->default('meet_host')->after('standard_checkout_end');

            $table->boolean('smoking_allowed')->default(false)->after('checkin_method');
            $table->boolean('parties_allowed')->default(false)->after('smoking_allowed');
            $table->time('quiet_hours_start')->nullable()->after('parties_allowed');
            $table->time('quiet_hours_end')->nullable()->after('quiet_hours_start');

            $table->boolean('has_elevator')->default(false)->after('quiet_hours_end');
            $table->boolean('has_step_free_access')->default(false)->after('has_elevator');
            $table->boolean('is_ground_floor')->default(false)->after('has_step_free_access');
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->unsignedInteger('base_people')->default(2)->after('people');
            $table->decimal('extra_people_fee', 12, 2)->default(0)->after('base_people');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'pet_policy',
                'pet_policy_note',
                'standard_checkin_start',
                'standard_checkout_end',
                'checkin_method',
                'smoking_allowed',
                'parties_allowed',
                'quiet_hours_start',
                'quiet_hours_end',
                'has_elevator',
                'has_step_free_access',
                'is_ground_floor',
            ]);
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['base_people', 'extra_people_fee']);
        });
    }
};
