<?php

use yii\db\Schema;
use yii\db\Migration;

class m150723_095512_backend_menu_fixes extends Migration
{
    public function up()
    {
        $tbl = '{{%backend_menu}}';
        $this->update($tbl, ['route'=>'shop/backend-order/index'], ['route'=>'/shop/backend-order/index']);
        $this->update($tbl, ['route'=>'shop/backend-customer/index'], ['route'=>'/shop/backend-customer/index']);
        $this->update($tbl, ['route'=>'shop/backend-contragent/index'], ['route'=>'/shop/backend-contragent/index']);
        \yii\caching\TagDependency::invalidate(
            Yii::$app->cache,
            [
                \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(\app\backend\models\BackendMenu::className())
            ]
        );
    }

    public function down()
    {
        $tbl = '{{%backend_menu}}';
        $this->update($tbl, ['route'=>'/shop/backend-order/index'], ['route'=>'shop/backend-order/index']);
        $this->update($tbl, ['route'=>'/shop/backend-customer/index'], ['route'=>'shop/backend-customer/index']);
        $this->update($tbl, ['route'=>'/shop/backend-contragent/index'], ['route'=>'shop/backend-contragent/index']);
        \yii\caching\TagDependency::invalidate(
            Yii::$app->cache,
            [
                \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(\app\backend\models\BackendMenu::className())
            ]
        );
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
