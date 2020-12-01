<?php

namespace Database\Factories;

use App\Models\CustomerPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerPriceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CustomerPrice::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'Rate' => $this->faker->numberBetween(5,10),
            'VAT' => 5,
            'user_id' => $this->faker->numberBetween(1,3),
            'customer_id' => $this->faker->numberBetween(1,3),
            'company_id' => $this->faker->numberBetween(1,3),
            'Description' => $this->faker->text,
        ];
    }
}
