<?php

namespace Database\Seeders;

use App\Models\MeterReading;
use Illuminate\Database\Seeder;

class MeterReadingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        MeterReading::factory()->count(3)->create();
    }
}
