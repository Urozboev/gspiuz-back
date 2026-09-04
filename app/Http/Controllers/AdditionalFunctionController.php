<?php

namespace App\Http\Controllers;

use App\Models\Lang;
use App\Models\AdditionalFunction;
use Illuminate\Http\Request;

class AdditionalFunctionController extends Controller
{
    public $title = 'Qoʻshimcha xizmatlar';
    public $route_name = 'additional_functions';
    public $route_parameter = 'additional_function';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $additional_function = AdditionalFunction::latest()
            ->first();
        $langs = Lang::all();

        return view('app.additional_functions.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'additional_function' => $additional_function,
            'langs' => $langs
        ]);
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

        $additional_function = AdditionalFunction::first();
        
        AdditionalFunction::updateOrCreate(
            [
                'id' => $additional_function->id ?? null
            ],
            [
                'telegram_bot_token' => $data['telegram_bot_token'] ?? null,
                'telegram_group_id' => $data['telegram_group_id'] ?? null,
                'livechat' => $data['livechat'] ?? null,
                'yandex_index' => $data['yandex_index'] ?? null,
                'google_index' => $data['google_index'] ?? null,
                'yandex_metrika' => $data['yandex_metrika'] ?? null,
                'google_analytics' => $data['google_analytics'] ?? null
            ]
        );

        return redirect()->route('additional_functions.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AdditionalFunction  $additionalFunction
     * @return \Illuminate\Http\Response
     */
    public function show(AdditionalFunction $additionalFunction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AdditionalFunction  $additionalFunction
     * @return \Illuminate\Http\Response
     */
    public function edit(AdditionalFunction $additionalFunction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AdditionalFunction  $additionalFunction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AdditionalFunction $additionalFunction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AdditionalFunction  $additionalFunction
     * @return \Illuminate\Http\Response
     */
    public function destroy(AdditionalFunction $additionalFunction)
    {
        //
    }
}
