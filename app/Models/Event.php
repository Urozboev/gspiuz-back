<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Tadbir — konferensiya, uchrashuv, bayram va hokazo.
 *
 * Kalendar orqaga ham varaqlanadi, shuning uchun o'tgan tadbirlar
 * o'chirilmaydi, ular ham qaytariladi.
 */
class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'desc', 'slug', 'date', 'end_date', 'time',
        'location', 'type', 'url', 'img', 'active', 'views_count',
    ];

    protected $casts = [
        'title'    => 'array',
        'desc'     => 'array',
        'location' => 'array',
        'date'     => 'date',
        'end_date' => 'date',
        'active'   => 'boolean',
    ];

    protected $appends = ['lg_img', 'md_img', 'sm_img'];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', 1);
    }

    public function getLgImgAttribute(): ?string
    {
        return $this->img ? url('/upload/images/' . $this->img) : null;
    }

    public function getMdImgAttribute(): ?string
    {
        return $this->img ? url('/upload/images/600/' . $this->img) : null;
    }

    public function getSmImgAttribute(): ?string
    {
        return $this->img ? url('/upload/images/200/' . $this->img) : null;
    }
}
