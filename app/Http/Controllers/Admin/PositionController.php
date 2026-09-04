<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lang;
use App\Models\Menu;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PositionController extends Controller
{
    public $title = 'Lavozimlar';
    public $route_name = 'positions';
    public $route_parameter = 'position';
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Query yaratamiz
        $positionQuery = Position::query();

        // Agar "search" parametri bo'lsa, qidiruv sharti qo'shamiz
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = trim($_GET['search']);
            $positionQuery->where('name', 'like', '%' . $search . '%'); // Menyu sarlavhasida qidirish

        }

        // Pagination va tartib
        $positions = $positionQuery->latest()
            ->paginate(12);

        // Mavjud tillar
        $languages = Lang::all();

        // View qaytariladi
        return view('admin.positions.index', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'positions' => $positions,
            'languages' => $languages,
            'search' => isset($_GET['search']) ? $_GET['search'] : '', // Qidiruv qiymati
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $langs = Lang::all();
        $positions = Position::all();

        return view('admin.positions.create', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'positions' => $positions,
            'langs' => $langs,
        ]);
    }
    /**
     * Store a newly created resource in storage.
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
        Position::create($data);

        return redirect()->route('positions.index')->with([
            'success' => true,
            'message' => 'Saved successfully'
        ]);
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
    public function edit(Position $position)
    {
        $langs = Lang::all();

        return view('admin.positions.edit', [
            'title' => $this->title,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'langs' => $langs,
            'position' => $position
        ]);
    }

    /**
     * Update the specified resource in storage.
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

         $position = Position::findOrFail($id);



        $position->update($data);

        return redirect()->route('positions.index')->with([
            'success' => true,
            'message' => 'Updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $position = Position::findOrFail($id);
        $position->delete();

        return redirect()->route('positions.index')->with([
            'success' => true,
            'message' => 'Deleted successfully'
        ]);
    }
}
