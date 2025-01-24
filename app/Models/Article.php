<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    /** @use HasFactory<\Database\Factories\ArticleFactory> */
    use HasFactory;

    protected $fillable = [ 'title', 'author', 'description', 'url', 'urlToImage', 'publishedAt', 'content', 'source_name', 'provider'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'publishedAt' => 'datetime',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'article_category');
    }

    public function authors()
    {
        return $this->belongsToMany(Author::class, 'article_author');
    }

}
