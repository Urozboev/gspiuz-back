<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lang;
use App\Models\Rek;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Saytga kirilganda ochiladigan modal xabarlar.
 *
 * Bayram tabriklari va muhim e'lonlar shu yerdan boshqariladi. Har bir
 * xabarga muddat beriladi: belgilangan kundan boshlab o'zi paydo bo'ladi
 * va oxirgi kundan keyin o'zi yo'qoladi — hech kim admin panelga kirib
 * o'chirishi shart emas.
 *
 * Ma'lumot `reks` jadvalida saqlanadi (avval bitta reklama bloki uchun
 * ishlatilgan; eski `/reklama` endpointi o'zgarishsiz qoldi).
 */
class PopupController extends Controller
{
    public $title = 'Modal xabarlar';

    public $route_name = 'popups';

    public $route_parameter = 'popup';

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $popups = Rek::query()
            ->when($search !== '', fn ($query) => $query->where('title', 'like', '%' . $search . '%'))
            ->orderBy('order')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('admin.popups.index', [
            'title'           => $this->title,
            'route_name'      => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'popups'          => $popups,
            'search'          => $search,
        ]);
    }

    public function create()
    {
        return view('admin.popups.create', [
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

        Rek::create($data);

        return redirect()->route('popups.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi',
        ]);
    }

    public function edit(Rek $popup)
    {
        return view('admin.popups.edit', [
            'title'           => $this->title,
            'route_name'      => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs'           => Lang::all(),
            'popup'           => $popup,
        ]);
    }

    public function update(Request $request, Rek $popup)
    {
        $data = $this->validated($request);

        if ($data === null) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Maʼlumotlar toʻgʻri toʻldirilmagan',
            ]);
        }

        $popup->update($data);

        return redirect()->route('popups.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli yangilandi',
        ]);
    }

    public function destroy(Rek $popup)
    {
        $popup->delete();

        return redirect()->route('popups.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli oʻchirildi',
        ]);
    }

    /** Formadan kelgan ma'lumot; xato bo'lsa null qaytaradi. */
    private function validated(Request $request): ?array
    {
        $validator = Validator::make($request->all(), [
            'title.' . $this->main_lang->code => 'required|string',
            'starts_at'                       => 'nullable|date',
            'ends_at'                         => 'nullable|date|after_or_equal:starts_at',
            'url'                             => 'nullable|string|max:255',
            'order'                           => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return null;
        }

        $data = $request->all();

        return [
            'title'     => $data['title'] ?? null,
            'desc'      => $data['desc'] ?? null,
            'url'       => $data['url'] ?? null,
            // Dropzone massiv qaytaradi — birinchisini olamiz.
            'logo'      => is_array($data['dropzone_images'] ?? null)
                ? ($data['dropzone_images'][0] ?? null)
                : ($data['dropzone_images'] ?? null),
            'action'       => $request->boolean('action'),
            'action_label' => $data['action_label'] ?? null,
            'starts_at' => ($data['starts_at'] ?? null) ?: null,
            'ends_at'   => ($data['ends_at'] ?? null) ?: null,
            'active'    => $request->boolean('active'),
            'order'     => (int) ($data['order'] ?? 0),
        ];
    }
}
