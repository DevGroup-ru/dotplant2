<?php

namespace app\data;

use Yii;

class Module extends \yii\base\Module
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
 