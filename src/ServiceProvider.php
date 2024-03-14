<?php

namespace Trendyminds\Palette;

use Illuminate\Support\Facades\Route;
use Statamic\Providers\AddonServiceProvider;
use Trendyminds\Palette\Http\Controllers\AccessController;
use Trendyminds\Palette\Http\Controllers\ActionsController;
use Trendyminds\Palette\Http\Controllers\SearchController;
use Trendyminds\Palette\Tags\PaletteTags;

class ServiceProvider extends AddonServiceProvider
{
    protected $vite = [
        'input' => ['resources/js/access.js'],
        'publicDirectory' => 'resources/dist',
    ];

    protected $tags = [
        PaletteTags::class,
    ];

    public function bootAddon()
    {
        $this->publishes([
            __DIR__.'/../config/statamic/palette.php' => config_path('statamic/palette.php'),
        ], 'palette-config');

        $this->registerActionRoutes(function () {
            Route::get('access', AccessController::class);

            Route::get('actions', ActionsController::class)
                ->middleware('statamic.cp.authenticated');

            Route::get('search', SearchController::class)
                ->middleware('statamic.cp.authenticated');
        });
    }

    public function register()
    {
        parent::register();

        $this->mergeConfigFrom(__DIR__.'/../config/statamic/palette.php', 'statamic.palette');
    }
}
