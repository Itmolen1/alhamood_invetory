<?php

namespace Database\Factories;

use App\Models\CustomerAdvance;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerAdvanceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CustomerAdvance::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'customer_id' => $this->faker->numberBetween(0,3),
            'user_id' => $this->faker->numberBetween(0,3),
            'company_id' => $this->faker->numberBetween(0,3),
            'receiptNumber' => $this->faker->numberBetween(0,3),
            'paymentType' => $this->faker->name,
            'Amount' => $this->faker->numberBetween(12,2222),
            'receiverName' => $this->faker->name,
        ];
    }
}
