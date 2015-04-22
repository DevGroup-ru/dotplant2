<?php

return [
    [
        'name' => 'css',
        'type' => 'dir',
        'children' => [
            [
                'name' => 'index.html',
            ],
        ],
    ],
    [
        'name' => 'fonts',
        'type' => 'dir',
        'children' => [
            [
                'name' => 'index.html',
            ],
        ],
    ],
    [
        'name' => 'images',
        'type' => 'dir',
        'children' => [
            [
                'name' => 'index.html',
            ],
        ],
    ],
    [
        'name' => 'js',
        'type' => 'dir',
        'children' => [
            [
                'name' => 'index.html',
            ],
        ],
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
            [
                'name' => '.htaccess',
                'content' => 'Deny from All',
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
                'children' => [
                    [
                        'name' => 'thumbnail',
                        'type' => 'dir',
                    ],
                    [
                        'name' => 'watermark',
                        'type' => 'dir',
                    ],
                    [
                        'name' => '.gitignore',
                        'content' => "*\n!.gitignore\n!index.html",
                    ],
                    [
                        'name' => 'index.html',
                    ],
                ],
            ],
            [
                'name' => 'index.html',
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
            [
                'name' => '.htaccess',
                'content' => 'Deny from All',
            ],
        ],
    ],
    [
        'name' => 'index.html',
    ],
];
