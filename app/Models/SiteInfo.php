<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'logo',
        'logo_dark',
        'desc',
        'tagline',
        'slogan',
        'admission_starts_at',
        'admission_ends_at',
        'admission_url',
        'address',
        'phone_number',
        'email',
        'yt_url',
        'work_time',
        'map',
        'exchange',
        'favicon',
        'telegram',
        'instagram',
        'facebook',
        'youtube',
        'audience_size',
        'educational_programs',
        'green_zone',
        'library_collection',
        'number_of_students',
        'male_students',
        'female_students',
        'legal_name',
        'bank_account',
        'bank_name',
        'mfo',
        'inn',
        'oked',
        'treasury_account',
    ];

    protected $casts = [
        'title' => 'array',
        'desc' => 'array',
        'tagline' => 'array',
        'slogan' => 'array',
        'admission_starts_at' => 'date',
        'admission_ends_at' => 'date',
        'address' => 'array',
        'legal_name' => 'array',
        'bank_name' => 'array',
//        'work_time' => 'array',
    ];
}
