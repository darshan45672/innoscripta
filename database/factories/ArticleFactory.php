<?php

namespace Database\Factories;

use App\Models\NewsSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'url' => $this->faker->url,
            'publishedAt' => $this->faker->dateTime,
            'urlToImage' => $this->faker->imageUrl(),
            'provider' => $this->faker->name,
            'content' => $this->faker->paragraph,
            'news_source_id' => NewsSource::factory()->create()->id,
        ];
    }
}
