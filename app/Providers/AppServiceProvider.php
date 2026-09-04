<?php

namespace App\Providers;

use App\Models\Config;
use App\Models\ConfigGroup;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Models\Lang;
use App\Models\SiteInfo;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();

        $main_lang = null;
        if (Schema::hasTable('langs')) {
            $main_lang = Lang::where('is_main', 1)
                ->first();
        }
        View::share('main_lang', $main_lang);
        /////////////////////////////////////////////////////////////////
        $menu_items = null;
        $menu_items_groups = null;
        if (Schema::hasTable('config') && Schema::hasTable('config_groups')) {
            $menu_items = Config::all();

            $menu_items_groups = ConfigGroup::all();
        }
        View::share('menu_items', $menu_items);
        View::share('menu_items_groups', $menu_items_groups);
        //////////////////////////////////////////////////////////////////
        $site_info = null;
        if (Schema::hasTable('site_infos') && isset(SiteInfo::all()[0])) {
            $site_info = SiteInfo::first();
        }
        View::share('site_info', $site_info);
    }
}
