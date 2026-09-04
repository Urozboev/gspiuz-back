<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kampus extends Model
{
    use HasFactory;
    protected $fillable = [
        'first_name',
        'second_name',
        'third_name',
        'name',
        'slug',
        'map',
        'active',
        'audience_size',
        'educational_programs',
        'green_zone',
        'first_description',
        'second_description',
        'third_description',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'first_name' => 'array',
        'second_name' => 'array',
        'third_name' => 'array',
        'name' => 'array',
        'first_description' => 'array',
        'second_description' => 'array',
        'third_description' => 'array',
    ];
    public function kampusImages()
    {
        return $this->hasMany(KampusImg::class);
    }

}
