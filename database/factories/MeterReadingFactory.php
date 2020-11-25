<?php

namespace Database\Factories;

use App\Models\MeterReading;
use Illuminate\Database\Eloquent\Factories\Factory;

class MeterReadingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MeterReading::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'startPad' => $this->faker->numberBetween(100,10000),
            'endPad' => $this->faker->numberBetween(100,10000),
            'totalPadSale' => $this->faker->numberBetween(100,10000),
            'totalMeterSale' => $this->faker->numberBetween(100,10000),
            'saleDifference' => $this->faker->numberBetween(100,10000),
            'user_id' => $this->faker->numberBetween(1,3),
            'company_id' => $this->faker->numberBetween(1,3),
        ];
    }
}
