<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomMaintenancesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        $records = [];

        for ($i = 1; $i <= 20; $i++) {
            $start = Carbon::now()->addDays(rand(-10, 20))->setTime(rand(8, 16), 0);
            $end = (clone $start)->addHours(rand(2, 6));

            $records[] = [
                'room_id' => (($i - 1) % 10) + 1,
                'property_id' => (($i - 1) % 5) + 1,
                'title' => 'Maintenance Task ' . $i,
                'description' => 'Routine maintenance task #' . $i . ' for tracking.',
                'maintenance_type' => rand(0, 1) ? 'scheduled' : 'emergency',
                'start_time' => $start,
                'end_time' => $end,
                'status' => collect(['planned', 'in_progress', 'completed', 'cancelled'])->random(),
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('room_maintenances')->insert($records);
    }
}
