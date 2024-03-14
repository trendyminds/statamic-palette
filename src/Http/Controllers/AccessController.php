<?php

namespace Trendyminds\Palette\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class AccessController extends CpController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // Don't load if Palette is disabled
        if (! config('statamic.palette.enabled')) {
            return response()->noContent();
        }

        // Don't load if the control panel is disabled
        if (! config('statamic.cp.enabled')) {
            return response()->noContent();
        }

        // We don't want to do anything for anonymous users
        if (! User::current()) {
            return response()->noContent();
        }

        // We don't want to do anything for users without control panel access
        if (User::current()->cant('access cp')) {
            return response()->noContent();
        }

        // Get the compiled files out of the manifest
        $manifest = public_path('vendor/statamic-palette/build/manifest.json');
        $assets = json_decode(file_get_contents($manifest));

        // Pull the initialization and CSS files out
        $initFile = collect($assets)
            ->filter(fn ($asset) => str($asset->file)->contains('assets/Init-'))
            ->pluck('file')
            ->first();

        $cssFile = collect($assets)
            ->filter(fn ($asset) => str($asset->file)->contains('assets/addon-'))
            ->pluck('file')
            ->first();

        return response()->json([
            'css' => "/vendor/statamic-palette/build/$cssFile",
            'js' => "/vendor/statamic-palette/build/$initFile",
        ]);
    }
}
