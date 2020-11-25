<?php

namespace Database\Seeders;

use App\Models\MeterReader;
use Illuminate\Database\Seeder;

class MeterReaderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        MeterReader::factory()->count(3)->create();
    }
}
