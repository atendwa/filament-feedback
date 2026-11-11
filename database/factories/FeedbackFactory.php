<?php

declare(strict_types=1);

namespace Atendwa\Feedback\Database\Factories;

use Atendwa\Feedback\Models\Feedback;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feedback>
 */
class FeedbackFactory extends Factory
{
    protected $model = Feedback::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category' => 'General Feedback',
            'title' => fake()->sentence(4),
            'message' => fake()->paragraph(),
        ];
    }

    public function anonymous(): static
    {
        return $this->state(['is_anonymous' => true]);
    }

    public function repliable(?string $email = null): static
    {
        return $this->state(['wants_replies' => true, 'contact_email' => $email]);
    }
}
