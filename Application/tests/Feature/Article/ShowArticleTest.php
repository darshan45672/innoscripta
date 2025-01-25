<?php

namespace Tests\Feature\Article;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShowArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_retrieve_article_by_id()
    {
        $article = Article::factory()->create();

        $response = $this->getJson(route('articles.show', $article->id));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'title',
                'description',
                'url',
                'content',
                'publishedAt',
                'urlToImage',
                'provider',
                'news_source_id',
                'categories',
                'authors',
                'source'
            ]);
    }

    public function test_returns_404_when_article_not_found()
    {
        $response = $this->getJson(route('articles.show', 9999)); // Non-existing ID

        $response->assertStatus(404)
            ->assertJson(['message' => 'Article not found']);
    }

    public function test_article_is_cached()
    {
        $article = Article::factory()->create();

        $this->getJson(route('articles.show', $article->id));

        $this->assertTrue(Cache::has("article_{$article->id}"));
    }

    public function test_article_is_not_cached_for_multiple_requests()
    {
        $article = Article::factory()->create();

        $this->getJson(route('articles.show', $article->id));

        Cache::forget("article_{$article->id}");

        $this->getJson(route('articles.show', $article->id));

        $this->assertTrue(Cache::has("article_{$article->id}"));
    }

    public function test_rate_limiting_is_enforced()
    {
        $article = Article::factory()->create();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        for ($i = 0; $i < 10; $i++) {
            $this->getJson(route('articles.show', $article->id));
        }

        $response = $this->getJson(route('articles.show', $article->id));

        $response->assertStatus(429);
    }
}
