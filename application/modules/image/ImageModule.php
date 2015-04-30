<?php

namespace app\modules\image;

use app\components\BaseModule;

class ImageModule extends BaseModule
{
    public $defaultThumbnailSize = '80x80';
    public $noImageSrc = 'http://placehold.it/300&text=Image+not+found';
    public $thumbnailsDirectory = '/theme/resources/product-images/thumbnail';
    public $useWatermark = 0;
    public $watermarkDirectory = '/theme/resources/product-images/watermark';

    public function behaviors()
    {
        return [
            'configurableModule' => [
                'class' => 'app\modules\config\behaviors\ConfigurableModuleBehavior',
                'configurationView' => '@app/modules/image/views/configurable/_config',
                'configurableModel' => 'app\modules\image\models\ConfigConfigurableModel',
            ]
        ];
    }
}
