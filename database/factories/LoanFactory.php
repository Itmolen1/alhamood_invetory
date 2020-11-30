<?php

namespace Database\Factories;

use App\Models\Loan;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Loan::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'loanTo' => $this->faker->name,
            'Description' => $this->faker->text,
            'payLoan' => $this->faker->numberBetween(100,3000),
            'remainingLoan' => $this->faker->numberBetween(100,3000),
            'user_id' => $this->faker->numberBetween(1,3),
            'company_id' => $this->faker->numberBetween(1,3),
            'customer_id' => $this->faker->numberBetween(1,3),
        ];
    }
}
