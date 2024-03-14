<?php

namespace Trendyminds\Palette\Tags;

use Statamic\Tags\Tags;

class PaletteTags extends Tags
{
    protected static $handle = 'palette';

    public function index(): string
    {
        if (! config('statamic.palette.enabled')) {
            return '';
        }

        if (! config('statamic.cp.enabled')) {
            return '';
        }

        // Get the compiled file out of the manifest
        $manifest = public_path('vendor/statamic-palette/build/manifest.json');
        $assets = json_decode(file_get_contents($manifest));
        $accessFile = collect($assets)
            ->filter(fn ($asset) => str($asset->file)->contains('assets/access-'))
            ->pluck('file')
            ->first();

        $script = "/vendor/statamic-palette/build/$accessFile";

        return "<script src=\"$script\" defer></script>";
    }
}
