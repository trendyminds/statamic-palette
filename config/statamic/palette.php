<?php

return [
    /**
     * Whether Palette should be enabled
     */
    'enabled' => env('PALETTE_ENABLED', true),

    /**
     * Custom URLs to include in Palette's list
     *
     * This can be a simple set of array values:
     * 'customUrls' => [
     *     ['name' => 'My URL', 'url' => '/path/to/url', 'subtitle' => 'Optional text'],
     *  ],
     *
     * Or it can utilize a closure for something more dynamic:
     * 'customUrls' => function() {
     *     $url = '/path/to/url';
     *     return [
     *         ['name' => 'My URL', 'url' => $url, 'subtitle' => 'Optional text']
     *     ];
     * }
     */
    'customUrls' => [],
];
