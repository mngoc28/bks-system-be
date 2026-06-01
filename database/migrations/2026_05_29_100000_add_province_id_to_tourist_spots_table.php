<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tourist_spots', function (Blueprint $table): void {
            $table->foreignId('province_id')
                ->nullable()
                ->after('region_label')
                ->constrained('provinces')
                ->nullOnDelete();
        });

        $provincesByName = DB::table('provinces')->pluck('id', 'name');

        DB::table('tourist_spots')
            ->select(['id', 'region_label'])
            ->orderBy('id')
            ->each(function (object $spot) use ($provincesByName): void {
                $label = trim((string) ($spot->region_label ?? ''));
                if ($label === '') {
                    return;
                }

                $provinceId = $provincesByName->get($label);
                if ($provinceId === null) {
                    return;
                }

                DB::table('tourist_spots')
                    ->where('id', $spot->id)
                    ->update(['province_id' => (int) $provinceId]);
            });

        Schema::table('tourist_spots', function (Blueprint $table): void {
            $table->index(['province_id', 'is_active'], 'idx_tourist_spots_province_active');
        });
    }

    public function down(): void
    {
        Schema::table('tourist_spots', function (Blueprint $table): void {
            $table->dropIndex('idx_tourist_spots_province_active');
            $table->dropConstrainedForeignId('province_id');
        });
    }
};
