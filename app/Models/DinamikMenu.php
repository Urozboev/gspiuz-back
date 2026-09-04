<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DinamikMenu extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = ['menu_id', 'title', 'text','background','short_title','file',
        'layout', 'meta_title', 'meta_description', 'active', 'video', 'images'];

    /** Sahifaning ko'rinish turlari. */
    public const LAYOUTS = ['single', 'cards', 'files'];

    protected $casts = [
        'title' => 'array',
        'text' => 'array',
        'file' => 'array',
        'short_title' => 'array',
        'meta_title' => 'array',
        'meta_description' => 'array',
        'images' => 'array',

    ];

    // Menu bilan bog‘lanish
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    // Form bilan bog‘lanish
    public function forms()
    {
        return $this->hasMany(FormMenu::class, 'dinamik_menu_id');
    }
    protected $appends = [
        'lg_img',
        'md_img',
        'sm_img'
    ];

    public function getLgImgAttribute() {
        return $this->background  ? url('').'/upload/images/'.$this->background : null;
    }

    public function getMdImgAttribute() {
        return $this->background ? url('').'/upload/images/600/'.$this->background : null;
    }

    public function getSmImgAttribute() {
        return $this->background ? url('').'/upload/images/200/'.$this->background : null;
    }
}
