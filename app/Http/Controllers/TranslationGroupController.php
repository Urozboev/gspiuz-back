<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\TranslationGroup;
use Illuminate\Http\Request;

class TranslationGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'title' => 'required|max:255|unique:translation_groups,title',
            'sub_text' => 'required|max:255|unique:translation_groups,sub_text',
        ]);
        if ($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Maʼlumotlar toʻgʻri toʻldirilmagan'
            ]);
        }

        TranslationGroup::create($data);

        return back()->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TranslationGroup  $translationGroup
     * @return \Illuminate\Http\Response
     */
    public function show(TranslationGroup $translationGroup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TranslationGroup  $translationGroup
     * @return \Illuminate\Http\Response
     */
    public function edit(TranslationGroup $translationGroup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TranslationGroup  $translationGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TranslationGroup $translationGroup)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TranslationGroup  $translationGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy(TranslationGroup $translationGroup)
    {
        //
    }
}
