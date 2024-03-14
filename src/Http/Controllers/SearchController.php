<?php

namespace Trendyminds\Palette\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Statamic;

class SearchController extends CpController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // Don't load if Palette is disabled
        if (! config('statamic.palette.enabled')) {
            return response()->json([]);
        }

        // Don't load if the control panel is disabled
        if (! config('statamic.cp.enabled')) {
            return response()->json([]);
        }

        // Query Statamic's search results and return them as JSON
        $query = Statamic::tag('search:results')
            ->index('palette')
            ->limit(10)
            ->for($request->get('q'))
            ->fetch()
            ->map(function ($result) {
                $icon = 'document';
                $subtitle = str($result->getType())->ucfirst()->value();

                if ($result->getType() === 'entry') {
                    $subtitle = $result->collection->title;
                }

                if ($result->getType() === 'user') {
                    $icon = 'user';
                }

                if ($result->getType() === 'asset') {
                    $icon = 'attachment';
                }

                return [
                    'name' => $result->title,
                    'type' => 'link',
                    'url' => $result->edit_url,
                    'subtitle' => $subtitle,
                    'icon' => $icon,
                ];
            })->toArray();

        return response()->json($query);
    }
}
