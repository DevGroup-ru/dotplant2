<?php

namespace app\data;

use app\backend\BackendModule;
use Yii;

class Module extends BackendModule
{
    public $dataBase = '/data';

    public $exportDir = '@webroot/data/export';
    public $importDir = '@webroot/data/import';

    public function init()
    {
        parent::init();

        $this->dataBase = Yii::getAlias($this->dataBase);
        $this->exportDir = Yii::getAlias($this->exportDir);
        $this->importDir = Yii::getAlias($this->importDir);
    }
}
 