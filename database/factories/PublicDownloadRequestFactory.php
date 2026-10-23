<?php

namespace Database\Factories;

use App\Models\PublicDownloadRequest;
use App\Models\LearningMaterial;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PublicDownloadRequest>
 */
class PublicDownloadRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'learning_material_id' => LearningMaterial::factory(),
            'requester_name' => fake()->name(),
            'requester_email' => fake()->safeEmail(),
            'access_token_hash' => hash('sha256', Str::random(64)),
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_note' => null,
        ];
    }
}
