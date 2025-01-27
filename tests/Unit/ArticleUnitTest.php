<?php

namespace Tests\Unit;

use App\Http\Controllers\ArticleController;
use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\NewsSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class ArticleUnitTest extends TestCase
{
    use RefreshDatabase;

    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new ArticleController();
    }

    
    public function test_it_can_fetch_articles_from_multiple_sources()
    {
        Http::fake([
            env('NEWS_API_KEY_URL') => Http::response([
                'articles' => [
                    [
                        'title' => 'Test Article',
                        'author' => 'John Doe',
                        'description' => 'Test Description',
                        'url' => 'http://test.com',
                        'source' => ['name' => 'Test Source'],
                        'urlToImage' => 'http://test.com/image.jpg',
                        'publishedAt' => '2024-01-27',
                        'content' => 'Test content',
                        'category' => 'technology'
                    ]
                ]
            ], 200),
            env('GAURDIAN_API_KEY_URL') => Http::response([
                'response' => [
                    'results' => [
                        [
                            'webTitle' => 'Guardian Article',
                            'webUrl' => 'http://guardian.com',
                            'webPublicationDate' => '2024-01-27',
                            'sectionName' => 'politics'
                        ]
                    ]
                ]
            ], 200),
            env('NYT_URL') => Http::response(
                '<?xml version="1.0"?>
                <rss>
                    <channel>
                        <item>
                            <title>NYT Article</title>
                            <description>NYT Description</description>
                            <link>http://nyt.com</link>
                            <pubDate>2024-01-27</pubDate>
                            <category>world</category>
                        </item>
                    </channel>
                </rss>',
                200
            )
        ]);

        $this->controller->getArticles();

        $this->assertDatabaseHas('articles', ['title' => 'Test Article']);
        $this->assertDatabaseHas('articles', ['title' => 'Guardian Article']);
        $this->assertDatabaseHas('articles', ['title' => 'NYT Article']);
    }

    
    public function test_it_can_list_articles_with_filters()
    {
        $source = NewsSource::factory()->create(['name' => 'Test Source']);
        $category = Category::factory()->create(['name' => 'technology']);
        $author = Author::factory()->create(['name' => 'John Doe']);
        
        $article = Article::factory()->create([
            'title' => 'Test Article',
            'description' => 'Test Description',
            'news_source_id' => $source->id,
            'publishedAt' => '2024-01-27'
        ]);

        $article->categories()->attach($category);
        $article->authors()->attach($author);

        $request = new Request([
            'search' => 'Test',
            'provider' => 'newsapi',
            'source' => 'Test Source',
            'from' => '2024-01-01',
            'to' => '2024-01-31'
        ]);

        $response = $this->controller->index($request);
        $this->assertEquals(200, $response->status());
        $this->assertNotEmpty($response->getData()->data);
    }

    
    public function test_it_returns_404_when_no_articles_found()
    {
        $request = new Request(['search' => 'NonexistentArticle']);
        $response = $this->controller->index($request);
        
        $this->assertEquals(404, $response->status());
    }

    
    public function test_it_can_show_single_article()
    {
        $article = Article::factory()->create();
        
        $response = $this->controller->show($article->id);
        
        $this->assertEquals(200, $response->status());
        $this->assertEquals($article->title, $response->getData()->title);
    }

    
    public function test_it_returns_404_for_nonexistent_article()
    {
        $response = $this->controller->show(999);
        $this->assertEquals(404, $response->status());
    }

    
    public function test_it_can_fetch_articles_based_on_user_preferences()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $author = Author::factory()->create();
        $source = NewsSource::factory()->create();

        $user->preferredCategories()->attach($category);
        $user->preferredAuthors()->attach($author);
        $user->preferredSources()->attach($source);

        Auth::login($user);

        $article = Article::factory()->create(['news_source_id' => $source->id]);
        $article->categories()->attach($category);
        $article->authors()->attach($author);

        $request = new Request();
        $response = $this->controller->preferences($request);

        $this->assertNotEmpty($response);
    }

    
    public function test_it_can_filter_preferences_by_source()
    {
        $user = User::factory()->create();
        $source = NewsSource::factory()->create(['name' => 'Test Source']);
        
        Auth::login($user);

        $article = Article::factory()->create(['news_source_id' => $source->id]);

        $request = new Request(['source' => 'Test Source']);
        $response = $this->controller->preferences($request);

        $this->assertNotEmpty($response);
    }

    
    public function test_it_can_list_all_authors()
    {
        Author::factory()->count(3)->create();

        $response = $this->controller->authors();
        
        $this->assertEquals(200, $response->status());
        $this->assertNotEmpty($response->getData());
    }

    
    public function test_it_can_list_all_categories()
    {
        Category::factory()->count(3)->create();

        $response = $this->controller->categories();
        
        $this->assertEquals(200, $response->status());
        $this->assertNotEmpty($response->getData());
    }

    
    public function test_it_can_list_all_sources()
    {
        NewsSource::factory()->count(3)->create();

        $response = $this->controller->sources();
        
        $this->assertEquals(200, $response->status());
        $this->assertNotEmpty($response->getData());
    }

    
    public function test_it_caches_article_results()
    {
        Article::factory()->create();
        
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn(Article::paginate(10));

        $request = new Request();
        $this->controller->index($request);
    }

    
    public function test_it_handles_invalid_news_api_response()
    {
        Http::fake([
            env('NEWS_API_KEY_URL') => Http::response([], 500)
        ]);

        $this->controller->getArticles();
        
        $this->assertDatabaseMissing('articles', ['provider' => 'newsapi']);
    }

    
    public function test_it_handles_invalid_guardian_response()
    {
        Http::fake([
            env('GAURDIAN_API_KEY_URL') => Http::response([], 500)
        ]);

        $this->controller->getArticles();
        
        $this->assertDatabaseMissing('articles', ['provider' => 'The Guardian']);
    }

    
    public function test_it_handles_invalid_nyt_response()
    {
        Http::fake([
            env('NYT_URL') => Http::response([], 500)
        ]);

        $this->controller->getArticles();
        
        $this->assertDatabaseMissing('articles', ['provider' => 'New York Times']);
    }
}
