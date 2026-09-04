<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'desc',
        'date',
        'file',
        'views_count',
        'meta_keywords',
        'video_link',
        'url',
        'slug'

    ];

    protected $casts = [
        'title' => 'array',
        'subtitle' => 'array',
        'desc' => 'array'
    ];

    public function postsCategories()
    {
        return $this->belongsToMany(PostsCategory::class, 'posts_category_post', 'post_id', 'posts_category_id');
    }

    public function postImages()
    {
        return $this->hasMany(PostImage::class);
    }
}
