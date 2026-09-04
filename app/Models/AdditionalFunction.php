<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdditionalFunction extends Model
{
    use HasFactory;

    protected $fillable = [
        'telegram_bot_token',
        'telegram_group_id',
        'livechat',
        'yandex_index',
        'google_index',
        'yandex_metrika',
        'google_analytics',
        'sitemap'
    ];
}
