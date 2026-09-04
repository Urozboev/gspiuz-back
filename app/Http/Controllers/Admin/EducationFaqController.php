<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EducationalProgram;
use App\Models\EmployType;
use App\Models\ERskill;
use App\Models\Lang;
use Illuminate\Http\Request;
use App\Models\EducationFaq;
use Illuminate\Support\Facades\Validator;

class EducationFaqController extends Controller
{
    public $title = 'Koʻp beriladigan savollar';
    public $route_name = 'employ_types';
    public $route_parameter = 'employ_type';
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $id)
    {
        $languages = Lang::all();
        $search = $request->input('search'); // Qidiruv so‘rovi
        $childPage = $request->input('child-page', 1); // Bolalar menyusining sahifasi

        // Faqat asosiy menyularni paginate qilish
        $paginatedMenus = EducationFaq::where('educational_program_id', $id)
            ->whereNull('parent_id')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'LIKE', '%' . $search . '%');
            })
            ->paginate(1); // Asosiy menyularni sahifalash

        // Agar hech narsa topilmasa, bo‘sh massiv qaytarish
        $menuTree = $paginatedMenus->isEmpty() ? [] : $this->buildMenuTree($paginatedMenus, $id, $childPage);

        return view('admin.faq.index', [
            'title' => 'Educational Programs',
            'route_name' => 'educational-programs',
            'educational_programs' => $menuTree,
            'languages' => $languages,
            'count' => $paginatedMenus,
            'id' => $id,
            'search' => $search,
        ]);
    }

    private function buildMenuTree($paginatedMenus, $id, $childPage)
    {
        // Sahifalangan asosiy menyular ID-lari
        $menuIds = $paginatedMenus->pluck('id')->toArray();

        // Har bir asosiy menyuning bolalarini alohida paginate qilish
        $childPaginations = [];
        foreach ($menuIds as $menuId) {
            $childPaginations[$menuId] = EducationFaq::where('educational_program_id', $id)
                ->where('parent_id', $menuId)
                ->paginate(12, ['*'], 'child-page'); // Sahifalangan child menyular
        }

        // Hierarxik daraxtni tuzish
        $menuTree = [];
        foreach ($paginatedMenus as $menu) {
            $menuTree[] = [
                'menu' => $menu, // Asosiy menyu
                'children' => $childPaginations[$menu->id] ?? collect(), // Bolalar menyusi
                'child_pagination' => $childPaginations[$menu->id] ?? null // Sahifalash ma'lumotlari
            ];
        }

        return $menuTree;
    }

//    public function index($id)
//    {
//
//        $langs = Lang::all();
//
//        $faqs = EducationFaq::where('educational_program_id', $id)->latest()->paginate(10);
//
//        return view('admin.faq.index', [
//            'title' => $this->title,
//            'route_name' => $this->route_name,
//            'route_parameter' => $this->route_parameter,
//            'langs' => $langs,
//            'faqs' => $faqs,
//        ]);
//    }

    public function create($id)
    {
        $langs = Lang::all();

        $skills = ERskill::query()->get();
        $faqs = EducationFaq::where('educational_program_id', $id)
            ->whereNull('parent_id')
            ->get();

        return view('admin.faq.create', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'faqs' => $faqs,
            'id' => $id,
            'skills' => $skills,
        ]);
    }


    /**
     *
     * Show the form for creating a new resource.
     */
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Kiruvchi ma'lumotlarni tasdiqlash
        $data = $request->validate([
            'educational_program_id' => 'required|integer',
            'question' => 'required|array', // "question" array bo'lishi kerak
            'answer' => 'required|array', // "answer" ham array bo'lishi kerak
            'skill_id' => 'required|integer',
            'parent_id' => 'nullable|integer', // parent_id ni qo'shdik
            'action' => 'nullable|integer', // parent_id ni qo'shdik
        ]);

        // Ma'lumotni saqlash
        EducationFaq::create([
            'educational_program_id' => $data['educational_program_id'] ?? null,
            'question' => $data['question'] ?? null, // JSON formatda saqlash
            'answer' => $data['answer'] ?? null, // JSON formatda saqlash
            'skill_id' => $data['skill_id'] ?? null,
            'action' => $data['action'] ?? null,
            'parent_id' => $data['parent_id'] ?? null, // Agar yo'q bo'lsa, null qo'yiladi
        ]);

        // Yaratilgandan so'ng qaytish
        return redirect()->route('education_faqs.index', $data['educational_program_id'])
            ->with('success', 'FAQ muvaffaqiyatli qo\'shildi!');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
//    public function edit($id,$main)
//    {
//        $faq = EducationFaq::find($id);
//        $langs = Lang::all();
//        $skills = ERskill::query()->get();
//        $faqs = EducationFaq::where('educational_program_id', $main)
//            ->whereNull('parent_id')
//            ->get();
//
//        // View ga ma'lumotlarni yuborish
//        return view('admin.faq.edit', [
//            'title' => $this->title,
//            'route_name' => $this->route_name,
//            'route_parameter' => $this->route_parameter,
//            'langs' => $langs,
//            'faq' => $faq,
//            'faqs' => $faqs,
//            'skills' => $skills,
//        ]);
//}\
    public function edit($main, $id)
    {
        $faq = EducationFaq::find($id);
        $langs = Lang::all();
        $skills = ERskill::all();
        $faqs = EducationFaq::where('educational_program_id', $main)
            ->whereNull('parent_id')
            ->get();

        return view('admin.faq.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'faq' => $faq,
            'faqs' => $faqs,
            'skills' => $skills,
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Kiruvchi ma'lumotlarni tasdiqlash
        $data = $request->validate([
            'educational_program_id' => 'required|integer',
            'question' => 'required|array',
            'answer' => 'required|array',
            'skill_id' => 'required|integer',
            'action' => 'nullable|integer', // parent_id ni qo'shdik

        ]);

        // Tahrirlanayotgan obyektni olish
        $faq = EducationFaq::find($id);

        // Ma'lumotni yangilash
        $faq->update([
            'educational_program_id' => $data['educational_program_id'] ?? null,
            'question' => $data['question'] ?? null, // Array sifatida saqlash
            'answer' => $data['answer'] ?? null, // Array sifatida saqlash
            'skill_id' => $data['skill_id'] ?? null,
            'action' => $data['action'] ?? null,

        ]);

        // Yangilangandan so'ng qaytish
        return redirect()->route('education_faqs.index', $data['educational_program_id'])
            ->with('success', 'FAQ muvaffaqiyatli yangilandi!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Ma'lumotni topish va o'chirish
        EducationFaq::find($id)->delete();

        // Foydalanuvchiga muvaffaqiyatli xabar qaytarish
        return redirect()->back()->with('success', 'FAQ muvaffaqiyatli o‘chirildi!');
    }

}
