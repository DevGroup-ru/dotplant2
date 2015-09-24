<?php

use app\modules\shop\models\Category;
use app\modules\shop\models\Product;
use yii\db\Expression;
use yii\db\Migration;

class m150923_101638_date_modified extends Migration
{

    public function up()
    {
        $this->addColumn(
            Category::tableName(),
            'date_added',
            $this->timestamp() . new Expression(' DEFAULT CURRENT_TIMESTAMP')
        ); // https://github.com/yiisoft/yii2/issues/9337

        $this->addColumn(Category::tableName(), 'date_modified', $this->timestamp() . ' NULL');
        $this->addColumn(
            Product::tableName(),
            'date_added',
            $this->timestamp() . new Expression(' DEFAULT CURRENT_TIMESTAMP')
        );
        $this->addColumn(Product::tableName(), 'date_modified', $this->timestamp() . ' NULL');
    }

    public function down()
    {
        $this->dropColumn(Category::tableName(), 'date_added');
        $this->dropColumn(Category::tableName(), 'date_modified');
        $this->dropColumn(Product::tableName(), 'date_added');
        $this->dropColumn(Product::tableName(), 'date_modified');
    }

}
