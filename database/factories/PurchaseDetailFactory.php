<?php

namespace Database\Factories;

use App\Models\PurchaseDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseDetailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PurchaseDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'purchase_id' => $this->faker->numberBetween(0,3),
            'user_id' => $this->faker->numberBetween(0,3),
            'company_id' => $this->faker->numberBetween(0,3),
            'product_id' => $this->faker->numberBetween(0,3),
            'Quantity' => $this->faker->numberBetween(10,100),
            'Price' => $this->faker->numberBetween(10,100),
            'rowTotal' => $this->faker->numberBetween(10,100),
            'Vat' => $this->faker->numberBetween(10,100),
            'rowVatAmount' => $this->faker->numberBetween(10,100),
            'rowSubTotal' => $this->faker->numberBetween(10,100),
        ];
    }
}
