<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostsCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'img',
        'parent_id',
        'slug'
    ];

    protected $casts = [
        'title' => 'array'
    ];

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'posts_category_post', 'posts_category_id', 'post_id');
    }
    public function menus()
    {
        return $this->belongsToMany(FormMenu::class, 'form_menu_category', 'form_menu_id', 'category_id');
    }

    public function parent()
    {
        return $this->hasOne(self::class, 'id', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    protected $appends = [
        'lg_img',
        'md_img',
        'sm_img'
    ];

    public function getLgImgAttribute() {
        return $this->img ? url('').'/upload/images/'.$this->img : null;
    }

    public function getMdImgAttribute() {
        return $this->img ? url('').'/upload/images/600/'.$this->img : null;
    }

    public function getSmImgAttribute() {
        return $this->img ? url('').'/upload/images/200/'.$this->img : null;
    }
    public function formMenus()
    {
        return $this->belongsToMany(FormMenu::class, 'form_menu_category');
    }

}
