<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleResource;
use App\Http\Resources\AuthorResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\NewsSourceResource;
use App\Http\Resources\UserResource;
use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\NewsSource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ArticleController extends Controller
{
    /**
     * @var array $data An array containing the default values for an article.
     * 
     * @property string $title The title of the article.
     * @property string $description A brief description of the article.
     * @property string $url The URL to the full article.
     * @property string $urlToImage The URL to the image associated with the article.
     * @property string $publishedAt The publication date of the article.
     * @property string $content The main content of the article.
     * @property string $provider The provider of the article.
     * @property string $news_source_id The ID of the news source.
     */
    protected $data = [
        'title' => '',
        'description' => '',
        'url' => '',
        'urlToImage' => '',
        'publishedAt' => '',
        'content' => '',
        'provider' => '',
        'news_source_id' => '',
    ];


    /**
     * Fetches articles from multiple sources.
     *
     * This method retrieves articles from the following sources:
     * - News API
     * - The Guardian
     * - The New York Times
     *
     * It calls the following methods:
     * - fetchNewsAPIArticles()
     * - fetchGuardianArticles()
     * - fetchNYTArticles()
     *
     * @return void
     */
    public function getArticles()
    {
        $this->fetchNewsAPIArticles();
        $this->fetchGuardianArticles();
        $this->fetchNYTArticles();
    }

    /**
     * Display a listing of the articles.
     *
     * This method handles the retrieval of articles from the database with optional filters
     * for search, provider, source, and date range. The results are cached for 6 minutes
     * to improve performance.
     *
     * @param \Illuminate\Http\Request $request The incoming request instance containing query parameters.
     * 
     * @return \Illuminate\Http\JsonResponse A JSON response containing the paginated list of articles.
     *
     * Query Parameters:
     * - search (string): A keyword to search in the title, author, and description of articles.
     * - provider (string): A keyword to filter articles by provider.
     * - source (string): A keyword to filter articles by source name.
     * - from (string): The start date to filter articles by published date.
     * - to (string): The end date to filter articles by published date.
     */
    public function index(Request $request)
    {
        /**
         * Generate a unique cache key and retrieve/store articles in cache.
         */
        $cacheKey = 'articles_' . md5(json_encode($request->all()));

        $articles = Cache::remember($cacheKey, 6, function () use ($request) {
            $query = Article::with(['source', 'categories', 'authors']);

            $filters = [
                'search' => fn($q, $value) => $q->where(function ($q) use ($value) {
                    $q->where('title', 'like', "%{$value}%")
                        ->orWhere('description', 'like', "%{$value}%")
                        ->orWhere('content', 'like', "%{$value}%");
                })->orWhereHas('authors', fn($authorQuery) => 
                    $authorQuery->where('name', 'like', "%{$value}%")
                ),
                'provider' => fn($q, $value) => $q->where('provider', 'like', "%{$value}%"),
                'source' => fn($q, $value) => $q->whereHas('source', fn($sourceQuery) =>
                    $sourceQuery->where('name', 'like', "%{$value}%")
                ),
                'categories' => fn($q, $value) => $q->whereHas('categories', fn($categoryQuery) =>
                    $categoryQuery->whereIn('name', (array) $value)
                ),
                'from' => fn($q, $value) => $q->where('publishedAt', '>=', $value),
                'to' => fn($q, $value) => $q->where('publishedAt', '<=', $value),
                'date_range' => fn($q, $value) => $q->whereBetween('publishedAt', $value),
            ];

            foreach ($filters as $key => $callback) {
                if ($key === 'date_range' && $request->filled(['from', 'to'])) {
                    $callback($query, [$request->input('from'), $request->input('to')]);
                } elseif ($request->filled($key)) {
                    $callback($query, $request->input($key));
                }
            }

            return $query->paginate(100);
        });

        if ($articles->isEmpty()) {
            return response()->json(['message' => 'No articles found'], 404);
        }

        $articles->getCollection()->transform(fn($article) => new ArticleResource([
            'id' => $article->id,
            'title' => $article->title,
            'description' => $article->description,
            'url' => $article->url,
            'publishedAt' => $article->publishedAt,
            'urlToImage' => $article->urlToImage,
            'provider' => $article->provider,
            'news_source' => $article->source->name,
            'categories' => $article->categories->pluck('name'),
            'authors' => $article->authors->pluck('name'),
        ]));

        return response()->json($articles);
    }

    /**
     * Display the specified article.
     */
    public function show($id)
    {
        $cacheKey = "article_{$id}";
        $article = Cache::remember($cacheKey, 60, function () use ($id) {
            return Article::with(['authors', 'categories', 'source'])->find($id);
        });

        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }

        return response()->json(new ArticleResource($article));
    }


    /**
     * Fetches articles from the News API and processes them.
     *
     * This function retrieves articles from the News API using the configured URL and API key.
     * It then processes each article by validating it, creating or finding the associated news source,
     * category, and authors, and finally creating a new article record in the database.
     * The new article is then associated with the relevant category and authors.
     *
     * @return void
     */
    private function fetchNewsAPIArticles()
    {
        $url = env('NEWS_API_KEY_URL');
        $response = Http::get($url);

        if (!$response->successful()) {
            return;
        }

        $articles = $response->json()['articles'] ?? [];

        foreach ($articles as $article) {
            if ($this->isValidNewsAPIArticle($article)) {
                $source = NewsSource::firstOrCreate(['name' => $article['source']['name']]);
                $category = $this->getOrCreateCategory($article['category'] ?? 'general');
                $authors = $this->parseAuthors($article['author'] ?? 'Anonymous');

                $this->data = [
                    'title' => $article['title'],
                    'description' => $article['description'],
                    'url' => $article['url'],
                    'urlToImage' => $article['urlToImage'],
                    'publishedAt' => $article['publishedAt'],
                    'content' => $article['content'],
                    'provider' => 'newsapi',
                    'news_source_id' => $source->id,
                ];

                $newArticle = $this->createArticle($this->data);

                $newArticle->categories()->attach($category->id);
                $this->attachAuthors($newArticle, $authors);
            }

        }
    }

    /**
     * Fetches articles from The Guardian API and processes them.
     *
     * This function retrieves articles from The Guardian API using the configured URL and API key.
     * It then processes each article, creating or updating the corresponding news source, category,
     * and authors in the database. The articles are then saved with the appropriate relationships.
     *
     * @return void
     */
    private function fetchGuardianArticles()
    {
        $url = env('GAURDIAN_API_KEY_URL');
        $response = Http::get($url);

        if (!$response->successful()) {
            return;
        }

        $articles = $response->json()['response']['results'] ?? [];

        foreach ($articles as $article) {
            if (isset($article['webTitle'], $article['webUrl'], $article['webPublicationDate'], $article['sectionName'])) {
                $source = NewsSource::firstOrCreate(['name' => $article['pillarName'] ?? 'The Guardian']);
                $category = $this->getOrCreateCategory($article['sectionName']);
                $authors = $this->parseAuthors($article['author'] ?? 'Anonymous');

                $this->data = [
                    'title' => $article['webTitle'],
                    'description' => $article['webTitle'],
                    'url' => $article['webUrl'],
                    'urlToImage' => '',
                    'publishedAt' => $article['webPublicationDate'],
                    'content' => $article['webTitle'],
                    'provider' => 'The Guardian',
                    'news_source_id' => $source->id
                ];
                $newArticle = $this->createArticle($this->data);

                $newArticle->categories()->attach($category->id);
                $this->attachAuthors($newArticle, $authors);
            }
        }
    }

    /**
     * Fetches articles from the New York Times (NYT) API, parses the XML response,
     * and stores the articles in the database. Each article is associated with a
     * news source, authors, and categories.
     *
     * @return void
     */
    private function fetchNYTArticles()
    {
        $url = env('NYT_URL');
        $response = Http::get($url);

        if (!$response->successful()) {
            return;
        }

        $xml = simplexml_load_string($response->body());
        $articles = $xml->channel->item ?? [];

        foreach ($articles as $article) {
            $source = NewsSource::firstOrCreate(['name' => $article->source ?? 'New York Times']);
            $authors = $this->parseAuthors($article->author ?? 'Anonymous');

            $this->data = [
                'title' => $article->title,
                'description' => $article->description,
                'url' => $article->link,
                'urlToImage' => '',
                'publishedAt' => $article->pubDate,
                'content' => $article->description,
                'provider' => 'New York Times',
                'news_source_id' => $source->id
            ];

            $newArticle = $this->createArticle($this->data);

            foreach ($article->category as $categoryName) {
                $category = $this->getOrCreateCategory($categoryName);
                $newArticle->categories()->attach($category->id);
            }

            $this->attachAuthors($newArticle, $authors);
        }
    }

    /**
     * Check if the given article array has all the required fields for a valid News API article.
     *
     * The required fields are:
     * - 'title'
     * - 'author'
     * - 'description'
     * - 'url'
     * - 'source' (which must be an array containing 'name')
     *
     * @param array $article The article array to validate.
     * @return bool Returns true if the article has all the required fields, false otherwise.
     */
    private function isValidNewsAPIArticle($article)
    {
        return isset($article['title'], $article['author'], $article['description'], $article['url'], $article['source']['name']);
    }

    /**
     * Creates a new article with the provided data and additional fields.
     *
     * @param array $data The data for the article, which may include 'title', 'webTitle', 'description', 'url', 'webUrl', 'urlToImage', and 'publishedAt' or 'webPublicationDate'.
     * @param array $additionalFields Optional additional fields to be included in the article creation.
     * @return \Illuminate\Database\Eloquent\Model The created Article model instance.
     */
    private function createArticle($data)
    {
        return Article::create([
            'title' => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'url' => $data['url'] ?? '',
            'urlToImage' => $data['urlToImage'] ?? '',
            'publishedAt' => $data['publishedAt'] ?? '',
            'content' => $data['content'] ?? '',
            'provider' => $data['provider'] ?? '',
            'news_source_id' => $data['news_source_id'] ?? '',
        ]);
    }

    /**
     * Retrieve an existing category by name or create a new one if it doesn't exist.
     *
     * @param string $categoryName The name of the category to retrieve or create.
     * @return \App\Models\Category The retrieved or newly created category instance.
     */
    private function getOrCreateCategory($categoryName)
    {
        return Category::firstOrCreate(['name' => $categoryName]);
    }

    /**
     * Parses a string of authors into an array of trimmed author names.
     *
     * If the input is not already an array, it will be split by commas and each
     * author name will be trimmed of surrounding whitespace.
     *
     * @param array|string $authors A string of comma-separated author names or an array of author names.
     * @return array An array of trimmed author names.
     */
    private function parseAuthors($authors)
    {
        if (!is_array($authors)) {
            $authors = array_map('trim', explode(',', $authors));
        }
        return $authors;
    }

    /**
     * Attach authors to the given article.
     *
     * This method takes an article and an array of author names. For each author name,
     * it checks if the author already exists in the database. If the author does not exist,
     * it creates a new author record. Then, it attaches the author to the article.
     *
     * @param \App\Models\Article $article The article to which authors will be attached.
     * @param array $authors An array of author names to be attached to the article.
     * @return void
     */
    private function attachAuthors($article, $authors)
    {
        foreach ($authors as $authorName) {
            if (!empty($authorName)) {
                $author = Author::firstOrCreate(['name' => $authorName]);
                $article->authors()->attach($author->id);
            }
        }
    }

    /**
     * Retrieve articles based on the user's preferences.
     *
     * This method fetches the authenticated user and loads their preferred categories,
     * authors, and sources. It then retrieves articles that match any of these preferences.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function preferences(Request $request)
    {
        $userId = Auth::id();
        $cacheKey = "user_{$userId}_preferences";

        return Cache::remember($cacheKey, 1, function () use ($userId, $request) {
            $user = User::findOrFail($userId)->load('preferredCategories', 'preferredAuthors', 'preferredSources');

            $categoryIds = $user->preferredCategories->pluck('id');
            $authorIds = $user->preferredAuthors->pluck('id');
            $sourceIds = $user->preferredSources->pluck('id');

            $query = Article::query();

            if ($request->has('source')) {
                $sourceName = $request->input('source');
                $sourceIds = NewsSource::where('name', $sourceName)->pluck('id');

                if ($sourceIds->isEmpty()) {
                    return response()->json(['message' => 'No articles found for the specified source'], 404);
                }

                $query->whereHas('source', fn($q) => $q->whereIn('news_sources.id', $sourceIds));
            }

            if ($request->has('category')) {
                $categoryName = $request->input('category');
                $categoryIds = Category::where('name', $categoryName)->pluck('id');

                if ($categoryIds->isEmpty()) {
                    return response()->json(['message' => 'No articles found for the specified category'], 404);
                }

                $query->whereHas('categories', fn($q) => $q->whereIn('categories.id', $categoryIds));
            }

            if ($request->has('author')) {
                $authorName = $request->input('author');
                $authorIds = Author::where('name', $authorName)->pluck('id');

                if ($authorIds->isEmpty()) {
                    return response()->json(['message' => 'No articles found for the specified author'], 404);
                }

                $query->whereHas('authors', fn($q) => $q->whereIn('authors.id', $authorIds));
            }

            if (!$request->has(['source', 'category', 'author'])) {
                $query->where(function ($q) use ($categoryIds, $authorIds, $sourceIds) {
                    $q->whereHas('categories', fn($q) => $q->whereIn('categories.id', $categoryIds))
                        ->orWhereHas('authors', fn($q) => $q->whereIn('authors.id', $authorIds))
                        ->orWhereHas('source', fn($q) => $q->whereIn('news_sources.id', $sourceIds));
                });
            }

            return $query->with(['categories', 'authors', 'source'])->get();
        });
    }
    
    public function authors(){
        $authors = Cache::remember('authors', 5, function () {
            return Author::all();
        });

        return response()->json(new AuthorResource($authors));
    }

    public function categories(){
        $categories = Cache::remember('categories', 60, function () {
            return Category::all();
        });

        return response()->json(new CategoryResource($categories));
    }

    public function sources(){
        $sources = Cache::remember('sources', 60, function () {
            return NewsSource::all();
        });

        return response()->json(new NewsSourceResource($sources));
    }
}

