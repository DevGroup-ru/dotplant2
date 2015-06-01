<?php

use yii\db\Schema;
use yii\db\Migration;

class m141210_111847_shop_deep_cat_bindings extends Migration
{
    public function up()
    {
        $shop = \app\models\Config::find()->where(['name' => 'Shop'])->one();
        $this->insert(\app\models\Config::tableName(), [
            'parent_id' => $shop->id,
            'name' => 'Show products of child categories',
            'key' => 'showProductsOfChildCategories',
            'value' => 1,
            'preload' => 1,
            'path' => $shop->path . '.showProductsOfChildCategories',
        ]);
    }

    public function down()
    {
        $this->delete(\app\models\Config::tableName(), ['key' => 'showProductsOfChildCategories']);
    }
}
