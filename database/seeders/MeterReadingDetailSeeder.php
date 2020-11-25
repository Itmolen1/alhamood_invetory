<?php

namespace Database\Seeders;

use App\Models\MeterReadingDetail;
use Illuminate\Database\Seeder;

class MeterReadingDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        MeterReadingDetail::factory()->count(3)->create();
    }
}
