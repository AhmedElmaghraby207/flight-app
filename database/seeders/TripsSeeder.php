<?php

namespace Database\Seeders;

use App\Models\Trip;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TripsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Trip::all()->count() == 0) {
            DB::table('trips')->insert([
                [
                    'origin_city' => 'cairo',
                    'destination_city' => 'tokyo',
                    'price' => 12000,
                    'take_off_time' => '2020-07-26 15:00:00',
                    'landing_time' => '2020-07-27 02:00:00',
                ],
                [
                    'origin_city' => 'cairo',
                    'destination_city' => 'dubai',
                    'price' => 3000,
                    'take_off_time' => '2020-07-26 18:00:00',
                    'landing_time' => '2020-07-26 21:00:00',
                ],
                [
                    'origin_city' => 'dubai',
                    'destination_city' => 'tokyo',
                    'price' => 6000,
                    'take_off_time' => '2020-07-26 23:00:00',
                    'landing_time' => '2020-07-27 11:00:00',
                ],
            ]);
        }
    }
}
