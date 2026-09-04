<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employ;
use App\Models\Rek;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/**
 * Saytdagi ikkita avtomatik blok:
 *
 *  GET /popups    — kirilganda ochiladigan modal xabarlar (bayram tabriklari).
 *  GET /birthdays — bugun tugʻilgan kuni boʻlgan xodimlar.
 *
 * Ikkalasi ham hech kim qoʻlda oʻchirmasligi uchun sana boʻyicha oʻzi
 * paydo boʻladi va oʻzi yoʻqoladi.
 */
class NoticeController extends Controller
{
    /** Modal xabarlar — muddati kelganlari, tartib boʻyicha. */
    public function popups()
    {
        $locale = App::getLocale();

        $notices = Rek::visible()
            ->orderBy('order')
            ->orderByDesc('id')
            ->get()
            ->map(fn ($notice) => [
                'id'        => $notice->id,
                'title'     => $this->line($notice->title, $locale),
                'desc'      => $this->line($notice->desc, $locale),
                'image'     => [
                    'lg' => $notice->logo ? url('/upload/images/' . $notice->logo) : null,
                    'md' => $notice->logo ? url('/upload/images/600/' . $notice->logo) : null,
                    'sm' => $notice->logo ? url('/upload/images/200/' . $notice->logo) : null,
                ],
                'url'       => $notice->url,
                'action'       => (bool) $notice->action,
                'action_label' => $this->line($notice->action_label, $locale),
                'starts_at' => optional($notice->starts_at)->toDateString(),
                'ends_at'   => optional($notice->ends_at)->toDateString(),
            ])
            ->values();

        return response()->json(['data' => $notices]);
    }

    /**
     * Tugʻilgan kunlar.
     *
     * Sukut boʻyicha bugungi kun. `?days=7` berilsa — shu kundan boshlab
     * berilgan kun ichidagilar (kadrlar boʻlimi uchun).
     */
    public function birthdays(Request $request)
    {
        $locale = App::getLocale();

        $days = (int) $request->query('days', 0);
        $days = max(0, min($days, 31));

        $dates = collect(range(0, $days))
            ->map(fn ($offset) => now()->addDays($offset))
            ->map(fn ($date) => [(int) $date->format('n'), (int) $date->format('j')]);

        $employees = Employ::query()
            ->whereNotNull('birthday')
            // Faollik `employs` da emas, `employ_metas` da belgilanadi.
            ->whereHas('employMeta', fn ($query) => $query->where('active', 1))
            ->where(function ($query) use ($dates) {
                foreach ($dates as [$month, $day]) {
                    $query->orWhere(function ($q) use ($month, $day) {
                        $q->whereMonth('birthday', $month)->whereDay('birthday', $day);
                    });
                }
            })
            ->with('employMeta.department', 'employMeta.position')
            ->get();

        $people = $employees
            ->map(fn ($employ) => $this->person($employ, $locale))
            ->sortBy('date')
            ->values();

        return response()->json(['data' => $people]);
    }

    private function person(Employ $employ, string $locale): array
    {
        $meta = $employ->employMeta;
        $birthday = $employ->birthday ? \Illuminate\Support\Carbon::parse($employ->birthday) : null;

        return [
            'id'         => $employ->id,
            'slug'       => $employ->slug,
            'full_name'  => trim(implode(' ', array_filter([
                $this->line($employ->last_name, $locale),
                $this->line($employ->first_name, $locale),
                $this->line($employ->surname, $locale),
            ]))),
            'position'   => $meta ? $this->line(optional($meta->position)->name, $locale) : null,
            'department' => $meta ? $this->line(optional($meta->department)->name, $locale) : null,
            'photo'      => $employ->photo ? url('/upload/images/' . $employ->photo) : null,
            // To'lgan yosh: shu yilgi tug'ilgan kuni hali kelmagan bo'lsa bir yil kam.
            'age'        => $birthday ? $birthday->diffInYears(now()) : null,
            'date'       => $birthday ? $birthday->format('m-d') : null,
        ];
    }

    /** Koʻp tilli maydondan joriy til qatorini oladi. */
    private function line($value, string $locale): ?string
    {
        if (is_array($value)) {
            $line = $value[$locale] ?? null;

            if ($line === null || $line === '') {
                $line = $value[$this->main_lang->code ?? 'uz'] ?? null;
            }

            return $line !== '' ? $line : null;
        }

        return is_string($value) && $value !== '' ? $value : null;
    }
}
