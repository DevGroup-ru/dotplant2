<?php

use app\modules\shop\models\Category;
use app\models\Config;
use app\modules\page\models\Page;
use app\modules\shop\models\Product;
use yii\db\Migration;

class m150422_105109_hard_deleting extends Migration
{
    public function up()
    {
        $this->dropColumn(Category::tableName(), 'is_deleted');
        $this->dropColumn(Page::tableName(), 'is_deleted');
        $this->dropColumn(Product::tableName(), 'is_deleted');
        $shopConfig = Config::findOne(['path' => 'shop']);
        /** @var $shopConfig Config */
        if (!is_null($shopConfig)) {
            $config = new Config;
            $config->attributes = [
                'parent_id' => $shopConfig->id,
                'name' => 'Show deleted orders',
                'key' => 'showDeletedOrders',
                'value' => 0,
            ];
            $config->save();
        }
    }

    public function down()
    {
        Config::deleteAll(['path' => 'shop.showDeletedOrders']);
        $this->addColumn(Product::tableName(), 'is_deleted', 'TINYINT UNSIGNED DEFAULT \'0\'');
        $this->addColumn(Page::tableName(), 'is_deleted', 'TINYINT UNSIGNED DEFAULT \'0\'');
        $this->addColumn(Category::tableName(), 'is_deleted', 'TINYINT UNSIGNED DEFAULT \'0\'');
    }
}
