<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lang;
use App\Models\Rek;
use App\Models\SiteInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdvertisementController extends Controller
{
    public $title = 'Reklama';
    public $route_name = 'advertisements';
    public $route_parameter = 'advertisement';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function index()
    {
        $site_info = Rek::latest()
            ->first();
        $langs = Lang::all();

        return view('app.site_infos.rek', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'site_info' => $site_info,
            'langs' => $langs
        ]);
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function facts_figures_create(Request $request)
    {
        $data = $request->all();


        $site_info = SiteInfo::first();

        SiteInfo::updateOrCreate(
            [
                'id' => $site_info->id ?? null
            ],
            [
                'audience_size' => $data['audience_size'] ?? null,
                'educational_programs' => $data['educational_programs'] ?? null,
                'green_zone' => $data['green_zone'] ?? null,
                'library_collection' => $data['library_collection'] ?? null,
                'number_of_students' => $data['number_of_students'] ?? null,
                'male_students' => $data['male_students'] ?? null,
                'female_students' => $data['female_students'] ?? null,
            ]

        );

        return redirect()->route('facts_figures')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
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

        // Faylni tekshirib olish
        $data['logo'] = $data['logo'][0] ?? null;
        $data['logo_dark'] = $data['logo_dark'][0] ?? null;
        $data['favicon'] = $data['favicon'][0] ?? null;

        // Eski yozuvni topish
        $site_info = Rek::first();

        Rek::updateOrCreate(
            ['id' => $site_info->id ?? null],
            [
                'title' => $data['title'] ?? null,
                'url' => $data['url'] ?? null,
                'logo' => $data['logo'] ?? null,
                // Bayroq: havola tugmasi koʻrsatilsinmi. Avval bu yerga
                // soʻzma-soʻz 'default_value' qatori yozilardi.
                'action' => $request->boolean('action'),
            ]
        );


        return redirect()->route('advertisements.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SiteInfo  $siteInfo
     * @return \Illuminate\Http\Response
     */
    public function show(SiteInfo $siteInfo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SiteInfo  $siteInfo
     * @return \Illuminate\Http\Response
     */
    public function edit(SiteInfo $siteInfo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SiteInfo  $siteInfo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SiteInfo $siteInfo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SiteInfo  $siteInfo
     * @return \Illuminate\Http\Response
     */
    public function destroy(SiteInfo $siteInfo)
    {
        //
    }
}
