<?php

use app\backend\models\BackendMenu;
use app\modules\shop\models\Measure;
use app\modules\shop\models\Product;
use yii\db\Migration;

class m150518_065023_measure extends Migration
{
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        $this->createTable(
            Measure::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'name' => 'VARCHAR(255) NOT NULL',
                'symbol' => 'VARCHAR(255) NOT NULL',
                'nominal' => 'FLOAT NOT NULL',
            ],
            $tableOptions
        );
        $this->insert(
            Measure::tableName(),
            [
                'name' => Yii::t('app', 'Pieces'),
                'symbol' => Yii::t('app', 'pcs'),
                'nominal' => 1,
            ]
        );
        $this->addColumn(Product::tableName(), 'measure_id', 'INT UNSIGNED NOT NULL');
        $this->update(Product::tableName(), ['measure_id' => $this->db->lastInsertID]);
        /** @var BackendMenu $menu */
        $menu = BackendMenu::findOne(['name' => 'Shop']);
        if (!is_null($menu)) {
            $this->insert(
                BackendMenu::tableName(),
                [
                    'parent_id' => $menu->id,
                    'name' => 'Measures',
                    'route' => '/shop/backend-measure/index',
                    'icon' => 'calculator',
                    'sort_order' => '9',
                    'added_by_ext' => 'core',
                    'rbac_check' => 'shop manage',
                    'css_class' => '',
                    'translation_category' => 'app',
                ]
            );
        }
    }

    public function down()
    {
        $this->delete(BackendMenu::tableName(), ['route' => '/shop/backend-measure/index']);
        $this->dropColumn(Product::tableName(), 'measure_id');
        $this->dropTable(Measure::tableName());
    }
}
