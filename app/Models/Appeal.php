<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Murojaat — /murojaat sahifasidan kelgan ariza.
 */
class Appeal extends Model
{
    use HasFactory;

    /** Murojaat turlari. */
    public const TYPE_RECTOR     = 'rector';     // Rektorga
    public const TYPE_TUTOR      = 'tutor';      // Tyutorga
    public const TYPE_COMPLIANCE = 'compliance'; // Komplayens xizmatiga

    /** Ko'rib chiqish holatlari. */
    public const STATUS_NEW       = 'new';        // Yangi
    public const STATUS_IN_REVIEW = 'in_review';  // Ko'rib chiqilmoqda
    public const STATUS_ANSWERED  = 'answered';   // Javob berilgan
    public const STATUS_REJECTED  = 'rejected';   // Rad etilgan

    protected $fillable = [
        'ticket',
        'type',
        'status',
        'name',
        'phone_number',
        'email',
        'address',
        'message',
        'file',
        'answer',
        'answered_at',
        'answered_by',
        'ip',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
    ];

    public static function types(): array
    {
        return [
            self::TYPE_RECTOR     => ['uz' => 'Rektorga',  'ru' => 'Ректору',            'en' => 'To the Rector'],
            self::TYPE_TUTOR      => ['uz' => 'Tyutorga',  'ru' => 'Тьютору',            'en' => 'To the Tutor'],
            self::TYPE_COMPLIANCE => ['uz' => 'Komplayens xizmatiga', 'ru' => 'В службу комплаенс', 'en' => 'To the Compliance Office'],
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_NEW       => ['uz' => 'Yangi',              'ru' => 'Новое',            'en' => 'New'],
            self::STATUS_IN_REVIEW => ['uz' => "Ko'rib chiqilmoqda", 'ru' => 'На рассмотрении',  'en' => 'In review'],
            self::STATUS_ANSWERED  => ['uz' => 'Javob berilgan',     'ru' => 'Дан ответ',        'en' => 'Answered'],
            self::STATUS_REJECTED  => ['uz' => 'Rad etilgan',        'ru' => 'Отклонено',        'en' => 'Rejected'],
        ];
    }

    /** Navbatdagi ariza raqamini yaratadi: MRJ-2026-000123 */
    public static function generateTicket(): string
    {
        $year = date('Y');
        $last = static::where('ticket', 'like', "MRJ-{$year}-%")->max('ticket');
        $next = $last ? ((int) substr($last, -6)) + 1 : 1;

        return sprintf('MRJ-%s-%06d', $year, $next);
    }

    public function fileUrl(): ?string
    {
        return $this->file ? url('/upload/appeals/' . $this->file) : null;
    }
}
