<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedArticle extends Model
{
    use HasFactory;

    protected $table = 'saved_articles';
    // protected $attributes = [
    //     'thumbnail' => 'default-thumbnail.jpg',
    // ];


    protected $fillable = [
        'user_id',
        'title',
        'description',
        'source',
        'category',
        'author',
        'published_at',
        'thumbnail',
        'url',
        'preferred_sources',
        'preferred_categories',
        'preferred_authors',
    ];
}