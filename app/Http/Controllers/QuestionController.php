<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\Lang;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public $title = 'Koʻp beriladigan savollar';
    public $route_name = 'questions';
    public $route_parameter = 'question';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $questions = Question::latest()
            ->paginate(12);
        $languages = Lang::all();

        return view('app.questions.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'questions' => $questions,
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

        return view('app.questions.create', [
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
            'question.'.$this->main_lang->code => 'required',
            'answer.'.$this->main_lang->code => 'required'
        ]);
        if ($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Maʼlumotlar toʻgʻri toʻldirilmagan'
            ]);
        }

        Question::create($data);

        return redirect()->route('questions.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Question $question)
    {
        $langs = Lang::all();

        return view('app.questions.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'question' => $question
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Question $question)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'question.'.$this->main_lang->code => 'required',
            'answer.'.$this->main_lang->code => 'required'
        ]);
        if ($validator->fails()) {
            return back()->withInput()->with([
                'success' => false,
                'message' => 'Maʼlumotlar toʻgʻri toʻldirilmagan'
            ]);
        }

        $question->update($data);

        return redirect()->route('questions.index')->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli saqlandi'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Question $question)
    {
        $question->delete();

        return back()->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli oʻchirildi'
        ]);
    }
}
