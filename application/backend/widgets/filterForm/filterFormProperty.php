<?php

namespace app\backend\widgets\filterForm;

use yii\helpers\ArrayHelper;
use yii\db\Query;
use app\models\Property;
use app\models\PropertyGroup;
use Yii;

class filterFormProperty extends filterForm {
    public $fieldName = 'property';
    public $objectId = null;
    public $andConditions = [
        'AND',
    ];


    public function init() {

        $this->fieldLabel = Yii::t('app', 'Filter By Properties');

        return parent::init();
    }


    public function getData() {

        $query = new Query;
        $query->select(
            Property::tableName().'.id, '.Property::tableName().'.name'
        )
            ->from(Property::tableName());
            $query->leftJoin(
                PropertyGroup::tableName(),
                PropertyGroup::tableName().'.id = '.Property::tableName().'.property_group_id'
            );
            $query->andWhere([
                PropertyGroup::tableName().'.object_id' => $this->objectId
            ]);
        $command = $query->createCommand();
        $this->data = ArrayHelper::map($command->queryAll(), 'id', 'name');
        return parent::getData();
    }

} 