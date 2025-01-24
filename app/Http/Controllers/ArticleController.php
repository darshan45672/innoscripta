<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\NewsSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ArticleController extends Controller
{
    /**
     * Fetches articles from multiple news sources and stores them in the database.
     *
     * This method retrieves articles from three different news sources: NewsAPI, The Guardian, and The New York Times.
     * It processes the fetched articles and stores them in the `articles` table.
     *
     * @return void
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getArticles()
    {
        $url = env('NEWS_API_URL') . env('NEWS_API_KEY');
        $response = Http::get($url);
        $fetchedArticles = $response->json()['articles'];

        // dd($fetchedArticles);
        foreach ($fetchedArticles as $article) {
            if (isset($article['title'], $article['author'], $article['description'], $article['url'], $article['source']['name'])) {
                // dd($article['source']['name']);
                $source = NewsSource::firstOrCreate(['name' => $article['source']['name']]);
                // dd($source->id);
                $newArticle = Article::create([
                    'title' => $article['title'],
                    'description' => $article['description'],
                    'url' => $article['url'],
                    'urlToImage' => $article['urlToImage'] ?? '',
                    'publishedAt' => $article['publishedAt'],
                    'content' => $article['content'] ?? '',
                    'provider' => 'newsapi',
                    'news_source_id' => $source->id,
                ]);

                $categoryName = isset($article['category']) && !empty($article['category'])
                    ? $article['category']
                    : 'general';

                $category = Category::firstOrCreate(['name' => $categoryName]);

                $newArticle->categories()->attach($category->id);

                $authors = $article['author'] ?? 'Anonymous';


                if (!is_array($authors)) {
                    $authors = array_map('trim', explode(',', $authors));
                }

                foreach ($authors as $authorName) {
                    if (!empty($authorName)) {
                        $author = Author::firstOrCreate(['name' => $authorName]);
                        $newArticle->authors()->attach($author->id);
                    }
                }
            }
        }

        $url = env('GAURDIAN_API_URL') . env('GAURDIAN_API_KEY');
        $response = Http::get($url);

        $fetchedArticles = $response->json()['response']['results'];

        foreach ($fetchedArticles as $article) {
            if (isset($article['webTitle'], $article['webUrl'], $article['webPublicationDate'], $article['sectionName'])) {
                $category = Category::firstOrCreate(
                    ['name' => $article['sectionName'] ?? 'general'],
                );


                if (isset($article['author'])) {
                    if (!is_array($article['author'])) {
                        $article['author'] = array_map('trim', explode(',', $article['author']));
                    }
                }

                $authors = Author::firstOrCreate(
                    ['name' => $article['author'] ?? 'Anonymous'],
                );

                $source = NewsSource::firstOrCreate(['name' => $article['pillarName'] ?? 'The Guardian']);

                $newArticle = Article::create([
                    'title' => $article['webTitle'],
                    'description' => $article['webTitle'],
                    'url' => $article['webUrl'],
                    'publishedAt' => $article['webPublicationDate'],
                    'source_name' => $article['sectionName'],
                    'provider' => 'guardian',
                    'news_source_id' => $source->id,
                ]);

                $newArticle->categories()->attach($category->id);

                $newArticle->authors()->attach($authors);
            }
        }

        $url = env('NYT_URL');
        $response = Http::get($url);
        $xml = simplexml_load_string($response->body());

        $items = (array) $xml->channel;

        // dd($items);

        foreach ($items['item'] as $article) {
            $source = NewsSource::firstOrCreate(['name' => $article['source'] ?? 'New York Times']);
            if (isset($article->title, $article->link, $article->pubDate)) {
                $newArticle = Article::create([
                    'title' => $article->title,
                    'description' => $article->description,
                    'url' => $article->link,
                    'publishedAt' => $article->pubDate,
                    'source_name' => 'New York Times',
                    'provider' => 'nytimes',
                    'news_source_id' => $source->id,
                ]);
            }
            if (isset($article->category)) {
                foreach ($article->category as $categoryName) {
                    $category = Category::firstOrCreate(
                        ['name' => $categoryName],
                    );

                    $newArticle->categories()->attach($category->id);
                }
            }
            $authors = Author::firstOrCreate(
                ['name' => $article['author'] ?? 'Anonymous'],
            );
            $newArticle->authors()->attach($authors);

        }
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
         * Generates a unique cache key based on the request parameters and stores/retrieves
         * the articles from the cache.
         *
         * @param \Illuminate\Http\Request $request The incoming request object containing all parameters.
         * @return mixed The cached articles or the result of the callback function if not cached.
         */
        $cacheKey = 'articles_' . md5(json_encode($request->all()));

        $articles = Cache::remember($cacheKey, 6, function () use ($request) {

            $query = Article::query();

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('author', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->filled('provider')) {
                $provider = $request->input('provider');
                $query->where('provider', 'like', "%{$provider}%");
            }

            if ($request->filled('source')) {
                $source = $request->input('source');
                $query->where('source_name', 'like', "%{$source}%");
            }

            if ($request->filled('from') && $request->filled('to')) {
                $from = $request->input('from');
                $to = $request->input('to');
                $query->whereBetween('publishedAt', [$from, $to]);
            } elseif ($request->filled('from')) {
                $from = $request->input('from');
                $query->where('publishedAt', '>=', $from);
            } elseif ($request->filled('to')) {
                $to = $request->input('to');
                $query->where('publishedAt', '<=', $to);
            }

            return $query->paginate(100);
        });

        $articles->getCollection()->transform(function ($article) {
            return [
                'id' => $article->id,
                'title' => $article->title,
                'author' => $article->author,
                'description' => $article->description,
                'url' => $article->url,
                'publishedAt' => $article->publishedAt,
                'source_name' => $article->source_name,
                'provider' => $article->provider,
                'categories' => $article->categories->pluck('name') // Get category names
            ];
        });

        return response()->json($articles);

    }

    /**
     * Display the specified article.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $article = Article::find($id);
        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }
        $article = new ArticleResource($article);
        return response()->json($article);
    }
}
