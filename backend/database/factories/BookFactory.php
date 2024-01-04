<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $randomUserId = User::inRandomOrder()->first()->id;
        return [
            // 'title' => $this->faker->realText($maxNbChars = 15),
            'title' => "ワンピース",
            'user_id' => $randomUserId,
            'url' => $this->faker->email,
            'detail' => $this->faker->realText($maxNbChars = 15),
            'review' => $this->faker->realText($maxNbChars = 35),
            'reviewer' => User::findOrFail($randomUserId)->name,
        ];
    }
}
