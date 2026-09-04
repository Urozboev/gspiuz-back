<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/**
 * Tadbirlar kalendari.
 *
 *  GET /events        — tadbirlar ro'yxati, sana bo'yicha saralangan
 *  GET /events/{slug} — bitta tadbir
 *
 * Kalendar orqaga varaqlanadi, shuning uchun o'tgan tadbirlar ham
 * qaytariladi. Sukut bo'yicha joriy yildan boshlab.
 */
class EventController extends Controller
{
    public function index(Request $request)
    {
        $locale = App::getLocale();

        $perPage = (int) $request->query('per_page', 50);
        $perPage = $perPage > 0 && $perPage <= 200 ? $perPage : 50;

        $query = Event::active()->orderBy('date');

        // `?from=` va `?to=` berilmasa — joriy yil.
        $from = $request->query('from', now()->startOfYear()->toDateString());
        $to = $request->query('to');

        if ($from) {
            // Ko'p kunlik tadbir oraliqqa qisman tushsa ham chiqadi.
            $query->where(function ($inner) use ($from) {
                $inner->whereDate('date', '>=', $from)
                    ->orWhereDate('end_date', '>=', $from);
            });
        }

        if ($to) {
            $query->whereDate('date', '<=', $to);
        }

        $events = $query->paginate($perPage);

        return response()->json([
            'data' => collect($events->items())
                ->map(fn ($event) => $this->line($event, $locale))
                ->values(),
            'meta' => [
                'total'        => $events->total(),
                'per_page'     => $events->perPage(),
                'current_page' => $events->currentPage(),
                'last_page'    => $events->lastPage(),
            ],
        ]);
    }

    public function show(string $slug)
    {
        $locale = App::getLocale();

        $event = Event::active()->where('slug', $slug)->first();

        if (!$event) {
            return response()->json(['message' => 'Tadbir topilmadi'], 404);
        }

        $event->increment('views_count');

        return response()->json(['data' => $this->line($event, $locale)]);
    }

    private function line(Event $event, string $locale): array
    {
        return [
            'id'       => $event->id,
            'slug'     => $event->slug,
            'title'    => $this->text($event->title, $locale),
            'desc'     => $this->text($event->desc, $locale),
            'date'     => optional($event->date)->toDateString(),
            'end_date' => optional($event->end_date)->toDateString(),
            'time'     => $event->time,
            'location' => $this->text($event->location, $locale),
            'type'     => $event->type,
            'url'      => $event->url,
            'image'    => $event->img ? [
                'lg' => $event->lg_img,
                'md' => $event->md_img,
                'sm' => $event->sm_img,
            ] : null,
        ];
    }

    /** Ko'p tilli maydondan joriy til qatorini oladi. */
    private function text($value, string $locale): ?string
    {
        if (!is_array($value)) {
            return is_string($value) && $value !== '' ? $value : null;
        }

        $line = $value[$locale] ?? null;

        if ($line === null || $line === '') {
            $line = $value[$this->main_lang->code ?? 'uz'] ?? null;
        }

        return $line !== '' ? $line : null;
    }
}
