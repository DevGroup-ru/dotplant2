<?php

namespace app\backend\widgets\filterForm;

use app\modules\shop\models\Category;
use yii\helpers\ArrayHelper;
use Yii;

class filterFormCategory extends filterForm {
    public $fieldName = 'category';
    public $andConditions = [
        'AND',
    ];
    public $operators = [];

    public function init() {

        $this->fieldLabel = Yii::t('app', 'Filter By Category');

        return parent::init();
    }


    public function getData() {

        $this->data = ArrayHelper::map(Category::find()->asArray()->all(), 'id', 'name');

        return parent::getData();
    }

} 