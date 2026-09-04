<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\Validator;
use App\Models\Lang;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MemberController extends Controller
{
    public $title = 'Talabalar';
    public $route_name = 'students';
    public $route_parameter = 'student';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $members = Member::latest()
            ->paginate(12);
        $languages = Lang::all();

        return view('app.members.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'members' => $members,
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

        return view('app.members.create', [
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

        $validator = Validator::make($data, [
            'name.'.$this->main_lang->code => 'required'
        ]);
        if ($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Validation error'
            ]);
        }
        $data['slug'] = Str::slug($data['name'][$this->main_lang->code], '-');
        if(Member::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $data['slug'].'-'.time();
        }

        if (isset($data['dropzone_images'])) {
            $data['img'] = $data['dropzone_images'];
        }

        Member::create($data);

        return redirect()->route('students.index')->with([
            'success' => true,
            'message' => 'Saved successfully'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Member  $member
     * @return \Illuminate\Http\Response
     */
    public function show(Member $member)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Member  $member
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $langs = Lang::all();

        $member = Member::find($id);

        return view('app.members.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'member' => $member
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Member  $member
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'name.'.$this->main_lang->code => 'required'
        ]);
        if ($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Validation error'
            ]);
        }
        $member = Member::find($id);


        if (isset($data['dropzone_images'])) {
            $data['img'] = $data['dropzone_images'];
        } else {
            $data['img'] = null;
        }

        $member->update($data);

        return redirect()->route('students.index')->with([
            'success' => true,
            'message' => 'Saved successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Member  $member
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {
        // Sertifikatni id orqali topish
        $member = Member::find($id);

        // Agar sertifikat mavjud bo'lmasa, xatolik xabarini ko'rsatish
        if (!$member) {
            return back()->with([
                'success' => false,
                'message' => 'students not found'
            ]);
        }

        // Sertifikatni o'chirish
        $member->delete();

        // Qayta yo'naltirish va muvaffaqiyat xabarini ko'rsatish
        return back()->with([
            'success' => true,
            'message' => 'Successfully removed'
        ]);
    }
}
