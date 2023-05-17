<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $modules = config('lv_modules', []);

        foreach ($modules as $module) {
            $this->app->bind("App\Repositories\\{$module}Repository", "App\Repositories\\{$module}Repository");
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
