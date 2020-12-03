<?php

namespace Database\Factories;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Expense::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'supplier_id' =>$this->faker->numberBetween(1,3),
            'user_id' =>$this->faker->numberBetween(1,3),
            'company_id' =>$this->faker->numberBetween(1,3),
            'Total' => $this->faker->numberBetween(1000, 100000),
            'subTotal' => $this->faker->numberBetween(1000, 100000),
            'totalVat' => $this->faker->numberBetween(1000, 100000),
            'grandTotal' => $this->faker->numberBetween(1000, 100000),
            'paidBalance' => $this->faker->numberBetween(1000, 100000),
            'remainingBalance' => $this->faker->numberBetween(1000, 100000),
        ];
    }
}
