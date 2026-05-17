<?php

namespace Database\Factories;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->company(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => '+1 555 '.$this->faker->numerify('### ####'),
            'status' => $this->faker->randomElement(LeadStatus::cases())->value,
            'sort_order' => 0,
        ];
    }
}
