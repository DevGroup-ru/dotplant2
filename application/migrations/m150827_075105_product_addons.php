<?php

use yii\db\Migration;
use app\modules\shop\models\Addon;
use app\backend\models\BackendMenu;

class m150827_075105_product_addons extends Migration
{

    public function up()
    {
        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=MyISAM'
            : null;

        $this->insert('{{%object}}', [
            'name' => 'Addon',
            'object_class' => Addon::className(),
            'object_table_name' => Yii::$app->db->schema->getRawTableName(Addon::tableName()),
            'column_properties_table_name' => Yii::$app->db->schema->getRawTableName('{{%addon_property}}'),
            'eav_table_name' => Yii::$app->db->schema->getRawTableName('{{%addon_eav}}'),
            'categories_table_name' => Yii::$app->db->schema->getRawTableName('{{%addon_category}}'),
            'link_slug_category' => Yii::$app->db->schema->getRawTableName('{{%addon_category_full_slug}}'),
            'link_slug_static_value' => Yii::$app->db->schema->getRawTableName(
                '{{%addon_static_value_category}}'
            ),
            'object_slug_attribute' => 'slug',
        ]);

        $this->createTable('{{%addon}}', [
            'id' => $this->primaryKey(),
            'name' => $this->text()->notNull(),

            'price' => $this->float()->notNull()->defaultValue(0),
            'currency_id' => $this->integer()->notNull()->defaultValue(0),

            // price is koef. to:
            // - product.price if add_to_order=0
            // - total order price without addons if add_to_order=1
            'price_is_multiplier' => $this->boolean()->notNull()->defaultValue(0),

            // 0 for non-catalog item(ie. service), product_id for catalog item
            // in case product_id specified and name empty - product.name will be used
            // in case no images specified for addon - product images will be used
            'is_product_id' => $this->integer()->notNull()->defaultValue(0),

            // add to order as individual item, don't bind to product
            // will be implemented later
            'add_to_order' => $this->boolean()->notNull()->defaultValue(0),

            // addons grouped into categories for convenience
            'addon_category_id' => $this->integer()->notNull(),

            'can_change_quantity' => $this->boolean()->notNull()->defaultValue(0),

            'measure_id' => $this->integer()->notNull()->defaultValue(1),
        ], $tableOptions);

        $this->createIndex('by_category', '{{%addon}}', ['addon_category_id']);
        if ($this->db->driverName === 'mysql') {
            // add fulltext index in mysql for name column :-)
            $this->execute("ALTER TABLE {{%addon}} ADD FULLTEXT(`name`)");
        }

        $this->createTable('{{%addon_category}}', [
            'id' => $this->primaryKey(),
            'name' => $this->text()->notNull(),
        ]);

        // real bindings of addons to objects
        $this->createTable('{{%addon_bindings}}', [
            'id' => $this->primaryKey(),
            'addon_id' => $this->integer()->notNull(),
            'appliance_object_id' => $this->integer()->notNull(),
            'object_model_id' => $this->integer()->notNull(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
        ], $tableOptions);

        $this->createIndex('addons4object', '{{%addon_bindings}}', ['appliance_object_id', 'object_model_id']);

        $this->addColumn('{{%order_item}}', 'addon_id', $this->integer()->notNull()->defaultValue(0));

        $tblMenu = '{{%backend_menu}}';
        /** @var BackendMenu $shopMenuItem */
        $shopMenuItem = BackendMenu::findOne([
            'name' => 'Shop',
        ]);
        $this->batchInsert($tblMenu,
            [
                'parent_id',
                'name',
                'route',
                'icon',
                'sort_order',
                'added_by_ext',
                'rbac_check',
                'css_class',
                'translation_category'
            ],
            [
                [$shopMenuItem->id, 'Addons', 'shop/backend-addons/index', 'cart-plus', 0, 'core', 'product manage', '', 'app'],
            ]
        );
        \yii\caching\TagDependency::invalidate(
            Yii::$app->cache,
            [
                \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(\app\backend\models\BackendMenu::className())
            ]
        );
    }

    public function down()
    {
        $this->dropTable('{{%addon}}');
        $this->dropTable('{{%addon_category}}');
        $this->dropTable('{{%addon_bindings}}');
        $this->delete('{{%object}}', ['name' => 'Addon']);

        $this->delete('{{%backend_menu}}', ['name' => 'Addons']);
        \yii\caching\TagDependency::invalidate(
            Yii::$app->cache,
            [
                \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(\app\backend\models\BackendMenu::className())
            ]
        );
        $this->dropColumn('{{%order_item}}', 'addon_id');
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
