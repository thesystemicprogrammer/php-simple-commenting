<?php

namespace Database\Factories;

use App\Models\Approval;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApprovalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Approval::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'approval_hash' => hash('sha256', $this->faker->url()),
            'comment_id' => $this->faker->numberBetween(0, 10000),
        ];
    }
}
