<?php

namespace Database\Seeders;

use App\Models\CustomerPrice;
use Illuminate\Database\Seeder;

class CustomerPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CustomerPrice::factory()->count(3)->create();
    }
}
