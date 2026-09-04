<?php

namespace App\Console\Commands;

use App\Models\DinamikMenu;
use App\Models\FormMenu;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

/**
 * Matn ichidagi <oembed> teglarini haqiqiy <iframe> ga aylantiradi.
 *
 * CKEditor 5 sukut bo'yicha video havolasini `<oembed url="…">` deb yozadi:
 * muharrirda video ko'rinadi, saytda esa yo'q, chunki brauzer bunday tegni
 * bilmaydi. Sozlama (`previewsInData`) tuzatildi, ammo undan oldin
 * saqlangan matnlar shu buyruq bilan to'g'rilanadi.
 *
 *   php artisan media:fix-embeds          — nima o'zgarishini ko'rsatadi
 *   php artisan media:fix-embeds --apply  — o'zgartiradi
 */
class FixEmbeddedMedia extends Command
{
    protected $signature = 'media:fix-embeds {--apply : Oʻzgarishlarni bazaga yozadi}';

    protected $description = 'Matnlardagi <oembed> teglarini <iframe> ga aylantiradi';

    /** Qaysi model va qaysi maydonlar tekshiriladi. */
    private const TARGETS = [
        Post::class        => ['desc'],
        DinamikMenu::class => ['text'],
        FormMenu::class    => ['text', 'body'],
    ];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $changed = 0;

        foreach (self::TARGETS as $model => $fields) {
            foreach ($model::all() as $record) {
                if ($this->fixRecord($record, $fields, $apply)) {
                    $changed++;
                }
            }
        }

        if ($changed === 0) {
            $this->info('Tuzatishga muhtoj matn topilmadi.');

            return self::SUCCESS;
        }

        $this->info(($apply ? 'Tuzatildi' : 'Tuzatish kerak') . ': ' . $changed . ' ta yozuv.');

        if (!$apply) {
            $this->line('Oʻzgartirish uchun: php artisan media:fix-embeds --apply');
        }

        return self::SUCCESS;
    }

    private function fixRecord(Model $record, array $fields, bool $apply): bool
    {
        $touched = false;

        foreach ($fields as $field) {
            $value = $record->{$field};

            if (!is_array($value)) {
                continue;
            }

            foreach ($value as $lang => $html) {
                if (!is_string($html) || !str_contains($html, '<oembed')) {
                    continue;
                }

                $fixed = $this->convert($html);

                if ($fixed !== $html) {
                    $value[$lang] = $fixed;
                    $touched = true;

                    $this->line(sprintf(
                        '  %s #%d [%s.%s]',
                        class_basename($record),
                        $record->id,
                        $field,
                        $lang
                    ));
                }
            }

            if ($touched && $apply) {
                $record->{$field} = $value;
            }
        }

        if ($touched && $apply) {
            $record->save();
        }

        return $touched;
    }

    /** <oembed url="…"></oembed> → <iframe src="…"></iframe> */
    private function convert(string $html): string
    {
        return preg_replace_callback(
            '~<oembed[^>]*url="([^"]+)"[^>]*>\s*</oembed>~i',
            function ($match) {
                $embed = $this->embedUrl($match[1]);

                if ($embed === null) {
                    // Tanilmagan xizmat — oddiy havola qoldiramiz,
                    // shunda hech bo'lmasa bosib ko'rish mumkin.
                    return '<p><a href="' . e($match[1]) . '" target="_blank" rel="noopener">'
                        . e($match[1]) . '</a></p>';
                }

                return '<iframe src="' . e($embed) . '" width="100%" height="480" '
                    . 'frameborder="0" allowfullscreen '
                    . 'allow="accelerometer; autoplay; clipboard-write; encrypted-media; '
                    . 'gyroscope; picture-in-picture"></iframe>';
            },
            $html
        ) ?? $html;
    }

    /** YouTube havolasidan qo'yish uchun manzil yasaydi. */
    private function embedUrl(string $url): ?string
    {
        // https://youtu.be/ID  yoki  https://www.youtube.com/watch?v=ID
        if (preg_match('~youtu\.be/([A-Za-z0-9_-]{6,})~', $url, $m)
            || preg_match('~youtube\.com/watch\?(?:.*&)?v=([A-Za-z0-9_-]{6,})~', $url, $m)
            || preg_match('~youtube\.com/embed/([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }

        return null;
    }
}
