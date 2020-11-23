<?php

namespace Database\Seeders;

use App\Models\ExpenseDetail;
use Illuminate\Database\Seeder;

class ExpenseDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ExpenseDetail::factory()->count(3)->create();
    }
}
