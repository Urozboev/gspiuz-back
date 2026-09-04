<?php

namespace App\Http\Controllers;

use App\Models\Lang;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $main_lang;

    function __construct()
    {
        $this->main_lang = Lang::where('is_main', 1)
            ->firstOrFail();
    }
}
