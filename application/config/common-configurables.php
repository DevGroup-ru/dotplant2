<?php

/*
 * ! WARNING !
 *
 * This file is auto-generated.
 * Please don't modify it by-hand or all your changes can be lost.
 */



return[
    'modules' => [
        'data' => [
            'exportDirPath' => '@app/modules/data/files/export',
            'importDirPath' => '@app/modules/data/files/import',
            'defaultType' => 'csv',
        ],
        'image' => [
            'defaultThumbnailSize' => '80x80',
            'noImageSrc' => 'http://placehold.it/300&text=Image+not+found',
            'thumbnailsDirectory' => 'thumbnail',
            'useWatermark' => '1',
            'watermarkDirectory' => 'watermark',
            'defaultComponent' => 'fs',
            'components' => [
                'fs' => [
                    'necessary' => [
                        'class' => 'creocoder\\flysystem\\LocalFilesystem',
                        'path' => '@webroot/files',
                        'active' => '1',
                        'srcAdapter' => 'app\\modules\\image\\components\\Local',
                    ],
                    'unnecessary' => [
                        'cache' => '',
                        'replica' => 'ftpFs',
                    ],
                ],
            ],
        ],
    ],
    'components' => [
        'fs' => [
            'class' => 'creocoder\\flysystem\\LocalFilesystem',
            'path' => '@webroot/files'
        ],
    ],
];

