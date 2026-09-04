<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Translation;
use App\Models\Lang;
use App\Models\TranslationGroup;
use Exception;
use Illuminate\Support\Facades\Validator;

class TranslationsController extends Controller
{
    public $title = 'Tarjimalar';
    public $route_name = 'translations';
    public $route_parameter = 'translation';

    public function index()
    {
        $translations = Translation::where('id', '>', 0);
        if(isset($_GET['search'])) {
            $translations = $translations->where('key', 'like', '%'.trim($_GET['search']).'%')
                ->orWhere('val', 'like', '%'.$_GET['search'].'%')
                // ->orWhere(DB::raw('JSON_EXTRACT(LOWER(val), "$.ru")'), 'like', '%'.trim($_GET['search']).'%');
                ->orWhere(DB::raw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(`val`, '$.\"ru\"')))"), 'like', '%' . mb_strtolower(trim($_GET['search'])) . '%');
        }
        $translations =  $translations
            ->orderBy('id', 'desc')
            ->paginate(12);

        $translation_groups = TranslationGroup::all();
        $languages = Lang::all();

        return view('app.translations.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'translations' => $translations,
            'languages' => $languages,
            'translation_groups' => $translation_groups,
            'search' => isset($_GET['search']) ? $_GET['search'] : '',
        ]);
    }

    public function show($id)
    {
        $translations = Translation::where('translation_group_id', $id);
        if(isset($_GET['search'])) {
            $translations = $translations->where('key', 'like', '%'.trim($_GET['search']).'%')
                ->orWhere('val', 'like', '%'.$_GET['search'].'%')
                ->orWhere(DB::raw('JSON_EXTRACT(LOWER(val), "$.ru")'), 'like', '%'.trim($_GET['search']).'%');
        }
        $translations =  $translations
            ->orderBy('id', 'desc')
            ->get();

        $group = TranslationGroup::find($id);
        $translation_groups = TranslationGroup::all();
        $languages = Lang::all();

        return view('app.translations.show', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'languages' => $languages,
            'translation_groups' => $translation_groups,
            'group' => $group,
            'search' => isset($_GET['search']) ? $_GET['search'] : '',
            'translations' => $translations
        ]);
    }

    public function edit($id)
    {
        $group = TranslationGroup::find($id);
        $translation_groups = TranslationGroup::all();
        $languages = Lang::all();

        return view('app.translations.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'languages' => $languages,
            'translation_groups' => $translation_groups,
            'group' => $group
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        DB::beginTransaction();
        try {

            $group = TranslationGroup::find($data['group']);
            $group->translations()
                ->delete();

            foreach ($data['translations'] as $item) {
                if (!is_null($item['key'])) {
                    Translation::create([
                        'translation_group_id' => $data['group'] ?? null,
                        'key' => $item['key'],
                        'val' => $item['val']
                    ]);
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return back()->withInput()->with([
                'success' => false,
                'message' => 'Saqlashda xatolik yuz berdi'
            ]);
        }

        return redirect()->route($this->route_name.'.show', [$this->route_parameter => $data['group']])->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }

    public function destroy(Translation $translation)
    {
        $translation->delete();

        return back()->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }

    public function update(Request $request, Translation $translation)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'key' => 'required|max:255',
            'val' => 'nullable|array'
        ]);
        if($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Maʼlumotlar toʻgʻri toʻldirilmagan'
            ]);
        }

        $translation->update($data);

        return back()->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }

    public function create()
    {
        $languages = Lang::all();
        return view('app.translations.create', compact('languages'));
    }



}
