<?php

namespace Tests\Feature\Article;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GetArticlesTest extends TestCase
{
    use RefreshDatabase;
    public function test_can_retrieve_articles_successfully()
    {
        Article::factory()->count(10)->create();

        $response = $this->getJson(route('articles.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'url',
                        'publishedAt',
                        'urlToImage',
                        'provider',
                        'news_source',
                        'categories',
                        'authors'
                    ]
                ]
            ]);
    }

    public function test_can_filter_articles_by_search_term()
    {
        Article::factory()->create(['title' => 'Laravel Testing']);
        Article::factory()->create(['title' => 'React Development']);

        $response = $this->getJson(route('articles.index', ['search' => 'Laravel']));

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Laravel Testing'])
            ->assertJsonMissing(['title' => 'React Development']);
    }

    public function test_can_filter_articles_by_provider()
    {
        Article::factory()->create(['provider' => 'BBC']);
        Article::factory()->create(['provider' => 'CNN']);

        $response = $this->getJson(route('articles.index', ['provider' => 'BBC']));

        $response->assertStatus(200)
            ->assertJsonFragment(['provider' => 'BBC'])
            ->assertJsonMissing(['provider' => 'CNN']);
    }

    public function test_can_filter_articles_by_date_range()
    {
        Article::factory()->create(['publishedAt' => '2024-01-01']);
        Article::factory()->create(['publishedAt' => '2024-01-10']);

        $response = $this->getJson(route('articles.index', [
            'from' => '2024-01-01',
            'to' => '2024-01-05'
        ]));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_articles_are_paginated()
    {
        Article::factory()->count(120)->create();

        $response = $this->getJson(route('articles.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'current_page',
                'data',
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'links',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total',
            ]);
    }

    public function test_rate_limiting_is_enforced()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        for ($i = 0; $i < 10; $i++) {
            $this->getJson(route('articles.index'));
        }

        $response = $this->getJson(route('articles.index'));

        $response->assertStatus(429);
    }

    public function test_returns_empty_response_when_no_articles_found()
    {
        $response = $this->getJson(route('articles.index'));

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'No articles found'
            ]);
    }

    public function test_ignores_invalid_filter_parameters()
    {
        Article::factory()->count(10)->create();

        $response = $this->getJson(route('articles.index', ['invalid_filter' => 'value']));

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_filter_articles_by_from_date_only()
    {
        Article::factory()->create(['publishedAt' => '2024-01-01']);
        Article::factory()->create(['publishedAt' => '2024-01-10']);

        $response = $this->getJson(route('articles.index', ['from' => '2024-01-01']));

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_articles_by_to_date_only()
    {
        Article::factory()->create(['publishedAt' => '2024-01-01']);
        Article::factory()->create(['publishedAt' => '2024-01-10']);

        $response = $this->getJson(route('articles.index', ['to' => '2024-01-05']));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
