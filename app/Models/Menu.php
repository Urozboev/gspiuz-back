<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = ['order','title', 'parent_id', 'path', 'slug', 'active'];
    protected $casts = [
        'title' => 'array',

    ];


    // O‘z-o‘ziga bog‘lanish: Menu -> Submenu
    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    // Dinamik menyu bilan bog‘lanish
    public function dinamikMens()
    {
        return $this->hasMany(DinamikMenu::class, 'menu_id');
    }
}
