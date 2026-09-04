<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\Lang;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    public $title = 'Sertifikatlar';
    public $route_name = 'certificates';
    public $route_parameter = 'certificate';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $certificates = Certificate::latest()
            ->paginate(12);
        $languages = Lang::all();

        return view('app.certificates.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'certificates' => $certificates,
            'languages' => $languages
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $langs = Lang::all();

        return view('app.certificates.create', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs
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

        // Validatsiya
        $validator = Validator::make($data, [
            'title.' . $this->main_lang->code => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Maʼlumotlar toʻgʻri toʻldirilmagan'
            ]);
        }

        // Faylni yuklash
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Faylni tasodifiy nom bilan saqlash (12 ta tasodifiy harf + kengaytma)
            $fileName = Str::random(12) . '.' . $file->getClientOriginalExtension();

            // Faylni public/upload/certificates papkasiga saqlash
            $file->move(public_path('upload/certificates'), $fileName);

            // Fayl nomini data massivi bilan qo'shish
            $data['file'] = $fileName;  // Yangi nomni $data['file']ga saqlash
        }

        // Dropzone rasmlarini qo'shish
        if (isset($data['dropzone_images'])) {
            $data['img'] = $data['dropzone_images'];
        }

        // Ma'lumotni bazaga saqlash
        Certificate::create($data);

        return redirect()->route('certificates.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Certificate  $certificate
     * @return \Illuminate\Http\Response
     */
    public function show(Certificate $certificate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Certificate  $certificate
     * @return \Illuminate\Http\Response
     */
    public function edit(Certificate $certificate)
    {
        $langs = Lang::all();

        return view('app.certificates.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'certificate' => $certificate
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Certificate  $certificate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Certificate $certificate)
    {
        $data = $request->all();

        // Validatsiya
        $validator = Validator::make($data, [
            'title.' . $this->main_lang->code => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Maʼlumotlar toʻgʻri toʻldirilmagan'
            ]);
        }

        // Faylni yangilash
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Faylni tasodifiy nom bilan saqlash (12 ta tasodifiy harf + kengaytma)
            $fileName = Str::random(12) . '.' . $file->getClientOriginalExtension();

            // Eski faylni o'chirish (agar bor bo'lsa)
            if ($certificate->file && file_exists(public_path('upload/certificates/' . $certificate->file))) {
                unlink(public_path('upload/certificates/' . $certificate->file));
            }

            // Faylni public/upload/certificates papkasiga saqlash
            $file->move(public_path('upload/certificates'), $fileName);

            // Yangi fayl nomini $data massivi bilan qo'shish
            $data['file'] = $fileName;
        } else {
            // Fayl mavjud bo'lmasa, eski fayl nomini saqlash
            $data['file'] = $certificate->file;
        }

        // Dropzone rasmlarini qo'shish
        if (isset($data['dropzone_images'])) {
            $data['img'] = $data['dropzone_images'];
        } else {
            $data['img'] = null;
        }

        // Ma'lumotni yangilash
        $certificate->update($data);

        return redirect()->route('certificates.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli yangilandi'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Certificate  $certificate
     * @return \Illuminate\Http\Response
     */
    public function destroy(Certificate $certificate)
    {
        $certificate->delete();

        return back()->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli oʻchirildi'
        ]);
    }
}
