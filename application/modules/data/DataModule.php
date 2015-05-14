<?php

namespace app\modules\data;

use app;
use app\components\BaseModule;
use Yii;

class DataModule extends BaseModule
{

    public $exportDirPath = '@app/modules/data/files/export';
    public $importDirPath = '@app/modules/data/files/import';

    public $defaultType = null;

    public $exportDir;
    public $importDir;

    public function init()
    {
        parent::init();
        $this->exportDir = Yii::getAlias($this->exportDirPath);
        $this->importDir = Yii::getAlias($this->importDirPath);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'configurableModule' => [
                'class' => 'app\modules\config\behaviors\ConfigurableModuleBehavior',
                'configurationView' => '@app/modules/data/views/configurable/_config',
                'configurableModel' => 'app\modules\data\models\ConfigConfigurableModel',
            ]
        ];
    }
}
 