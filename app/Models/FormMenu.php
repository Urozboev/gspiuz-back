<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;


class FormMenu extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = ['text', 'type','order', 'position', 'title', 'dinamik_menu_id','photo',
        'slug', 'group', 'subtitle', 'body', 'icon', 'file', 'video', 'link', 'image', 'date', 'active'];

    protected $casts = [
        'title' => 'array',
        'text' => 'array',
        'photo' => 'array',
        'body' => 'array',
        'subtitle' => 'array',
        'date' => 'date',

    ];


    // Dinamik menyu bilan bog‘lanish
    public function dinamikMen()
    {
        return $this->belongsTo(DinamikMenu::class, 'dinamik_menu_id');
    }

    // Fayllar bilan bog‘lanish


    public function postsmenuCategories()
    {
        return $this->belongsToMany(PostsCategory::class, 'form_menu_category', 'form_menu_id', 'category_id');
    }

    public function formImages()
    {
        return $this->hasMany(FormImage::class, 'form_id');
    }





}
