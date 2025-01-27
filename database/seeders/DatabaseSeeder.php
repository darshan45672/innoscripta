<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\NewsSource;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Darshan Bhandary',
            'email' => 'drshnbhandary@gmail.com',
        ]);

        $categories = [
            Category::factory()->create(['name' => 'General']),
            Category::factory()->create(['name' => 'Technology']),
            Category::factory()->create(['name' => 'Business']),
            Category::factory()->create(['name' => 'Sports']),
        ];

        $authors = [
            Author::factory()->create(['name' => 'Pappu']),
            Author::factory()->create(['name' => 'John Doe']),
            Author::factory()->create(['name' => 'Jane Smith']),
        ];

        $newsSources = [
            NewsSource::factory()->create(['name' => 'Pappu Times']),
            NewsSource::factory()->create(['name' => 'Tech Daily']),
            NewsSource::factory()->create(['name' => 'Business Weekly']),
        ];

        for ($i = 0; $i < 2000; $i++) {
            $si = $i + 1;
            $article = Article::factory()->create([
                'news_source_id' => $newsSources[array_rand($newsSources)]->id,
                'title' => "Article {$si}",
                'publishedAt' => now()->subDays(rand(1, 30)),
            ]);

            $randomCategories = collect($categories)->random(rand(1, 3));
            $article->categories()->attach($randomCategories->pluck('id'));

            $randomAuthors = collect($authors)->random(rand(1, 2));
            $article->authors()->attach($randomAuthors->pluck('id'));
        }
}}
