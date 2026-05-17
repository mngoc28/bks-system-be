<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('bookings', 'booking_code')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->string('booking_code', 32)->nullable()->unique()->after('id');
            });
        }

        DB::table('bookings')
            ->orderBy('id')
            ->where(function ($q) {
                $q->whereNull('booking_code')->orWhere('booking_code', '=', '');
            })
            ->orderBy('id')
            ->chunkById(100, function ($rows): void {
                foreach ($rows as $row) {
                    $created = $row->created_at ?? $row->updated_at ?? now()->toDateTimeString();
                    $year = (int) Carbon::parse((string) $created)->format('Y');
                    DB::table('bookings')->where('id', $row->id)->update([
                        'booking_code' => sprintf('RM-%04d-%06d', $year, (int) $row->id),
                    ]);
                }
            });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('bookings', 'booking_code')) {
            return;
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique(['booking_code']);
            $table->dropColumn('booking_code');
        });
    }
};
