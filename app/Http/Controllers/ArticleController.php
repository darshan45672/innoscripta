<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ArticleController extends Controller
{
    public function getArticles()
    {
        $response = Http::get('https://newsapi.org/v2/everything?q=political&apiKey=22ce375e050640ca8c116505683a6a27');
        // $response = Http::get('https://content.guardianapis.com/search?api-key=5a80c638-06d2-4e55-b23f-bf6620525c25');
        $fetchedArticles = $response->json()['articles'];
        dd($fetchedArticles);
        // dd($response->json());
        $count = count($fetchedArticles);
        // dd($count);

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
                ]);
            }
        }
        

        dd("done");
        // dd($article);
    }
}
