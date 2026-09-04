<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Saytga kirilganda ochiladigan modal xabar (bayram tabrigi, e'lon).
 *
 * Har bir xabar o'z muddatiga ega: `starts_at` dan `ends_at` gacha
 * ko'rinadi. Ikkalasi ham bo'sh bo'lsa — muddatsiz.
 */
class Rek extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'desc', 'url', 'logo', 'action', 'action_label',
        'starts_at', 'ends_at', 'active', 'order',
    ];

    protected $casts = [
        'title'     => 'array',
        'desc'      => 'array',
        'action_label' => 'array',
        'starts_at' => 'date',
        'ends_at'   => 'date',
        'active'    => 'boolean',
    ];

    /**
     * Ayni damda ko'rsatilishi kerak bo'lgan xabarlar.
     * Muddati boshlanmagan yoki tugagani chiqarilmaydi.
     */
    public function scopeVisible(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->where('active', 1)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', $today))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', $today));
    }
}
