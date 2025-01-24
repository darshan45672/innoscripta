<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    protected $fillable = ['name'];

    protected $hidden = ['created_at', 'updated_at'];

    public function articles()
    {
        return $this->belongsToMany(Article::class, 'article_category');
    }
    
    public function users()
    {
        return $this->belongsToMany(User::class, 'category_user');
    }
}
