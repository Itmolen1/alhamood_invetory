<?php

namespace Database\Factories;

use App\Models\ExpenseDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseDetailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ExpenseDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'expense_id' => $this->faker->numberBetween(1,3),
            'user_id' => $this->faker->numberBetween(1,3),
            'company_id' => $this->faker->numberBetween(1,3),
            'expense_category_id' => $this->faker->numberBetween(1,3),
            'Total' => $this->faker->numberBetween(10,100),
            'Vat' => $this->faker->numberBetween(10,100),
            'rowVatAmount' => $this->faker->numberBetween(10,100),
            'rowSubTotal' => $this->faker->numberBetween(10,100),
        ];
    }
}
