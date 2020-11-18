<?php

namespace Database\Seeders;

use App\Models\CustomerAdvance;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call(UserSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(CompanySeeder::class);
        $this->call(CustomerSeeder::class);
        $this->call(SupplierSeeder::class);
        $this->call(CustomerAdvanceSeeder::class);
        $this->call(SupplierSeeder::class);
        $this->call(SupplierAdvanceSeeder::class);
        $this->call(VehicleSeeder::class);
        $this->call(DriverSeeder::class);
        $this->call(BankSeeder::class);
    }
}

