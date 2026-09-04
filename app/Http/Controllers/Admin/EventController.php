<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Lang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Tadbirlar kalendari.
 *
 * Saytdagi /events sahifasi va bosh sahifadagi "yaqin tadbirlar" bloki
 * shu bo'limdan to'ldiriladi. Yangilikdan farqi — tadbirning aniq sanasi,
 * vaqti va joyi bo'ladi.
 */
class EventController extends Controller
{
    public $title = 'Tadbirlar';

    public $route_name = 'events';

    public $route_parameter = 'event';

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $events = Event::query()
            ->when($search !== '', fn ($query) => $query->where('title', 'like', '%' . $search . '%'))
            ->orderByDesc('date')
            ->paginate(15)
            ->withQueryString();

        return view('admin.events.index', [
            'title'           => $this->title,
            'route_name'      => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'events'          => $events,
            'search'          => $search,
        ]);
    }

    public function create()
    {
        return view('admin.events.create', [
            'title'           => $this->title,
            'route_name'      => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs'           => Lang::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($data === null) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Maʼlumotlar toʻgʻri toʻldirilmagan',
            ]);
        }

        $data['slug'] = $this->uniqueSlug($data['title'][$this->main_lang->code] ?? 'tadbir');

        Event::create($data);

        return redirect()->route('events.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi',
        ]);
    }

    public function edit(Event $event)
    {
        return view('admin.events.edit', [
            'title'           => $this->title,
            'route_name'      => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs'           => Lang::all(),
            'event'           => $event,
        ]);
    }

    public function update(Request $request, Event $event)
    {
        $data = $this->validated($request);

        if ($data === null) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Maʼlumotlar toʻgʻri toʻldirilmagan',
            ]);
        }

        $event->update($data);

        return redirect()->route('events.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli yangilandi',
        ]);
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()->route('events.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli oʻchirildi',
        ]);
    }

    /** Sarlavhadan takrorlanmaydigan manzil yasaydi. */
    private function uniqueSlug(string $title): string
    {
        $slug = Str::slug($title) ?: 'tadbir';

        if (Event::withTrashed()->where('slug', $slug)->exists()) {
            $slug .= '-' . time();
        }

        return $slug;
    }

    private function validated(Request $request): ?array
    {
        $validator = Validator::make($request->all(), [
            'title.' . $this->main_lang->code => 'required|string',
            'date'                            => 'required|date',
            'end_date'                        => 'nullable|date|after_or_equal:date',
            'time'                            => 'nullable|string|max:32',
            'type'                            => 'nullable|string|max:64',
            'url'                             => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return null;
        }

        $data = $request->all();

        return [
            'title'    => $data['title'] ?? null,
            'desc'     => $data['desc'] ?? null,
            'location' => $data['location'] ?? null,
            'date'     => $data['date'],
            'end_date' => ($data['end_date'] ?? null) ?: null,
            'time'     => ($data['time'] ?? null) ?: null,
            'type'     => ($data['type'] ?? null) ?: null,
            'url'      => ($data['url'] ?? null) ?: null,
            // Dropzone massiv qaytaradi.
            'img'      => is_array($data['dropzone_images'] ?? null)
                ? ($data['dropzone_images'][0] ?? null)
                : ($data['dropzone_images'] ?? null),
            'active'   => $request->boolean('active'),
        ];
    }
}
