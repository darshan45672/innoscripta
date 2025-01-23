<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ArticleController extends Controller
{
    public function getArticles()
    {
        $url = env('NEWS_API_URL') .env('NEWS_API_KEY');
        $response = Http::get($url);
        // $response = Http::get('https://content.guardianapis.com/search?api-key=5a80c638-06d2-4e55-b23f-bf6620525c25');
        $fetchedArticles = $response->json()['articles'];
        $count = count($fetchedArticles);

        foreach ($fetchedArticles as $article) {
            if (isset($article['title'], $article['author'], $article['description'], $article['url'], $article['source']['name'])) {
                Article::create([
                    'title' => $article['title'],
                    'author' => $article['author'] ?? 'Unknown',
                    'description' => $article['description'],
                    'url' => $article['url'],
                    'urlToImage' => $article['urlToImage'] ?? '',
                    'publishedAt' => $article['publishedAt'],
                    'content' => $article['content'] ?? '',
                    'source_name' => $article['source']['name'] ?? 'Unknown',
                    'provider' => 'newsapi',
                ]);
            }
        }
        
         $response = Http::get('https://content.guardianapis.com/search?api-key=5a80c638-06d2-4e55-b23f-bf6620525c25');

        $fetchedArticles = $response->json()['response']['results'];
        // dd($fetchedArticles);
        foreach ($fetchedArticles as $article) {
            if (isset($article['webTitle'], $article['webUrl'], $article['webPublicationDate'], $article['sectionName'])) {
                Article::create([
                    'title' => $article['webTitle'],
                    'author' => $article['sectionName'],
                    'description' => $article['webTitle'],
                    'url' => $article['webUrl'],
                    // 'urlToImage' => $article['fields']['thumbnail'],
                    'publishedAt' => $article['webPublicationDate'],
                    'source_name' => $article['sectionName'],
                    'provider' => 'guardian',
                ]);
            }
        }

        // dd('Articles fetched successfully from News API and Guardian API');
        $response = Http::get('https://rss.nytimes.com/services/xml/rss/nyt/HomePage.xml');
        $xml = simplexml_load_string($response->body());

        $items = (array) $xml->channel;
        // dd($items['item']);

        foreach ($items['item'] as $article) {
            if (isset($article->title, $article->link, $article->pubDate)) {
                Article::create([
                    'title' => $article->title,
                    'author' => 'New York Times',
                    'description' => $article->description,
                    'url' => $article->link,
                    'publishedAt' => $article->pubDate,
                    'source_name' => 'New York Times',
                    'provider' => 'nytimes',
                ]);
            }
        }

        dd('Articles fetched successfully');
    }

    public function index(Request $request)
    {
        $articles = Article::paginate(10);
        if ($request->has('search')) {
            $search = $request->input('search');
            $articles = Article::where('title', 'like', "%{$search}%")
                ->orWhere('author', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->paginate(10);

            // $articles = Article::search($search)->paginate(10);
        }
        return response()->json($articles);
    }
}
