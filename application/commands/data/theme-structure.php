<?php

return [
    [
        'name' => 'css',
        'type' => 'dir',
    ],
    [
        'name' => 'fonts',
        'type' => 'dir',
    ],
    [
        'name' => 'images',
        'type' => 'dir',
    ],
    [
        'name' => 'js',
        'type' => 'dir',
    ],
    [
        'name' => 'module',
        'type' => 'dir',
        'children' => [
            [
                'name' => 'assets',
                'type' => 'dir',
                'children' => [
                    [
                        'name' => 'ThemeAsset.php',
                        'content' => file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'theme-asset.tmlp'),
                    ],
                ],
            ],
            [
                'name' => 'components',
                'type' => 'dir',
            ],
            [
                'name' => 'controllers',
                'type' => 'dir',
            ],
            [
                'name' => 'config',
                'type' => 'dir',
                'children' => [
                    [
                        'name' => 'web.php',
                        'content' => file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'web-config.tmpl'),
                    ],
                ],
            ],
            [
                'name' => 'migrations',
                'type' => 'dir',
            ],
            [
                'name' => 'models',
                'type' => 'dir',
            ],
            [
                'name' => 'views',
                'type' => 'dir',
            ],
            [
                'name' => 'widgets',
                'type' => 'dir',
            ],
        ],
    ],
    [
        'name' => 'resources',
        'type' => 'dir',
        'children' => [
            [
                'name' => 'product-images',
                'type' => 'dir',
                'writable' => true,
            ],
        ],
    ],
    [
        'name' => 'views',
        'type' => 'dir',
        'children' => [
            [
                'name' => 'controllers',
                'type' => 'dir',
                'children' => [
                    [
                        'name' => 'cart',
                        'type' => 'dir',
                    ],
                    [
                        'name' => 'default',
                        'type' => 'dir',
                    ],
                    [
                        'name' => 'layouts',
                        'type' => 'dir',
                        'children' => [
                            [
                                'name' => 'main.php',
                                'content' => file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'layout.tmpl'),
                            ],
                        ],
                    ],
                    [
                        'name' => 'page',
                        'type' => 'dir',
                    ],
                    [
                        'name' => 'product',
                        'type' => 'dir',
                    ],
                    [
                        'name' => 'product-compare',
                        'type' => 'dir',
                    ],
                    [
                        'name' => 'templates',
                        'type' => 'dir',
                    ],
                ],
            ],
            [
                'name' => 'widgets',
                'type' => 'dir',
                'children' => [
                    [
                        'name' => 'form',
                        'type' => 'dir',
                    ],
                    [
                        'name' => 'navigation ',
                        'type' => 'dir',
                    ],
                ],
            ],
        ],
    ],
];
