<?php

namespace Database\Factories;

use App\Models\MeterReadingDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

class MeterReadingDetailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MeterReadingDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'startReading' => $this->faker->numberBetween(10,10000),
            'endReading' => $this->faker->numberBetween(10,10000),
            'netReading' => $this->faker->numberBetween(10,10000),
            'Purchases' => $this->faker->numberBetween(10,10000),
            'Sales' => $this->faker->numberBetween(10,10000),
            'user_id' => $this->faker->numberBetween(1,3),
            'company_id' => $this->faker->numberBetween(1,3),
            'meter_reader_id' => $this->faker->numberBetween(1,3),
        ];
    }
}
