<?php

namespace app\modules\data;

use app;
use app\components\BaseModule;
use Yii;

class DataModule extends BaseModule
{

    public $exportDir = '@app/modules/data/files/export';
    public $importDir = '@app/modules/data/files/import';

    public function init()
    {
        parent::init();
        $this->exportDir = Yii::getAlias($this->exportDir);
        $this->importDir = Yii::getAlias($this->importDir);
    }
}
 