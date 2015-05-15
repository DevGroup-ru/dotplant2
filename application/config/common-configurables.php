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
            'useWatermark' => '0',
            'watermarkDirectory' => 'watermark',
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
                        'replica' => '',
                    ],
                ],
                'ftpFs' => [
                    'necessary' => [
                        'class' => 'creocoder\\flysystem\\FtpFilesystem',
                        'host' => 'ftp.example.com',
                        'active' => '0',
                        'srcAdapter' => 'app\\modules\\image\\components\\Ftp',
                    ],
                    'unnecessary' => [
                        'port' => '',
                        'username' => '',
                        'password' => '',
                        'ssl' => '',
                        'timeout' => '',
                        'root' => '',
                        'permPrivate' => '',
                        'permPublic' => '',
                        'passive' => '',
                        'transferMode' => '',
                        'cache' => '',
                        'replica' => '',
                    ],
                ],
                'awss3Fs' => [
                    'necessary' => [
                        'class' => 'creocoder\\flysystem\\AwsS3Filesystem',
                        'key' => 'your-key',
                        'secret' => 'your-secret',
                        'bucket' => 'your-bucket',
                        'active' => '0',
                        'srcAdapter' => 'app\\modules\\image\\components\\Awss3',
                    ],
                    'unnecessary' => [
                        'region' => '',
                        'baseUrl' => '',
                        'prefix' => '',
                        'options' => '',
                        'cache' => '',
                        'replica' => '',
                    ],
                ],
                'sftpFs' => [
                    'necessary' => [
                        'class' => 'creocoder\\flysystem\\SftpFilesystem',
                        'host' => 'sftp.example.com',
                        'username' => 'your-username',
                        'password' => 'your-password',
                        'active' => '0',
                        'srcAdapter' => 'app\\modules\\image\\components\\Ftp',
                    ],
                    'unnecessary' => [
                        'port' => '',
                        'privateKey' => '',
                        'timeout' => '',
                        'root' => '',
                        'permPrivate' => '',
                        'permPublic' => '',
                        'cache' => '',
                        'replica' => '',
                    ],
                ],
            ],
        ],
    ],
    'components' => [
        'fs' => [
            'class' => 'creocoder\\flysystem\\LocalFilesystem',
            'path' => '@webroot/files',
        ],
    ],
];

