<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedArticle extends Model
{
    use HasFactory;

    protected $table = 'saved_articles';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'source',
        'category',
        'author',
        'published_at',
        'url',
    ];
}