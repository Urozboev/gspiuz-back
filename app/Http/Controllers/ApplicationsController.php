<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Lang;

class ApplicationsController extends Controller
{
    public $title = 'So\'rovlar';
    public $route_name = 'applications';
    public $route_parameter = 'application';

    public function index()
    {
        $applications = Application::latest()
            ->paginate(12);

            return view('app.applications.index', [
                'title' => $this->title,
                'route_name' => $this->route_name,
                'route_parameter' => $this->route_parameter,
                'applications' => $applications
            ]);
    }
    public function create()
    {
        $applications = Application::latest()
            ->paginate(12);
        $langs = Lang::all();

        return view('app.applications.create', [
            'title' => $this->title,
            'langs' => $langs,
            'route_name' => $this->route_name,
            'route_parameter' => $this->route_parameter,
            'applications' => $applications
        ]);
    }

    public function destroy(Application $application)
    {
        $application->delete();

        return back()->with([
            'success' => true,
            'message' => 'Muvaffaqiyatli oʻchirib tashlandi'
        ]);
    }
}
