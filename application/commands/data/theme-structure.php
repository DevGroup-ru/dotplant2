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
        'name' => 'dist',
        'type' => 'dir',
        'children' => [
            [
                'name' => 'styles',
                'type' => 'dir',
            ],
            [
                'name' => 'images',
                'type' => 'dir',
            ],
            [
                'name' => 'scripts',
                'type' => 'dir',
            ],
        ],
    ],
    [
        'name' => 'sass',
        'type' => 'dir',
        'children' => [
            [
                'name' => 'main.scss',
                'content' => "body {\n\tbackground-color: #fff;\n}\n",
            ]
        ],
    ],
    [
        'name' => 'gulpfile.js',
        'content' => file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'gulpfile.js'),
    ],
    [
        'name' => 'package.json',
        'content' => file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'package.json'),
    ],
    [
        'name' => '.gitignore',
        'content' => file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'gitignore.txt'),
    ],
    [
        'name' => '.jshintrc',
        'content' => file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'jshintrc'),
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
            [
                'name' => 'main.js',
                'content' => "$(function(){\n\t// your awesome code hoes here\n});\n",
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
                        'content' => file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'theme-asset.tmpl'),
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
                'name' => 'widgets',
                'type' => 'dir',
            ],
            [
                'name' => '.htaccess',
                'content' => 'Deny from All',
            ],
            [
                'name' => 'ThemeModule.php',
                'content' => file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'ThemeModule.tmpl'),
            ]
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
                'name' => 'modules',
                'type' => 'dir',
                'children' => [
                    [
                        'name' => 'basic',
                        'type' => 'dir',
                        'children' => [
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
                        ],
                    ],
                    [
                        'name' => 'shop',
                        'type' => 'dir',
                        'children' => [
                            [
                                'name' => 'cart',
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
                        ],
                    ],
                    [
                        'name' => 'page',
                        'type' => 'dir',
                        'children' => [
                            [
                                'name' => 'page',
                                'type' => 'dir',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'templates',
                'type' => 'dir',
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
                        'name' => 'navigation',
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
