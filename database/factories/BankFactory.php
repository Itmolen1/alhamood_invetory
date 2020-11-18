<?php

namespace Database\Factories;

use App\Models\Bank;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Bank::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'Name' => $this->faker->name,
            'Branch' => $this->faker->address,
            'Description' => $this->faker->text,
            'Address' => $this->faker->address,
            'user_id' => $this->faker->numberBetween(1.3),
            'company_id' => $this->faker->numberBetween(1.3),
        ];
    }
}
