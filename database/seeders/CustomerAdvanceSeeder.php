<?php

namespace Database\Seeders;

use App\Models\CustomerAdvance;
use Illuminate\Database\Seeder;

class CustomerAdvanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CustomerAdvance::factory()->count(3)->create();
    }
}
