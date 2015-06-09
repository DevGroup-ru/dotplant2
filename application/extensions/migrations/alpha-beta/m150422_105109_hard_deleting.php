<?php

use app\modules\shop\models\Category;
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
    }

    public function down()
    {
        $this->addColumn(Product::tableName(), 'is_deleted', 'TINYINT UNSIGNED DEFAULT \'0\'');
        $this->addColumn(Page::tableName(), 'is_deleted', 'TINYINT UNSIGNED DEFAULT \'0\'');
        $this->addColumn(Category::tableName(), 'is_deleted', 'TINYINT UNSIGNED DEFAULT \'0\'');
    }
}
