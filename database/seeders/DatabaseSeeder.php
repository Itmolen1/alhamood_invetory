<?php

namespace Database\Seeders;

use App\Models\CustomerAdvance;
use App\Models\Product;
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
        $this->call(CountrySeeder::class);
        $this->call(StateSeeder::class);
        $this->call(CitySeeder::class);
        $this->call(RegionSeeder::class);
        $this->call(UnitSeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(PurchaseSeeder::class);
        $this->call(PurchaseDetailSeeder::class);
        $this->call(ExpenseCategorySeeder::class);
        $this->call(EmployeeSeeder::class);
        $this->call(ExpenseSeeder::class);
        $this->call(ExpenseDetailSeeder::class);
        $this->call(SaleSeeder::class);
        $this->call(SaleDetailSeeder::class);
        $this->call(MeterReaderSeeder::class);
        $this->call(MeterReaderSeeder::class);
        $this->call(MeterReadingSeeder::class);
    }
}

