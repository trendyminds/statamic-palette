<?php

namespace Trendyminds\Palette\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Auth\User;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Collection;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Entry;
use Statamic\Facades\Fieldset;
use Statamic\Facades\Form;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Search;
use Statamic\Facades\Site;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Utility;
use Statamic\Http\Controllers\CP\CpController;

class ActionsController extends CpController
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

        return response()->json([
            ...$this->_getRouteContextActions(),
            ...$this->_getContextActions(),
            ...$this->_navigationActions(),
            ...$this->_getCollectionActions(),
            ...$this->_adminActions(),
            ...$this->_utilityActions(),
            ...$this->_userActions(),
            ...$this->_customActions(),
        ]);
    }

    private function _userActions(): array
    {
        return [
            [
                'name' => 'Edit your profile',
                'subtitle' => 'User',
                'icon' => 'user',
                'type' => 'link',
                'url' => route('statamic.cp.account'),
            ],
            [
                'name' => 'Logout',
                'subtitle' => 'User',
                'icon' => 'logout',
                'type' => 'link',
                'url' => route('statamic.cp.logout'),
            ],
        ];
    }

    private function _getCollectionActions(): array
    {
        return Collection::all()->map(fn ($collection) => [
            'name' => $collection->title(),
            'subtitle' => 'Collection',
            'icon' => 'document',
            'type' => 'link',
            'url' => cp_route('collections.show', $collection),
        ])->toArray();
    }

    private function _navigationActions(): array
    {
        $actions = Nav::build()
            ->map(fn ($section) => collect($section['items'])->map(fn ($item) => [
                'name' => $item->display(),
                'type' => 'link',
                'url' => $item->url(),
                'subtitle' => '',
                'icon' => 'menu',
            ])
            )
            ->flatten(1);

        if ($this->_isCpRequest()) {
            $actions->prepend([
                'name' => env('APP_NAME', 'Laravel'),
                'subtitle' => 'Go to the homepage',
                'icon' => 'globe',
                'type' => 'link',
                'url' => Site::selected()->url(),
            ]);
        }

        return $actions->toArray();
    }

    private function _utilityActions(): array
    {
        return Utility::authorized()->map(fn ($utility) => [
            'name' => $utility->title(),
            'subtitle' => 'Utility',
            'icon' => 'utility',
            'type' => 'link',
            'url' => $utility->url(),
        ])->values()->toArray();
    }

    private function _adminActions(): array
    {
        // Return nothing if the user isn't a super admin
        if (! User::current()->super) {
            return [];
        }

        $fieldsets = Fieldset::all()->map(fn ($fieldset) => [
            'name' => $fieldset->title(),
            'subtitle' => 'Fieldset',
            'icon' => 'database',
            'type' => 'link',
            'url' => cp_route('fieldsets.edit', $fieldset->handle()),
        ])->values();

        $blueprints = Collection::all()->map(fn ($collection) => $collection->entryBlueprints()->map(fn ($blueprint) => [
            'name' => $blueprint->title(),
            'subtitle' => 'Blueprints → Collections',
            'icon' => 'database',
            'type' => 'link',
            'url' => cp_route('collections.blueprints.edit', [$collection, $blueprint]),
        ]
        ))->flatten(1);

        $taxonomies = Taxonomy::all()->map(fn ($taxonomy) => $taxonomy->termBlueprints()->map(fn ($blueprint) => [
            'name' => $blueprint->title(),
            'subtitle' => 'Blueprints → Taxonomies',
            'icon' => 'database',
            'type' => 'link',
            'url' => cp_route('taxonomies.blueprints.edit', [$taxonomy, $blueprint]),
        ]
        ))->flatten(1);

        $navs = \Statamic\Facades\Nav::all()->map(fn ($nav) => [
            'name' => $nav->title(),
            'subtitle' => 'Blueprints → Navigation',
            'icon' => 'database',
            'type' => 'link',
            'url' => cp_route('navigation.blueprint.edit', $nav->handle()),
        ]);

        $globals = GlobalSet::all()->map(fn ($global) => [
            'name' => $global->title(),
            'subtitle' => 'Blueprints → Globals',
            'icon' => 'database',
            'type' => 'link',
            'url' => cp_route('globals.blueprint.edit', $global->handle()),
        ]);

        $assets = AssetContainer::all()->map(fn ($asset) => [
            'name' => $asset->title(),
            'subtitle' => 'Blueprints → Asset Containers',
            'icon' => 'database',
            'type' => 'link',
            'url' => cp_route('asset-containers.blueprint.edit', $asset->handle()),
        ]);

        $forms = Form::all()->map(fn ($form) => [
            'name' => $form->title(),
            'subtitle' => 'Blueprints → Forms',
            'icon' => 'database',
            'type' => 'link',
            'url' => cp_route('forms.blueprint.edit', $form->handle()),
        ]);

        $users = [
            [
                'name' => 'User',
                'subtitle' => 'Blueprints → Users',
                'icon' => 'database',
                'type' => 'link',
                'url' => cp_route('users.blueprint.edit'),
            ],
            [
                'name' => 'Group',
                'subtitle' => 'Blueprints → Users',
                'icon' => 'database',
                'type' => 'link',
                'url' => cp_route('user-groups.blueprint.edit'),
            ],
        ];

        return [
            ...$fieldsets,
            ...$blueprints,
            ...$taxonomies,
            ...$navs,
            ...$globals,
            ...$assets,
            ...$forms,
            ...$users,
        ];
    }

    private function _isCpRequest(): bool
    {
        // Check the refering URL (it's the only way to know what page called the action)
        $referer = request()->headers->get('referer');

        $uri = str($referer)->replace(config('app.url'), '')->replaceFirst('/', '');

        // Parse the segments from the URI
        $segments = explode('/', $uri);

        // If the first segment is the cpTrigger we know this is a control panel request
        if ($segments && $segments[0] === config('statamic.cp.route')) {
            return true;
        }

        return false;
    }

    private function _getContextActions(): array
    {
        // Don't load the ability to search the site content if the palette search context doesn't exist
        if (! Search::indexes()->get('palette')) {
            return [];
        }

        return [
            [
                'name' => 'Find content',
                'subtitle' => 'Query for entries, assets, and users across the site',
                'icon' => 'search',
                'type' => 'context',
                'url' => 'SEARCH_ENTRIES',
            ],
        ];
    }

    private function _getRouteContextActions(): array
    {
        // Output an empty array if this is a control panel request
        if ($this->_isCpRequest()) {
            return [];
        }

        try {
            // Unfortunately the only way to find the entry the user is on is by getting the referring URL
            $referer = request()->headers->get('referer');

            // Normalize the referer and the base URL
            $referer = rtrim($referer, '/').'/';
            $url = rtrim(config('app.url'), '/').'/';

            // Pluck out the URI using the referer and the base URL
            $uri = str_replace($url, '', $referer);

            // Normalize the URI
            $uri = rtrim($uri, '/');

            // Remove any query strings and decode any special characters
            $uri = str($uri)->replaceMatches('/\?.*/', '');
            $uri = urldecode($uri);
            $uri = "/$uri";

            // Find the matching element
            $element = Entry::findByUri($uri);

            // Return an empty array if no element was found
            if (! $element) {
                return [];
            }

            return [
                [
                    'name' => $element->title,
                    'type' => 'link',
                    'url' => $element->edit_url,
                    'subtitle' => 'Edit this entry within Statamic',
                    'icon' => 'edit',
                ],
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    private function _customActions(): array
    {
        $customUrls = config('statamic.palette.customUrls');

        // If the user supplied a closure we need to invoke it and get the result
        if ($customUrls instanceof \Closure) {
            $customUrls = $customUrls();
        }

        return collect($customUrls)
            ->map(fn ($item) => [
                'name' => $item['name'] ?? '',
                'type' => 'link',
                'url' => $item['url'] ?? '',
                'subtitle' => $item['subtitle'] ?? '',
            ])
            ->toArray();
    }
}
