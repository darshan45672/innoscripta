<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsSource extends Model
{
    /** @use HasFactory<\Database\Factories\NewsSourceFactory> */
    use HasFactory;

    protected $fillable = ['name'];

    protected $hidden = ['created_at', 'updated_at'];

    public function articles()
    {
        return $this->hasMany(Article::class, 'news_source_id');

    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'news_source_user');
    }
}
