<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class Search extends Model
{
    public $q = '';

    public function attributeLabels()
    {
        return [
            'q' => \Yii::t('app', 'Do Search') . '...'
        ];
    }

    public function rules()
    {
        return [
            ['q', 'string', 'min' => 3, 'skipOnEmpty' => false],
        ];
    }

    public function searchProductsByProperty()
    {
        $result = (new Query())
            ->select('`id`')
            ->from(PropertyStaticValues::tableName())
            ->where('`name` LIKE :q')
            ->addParams([':q' => '%' . $this->q . '%'])
            ->all();
        $result = (new Query())
            ->select('`object_model_id`')
            ->distinct(true)
            ->from(ObjectStaticValues::tableName())
            ->where('`object_id` = :objectId')
            ->addParams([':objectId' => 1])
            ->andWhere(['in', '`property_static_value_id`', ArrayHelper::getColumn($result, 'id')])
            ->all();
        return ArrayHelper::getColumn($result, 'object_model_id');
    }

    public function searchProductsByDescription()
    {
        $result = (new Query())
            ->select('`id`')
            ->from(Product::tableName())
            ->orWhere('`name` LIKE :q')
            ->orWhere('`h1` LIKE :q')
            ->orWhere('`content` LIKE :q')
            ->addParams([':q' => '%' . $this->q . '%'])
            ->andWhere('active=1')
            ->all();
        return ArrayHelper::getColumn($result, 'id');
    }

    public function searchPagesByDescription()
    {
        $result = (new Query())
            ->select('`id`')
            ->from(Page::tableName())
            ->orWhere('`title` LIKE :q')
            ->orWhere('`h1` LIKE :q')
            ->orWhere('`content` LIKE :q')
            ->addParams([':q' => '%' . $this->q . '%'])
            ->andWhere('published=1')
            ->andWhere('searchable=1')
            ->all();
        return ArrayHelper::getColumn($result, 'id');
    }
}
