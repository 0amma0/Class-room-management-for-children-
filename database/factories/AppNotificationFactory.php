<?php

namespace Database\Factories;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AppNotification>
 */
class AppNotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => 'reflection',
            'teacher_reflection_id' => null,
            'public_download_request_id' => null,
            'title' => fake()->sentence(4),
            'message' => fake()->sentence(),
            'read_at' => null,
        ];
    }
}
