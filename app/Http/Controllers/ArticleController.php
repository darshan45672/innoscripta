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
        $fetchedArticles = $response->json()['articles'];
        // dd($fetchedArticles[56]);

        $article = Article::create([
            'title' => $fetchedArticles[0]['title'],
            'author' => $fetchedArticles[0]['author'],
            'description' => $fetchedArticles[0]['description'],
            'url' => $fetchedArticles[0]['url'],
            'urlToImage' => $fetchedArticles[0]['urlToImage'],
            'publishedAt' => $fetchedArticles[0]['publishedAt'],
            'content' => $fetchedArticles[0]['content'],
            'source_name' => $fetchedArticles[0]['source']['name'],
        ]);

        dd($article);
    }
}
