<?php

namespace app\modules\image;

use app\components\BaseModule;

class ImageModule extends BaseModule
{
    public $defaultThumbnailSize = '80x80';
    public $noImageSrc = 'http://placehold.it/300&text=Image+not+found';
    public $thumbnailsDirectory = 'thumbnail';
    public $useWatermark = 0;
    public $watermarkDirectory = 'watermark';
    public $components = [
        'fs' => [
            'necessary' => [
                'class' => 'creocoder\flysystem\LocalFilesystem',
                'path' => '@webroot/files',
                'active' => true,
            ],
            'unnecessary' => [
                'cache' => '',
                'replica' => '',
            ],
        ],
        'ftpFs' => [
            'necessary' => [
                'class' => 'creocoder\flysystem\FtpFilesystem',
                'host' => 'ftp.example.com',
                'active' => false,
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
        'nullFs' => [
            'necessary' => [
                'class' => 'creocoder\flysystem\NullFilesystem',
                'active' => false,
            ],
            'unnecessary' => [
                'cache' => '',
                'replica' => '',
            ],
        ],
        'awss3Fs' => [
            'necessary' => [
                'class' => 'creocoder\flysystem\AwsS3Filesystem',
                'key' => 'your-key',
                'secret' => 'your-secret',
                'bucket' => 'your-bucket',
                'active' => false,
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
        'azureFs' => [
            'necessary' => [
                'class' => 'creocoder\flysystem\AzureFilesystem',
                'accountName' => 'your-account-name',
                'accountKey' => 'your-account-key',
                'container' => 'your-container',
                'active' => false,
            ],
            'unnecessary' => [
                'cache' => '',
                'replica' => '',
            ],
        ],
        'copyFs' => [
            'necessary' => [
                'class' => 'creocoder\flysystem\CopyFilesystem',
                'consumerKey' => 'your-consumer-key',
                'consumerSecret' => 'your-consumer-secret',
                'accessToken' => 'your-access-token',
                'tokenSecret' => 'your-token-secret',
                'active' => false,
            ],
            'unnecessary' => [
                'prefix' => '',
                'cache' => '',
                'replica' => '',
            ],
        ],
        'dropboxFs' => [
            'necessary' => [
                'class' => 'creocoder\flysystem\DropboxFilesystem',
                'token' => 'your-token',
                'app' => 'your-app',
                'active' => false,
            ],
            'unnecessary' => [
                'prefix' => '',
                'cache' => '',
                'replica' => '',
            ],
        ],
        'gridFs' => [
            'necessary' => [
                'class' => 'creocoder\flysystem\GridFSFilesystem',
                'server' => 'mongodb://localhost:27017',
                'database' => 'your-database',
                'active' => false,
            ],
            'unnecessary' => [
                'cache' => '',
                'replica' => '',
            ],
        ],
        'rackspaceFs' => [
            'necessary' => [
                'class' => 'creocoder\flysystem\RackspaceFilesystem',
                'endpoint' => 'your-endpoint',
                'region' => 'your-region',
                'username' => 'your-username',
                'apiKey' => 'your-api-key',
                'container' => 'your-container',
                'active' => false,
            ],
            'unnecessary' => [
                'prefix' => '',
                'cache' => '',
                'replica' => '',
            ],
        ],
        'sftpFs' => [
            'necessary' => [
                'class' => 'creocoder\flysystem\SftpFilesystem',
                'host' => 'sftp.example.com',
                'username' => 'your-username',
                'password' => 'your-password',
                'active' => false,
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
        'webdavFs' => [
            'necessary' => [
                'class' => 'creocoder\flysystem\WebDAVFilesystem',
                'baseUri' => 'your-base-uri',
                'active' => false,
            ],
            'unnecessary' => [
                'userName' => '',
                'password' => '',
                'proxy' => '',
                'prefix' => '',
                'cache' => '',
                'replica' => '',
            ],
        ],
        'ziparchiveFs' => [
            'necessary' => [
                'class' => 'creocoder\flysystem\ZipArchiveFilesystem',
                'path' => '@webroot/files/archive.zip',
                'active' => false,
            ],
            'unnecessary' => [
                'prefix' => '',
                'cache' => '',
                'replica' => '',
            ],
        ],
    ];

    public function behaviors()
    {
        return [
            'configurableModule' => [
                'class' => 'app\modules\config\behaviors\ConfigurableModuleBehavior',
                'configurationView' => '@app/modules/image/views/configurable/_config',
                'configurableModel' => 'app\modules\image\models\ConfigConfigurationModel',
            ]
        ];
    }
}
