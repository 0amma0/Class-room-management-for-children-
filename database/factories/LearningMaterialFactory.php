<?php

namespace Database\Factories;

use App\Models\LearningMaterial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LearningMaterial>
 */
class LearningMaterialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(),
            'type' => 'document',
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'file_path' => null,
            'external_url' => null,
            'is_system' => false,
            'is_active' => true,
            'uploaded_by' => null,
        ];
    }
}
