<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Supplier::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'Name' => $this->faker->company,
            'Representative' => $this->faker->name,
            'company_type_id' => $this->faker->numberBetween(1,3),
            'payment_type_id' => $this->faker->numberBetween(1,3),
            'payment_term_id' => $this->faker->numberBetween(1,3),
            'Phone' => $this->faker->numberBetween(10000,122222),
            'Mobile' => $this->faker->numberBetween(10000,122222),
            'Address' => $this->faker->address,
            'postCode' => $this->faker->postcode,
            'Description' => $this->faker->text,
            'user_id' => $this->faker->numberBetween(0,3),
            'region_id' => $this->faker->numberBetween(0,3),
        ];
    }
}
