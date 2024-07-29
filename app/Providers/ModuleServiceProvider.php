<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $modules = config('app.modules');
        foreach ($modules as $module) {
            $this->loadModule($module);
            error_log('Initialized ModuleServiceProvider : '.$module);
        }
    }

    /**
     * Load modules.
     */
    protected function loadModule($module)
    {
        $modulePath = app_path("Modules/{$module}");

        if (File::exists("{$modulePath}/routes.php")) {
            $this->loadRoutesFrom("{$modulePath}/routes.php");
        }

        if (File::isDirectory("{$modulePath}/Views")) {
            $this->loadViewsFrom("{$modulePath}/Views", $module);
        }

        if (File::isDirectory("{$modulePath}/lang")) {
            $this->loadTranslationsFrom("{$modulePath}/lang", $module);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
