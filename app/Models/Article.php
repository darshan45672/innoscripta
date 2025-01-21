<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    /** @use HasFactory<\Database\Factories\ArticleFactory> */
    use HasFactory;

    protected $fillable = [ 'title', 'author', 'description', 'url', 'urlToImage', 'publishedAt', 'content', 'source_name'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'publishedAt' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'source_name' => 'text',
        'title' => 'text',
    ];
}
