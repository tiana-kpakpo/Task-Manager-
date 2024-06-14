<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $user = User::inRandomOrder()->first();

        return [
          'title' => fake()->sentence(),
          'description' => fake()->paragraph(),
          'priority' => 1,
          'category' => 'work',
            'due_date' => fake()->dateTime(),
        'user_id' => $user->id,
        'reminders' => fake()->dateTime()

        ];
    }
}
