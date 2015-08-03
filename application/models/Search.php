<?php

namespace app\models;

use app\modules\page\models\Page;
use app\modules\shop\models\Product;
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
        /** @var \app\modules\shop\ShopModule $module */
        $module = Yii::$app->modules['shop'];

        $query = (new Query())
            ->select('`id`')
            ->from(Product::tableName())
            ->orWhere('`name` LIKE :q')
            ->orWhere('`h1` LIKE :q')
            ->orWhere('`content` LIKE :q')
            ->orWhere('`sku` LIKE :q')
            ->addParams([':q' => '%' . $this->q . '%'])
            ->andWhere(
                [
                    'active' => 1,
                ]
            );
        if ($module->allowSearchGeneratedProducts != 1) {
            $query->andWhere(
                [
                    'parent_id' => 0
                ]
            );
        }

        return ArrayHelper::getColumn($query->all(), 'id');
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
