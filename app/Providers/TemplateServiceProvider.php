<?php

namespace App\Providers;

use App\Http\Traits\Template;
use App\Models\Setting;
use Illuminate\Support\ServiceProvider;

class TemplateServiceProvider extends ServiceProvider
{
    use Template;
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        view()->composer('admin.includes.sidebar', function ($view) {
            $setting = Setting::first();
            $theme = $setting->theme;
            $tempBars = $this->$theme();
            $view->with('tempBars', $tempBars);
        });

    }
}
