<?php

namespace App\Http\Controllers;

use App\Models\Rek;
use Illuminate\Support\Facades\Validator;
use App\Models\Lang;
use App\Models\SiteInfo;
use Illuminate\Http\Request;

class SiteInfoController extends Controller
{
    public $title = 'Sayt maʼlumotlari';
    public $route_name = 'site_infos';
    public $route_parameter = 'site_info';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function facts_figures()
    {
        $site_info = SiteInfo::latest()
            ->first();
        $langs = Lang::all();

        return view('app.site_infos.facts_figures', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'site_info' => $site_info,
            'langs' => $langs
        ]);
    }
    public function index()
    {
        $site_info = SiteInfo::latest()
            ->first();
        $langs = Lang::all();

        return view('app.site_infos.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'site_info' => $site_info,
            'langs' => $langs
        ]);
    }
    public function advertisement()
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

        $validator = Validator::make($data, [
            'title.' . $this->main_lang->code => 'required'
        ]);
        if ($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Maʼlumotlar toʻgʻri toʻldirilmagan'
            ]);
        }

        $data['logo'] = isset($data['logo'][0]) ? $data['logo'][0] : null;
        $data['logo_dark'] = isset($data['logo_dark'][0]) ? $data['logo_dark'][0] : null;
        $data['favicon'] = isset($data['favicon'][0]) ? $data['favicon'][0] : null;

        $site_info = SiteInfo::first();

        SiteInfo::updateOrCreate(
            [
                'id' => $site_info->id ?? null
            ],
            [
                'title' => $data['title'] ?? null,
                'desc' => $data['desc'] ?? null,
                'tagline' => $data['tagline'] ?? null,
                'slogan' => $data['slogan'] ?? null,
                'admission_starts_at' => ($data['admission_starts_at'] ?? null) ?: null,
                'admission_ends_at' => ($data['admission_ends_at'] ?? null) ?: null,
                'admission_url' => $data['admission_url'] ?? null,
                'logo' => $data['logo'] ?? null,
                'logo_dark' => $data['logo_dark'] ?? null,
                'address' => $data['address'] ?? null,
                'phone_number' => $data['phone_number'] ?? null,
                'email' => $data['email'] ?? null,
//                'work_time' => $data['work_time'] ?? null,
                'map' => $data['map'] ?? null,
                'yt_url' => $data['yt_url'] ?? null,
                'favicon' => $data['favicon'] ?? null,
                'telegram' => $data['telegram'] ?? null,
                'instagram' => $data['instagram'] ?? null,
                'facebook' => $data['facebook'] ?? null,
                'youtube' => $data['youtube'] ?? null,
            ]
        );

        return redirect()->route('site_infos.index')->with([
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
