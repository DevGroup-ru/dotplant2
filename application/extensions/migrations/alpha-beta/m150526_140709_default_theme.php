<?php

use yii\db\Schema;
use yii\db\Migration;

class m150526_140709_default_theme extends Migration
{
    public function up()
    {
        $this->insert(
            '{{%configurable}}',
            [
                'module' => 'DefaultTheme',
                'sort_order' => 2,
                'section_name' => 'Default Theme',
                'display_in_config' => 1,
            ]
        );

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%theme_parts}}',
            [
                'id' => Schema::TYPE_PK,
                'name' => Schema::TYPE_STRING,
                'key' => Schema::TYPE_STRING,
                'global_visibility' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
                'multiple_widgets' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
                'is_cacheable' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
                'cache_lifetime' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'cache_tags' => Schema::TYPE_TEXT . ' NULL',
                'cache_vary_by_session' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0',
            ],
            $tableOptions
        );

        $this->createTable('{{%theme_widgets}}',
            [
                'id' => Schema::TYPE_PK,
                'name' => Schema::TYPE_STRING,
                'widget' => Schema::TYPE_STRING,
                'preview_image' => Schema::TYPE_STRING . ' NULL',

                'configuration_model' => Schema::TYPE_STRING . ' NULL',
                'configuration_view' => Schema::TYPE_STRING . ' NULL',
                'configuration_json' => Schema::TYPE_TEXT . ' NULL',

                'is_cacheable' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
                'cache_lifetime' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'cache_tags' => Schema::TYPE_TEXT . ' NULL',
                'cache_vary_by_session' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0',
            ],
            $tableOptions
        );

        $this->createTable('{{%theme_variation}}',
            [
                'id' => Schema::TYPE_PK,
                'name' => Schema::TYPE_STRING . ' NULL',
                'by_url' => Schema::TYPE_STRING . ' NULL',
                'by_route' => Schema::TYPE_STRING . ' NULL',
                'matcher_class_name' => Schema::TYPE_STRING . ' NULL',
                'exclusive' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0',
            ],
            $tableOptions
        );


        $this->createTable('{{%theme_widget_applying}}',
            [
                'id' => Schema::TYPE_PK,
                'widget_id' => Schema::TYPE_INTEGER,
                'part_id' => Schema::TYPE_INTEGER,
            ],
            $tableOptions
        );
        $this->createIndex('widget_part', '{{%theme_widget_applying}}', ['widget_id', 'part_id'], true);

        $this->createTable('{{%theme_active_widgets}}',
            [
                'id' => Schema::TYPE_PK,
                'part_id' => Schema::TYPE_INTEGER,
                'widget_id' => Schema::TYPE_INTEGER,
                'variation_id' => Schema::TYPE_INTEGER,
                'sort_order' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            ],
            $tableOptions
        );
        $this->createIndex('variation', '{{%theme_active_widgets}}', ['variation_id']);

        $cacheLifetime = 86400;

        $baseParts = [
            [ // 1
                'name' => Yii::t('app', 'Pre-Header'),
                'key' => 'pre-header',
                'multiple_widgets' => 1,
                'cache_lifetime' => $cacheLifetime,
                'cache_tags' => '\app\widgets\navigation\models\Navigation',
            ],
            [ // 2
                'name' => Yii::t('app', 'Header'),
                'key' => 'header',
                'multiple_widgets' => 0,
                'cache_lifetime' => $cacheLifetime,
                'cache_tags' => '\app\widgets\navigation\models\Navigation',
            ],
            [ // 3
                'name' => Yii::t('app', 'Post-Header'),
                'key' => 'post-header',
                'multiple_widgets' => 1,
                'cache_lifetime' => $cacheLifetime,
                'cache_tags' => '\app\widgets\navigation\models\Navigation',
            ],
            [ // 4
                'name' => Yii::t('app', 'Before content'),
                'key' => 'before-content',
                'multiple_widgets' => 1,
            ],
            [ // 5
                'name' => Yii::t('app', 'Left sidebar'),
                'key' => 'left-sidebar',
                'multiple_widgets' => 1,
                'is_cacheable' => 0,
            ],
            [ // 6
                'name' => Yii::t('app', 'Before inner-content'),
                'key' => 'before-inner-content',
                'multiple_widgets' => 1,
                'is_cacheable' => 0,
            ],
            [ // 7
                'name' => Yii::t('app', 'After inner-content'),
                'key' => 'after-inner-content',
                'multiple_widgets' => 1,
                'is_cacheable' => 0,
            ],
            [ // 8
                'name' => Yii::t('app', 'Right sidebar'),
                'key' => 'right-sidebar',
                'multiple_widgets' => 1,
                'is_cacheable' => 0,
            ],
            [ // 9
                'name' => Yii::t('app', 'Pre-footer'),
                'key' => 'pre-footer',
                'multiple_widgets' => 1,
                'cache_lifetime' => $cacheLifetime,
            ],
            [ // 10
                'name' => Yii::t('app', 'Footer'),
                'key' => 'footer',
                'multiple_widgets' => 1,
                'cache_lifetime' => $cacheLifetime,
            ],
            [ // 11
                'name' => Yii::t('app', 'Post-footer'),
                'key' => 'post-footer',
                'multiple_widgets' => 1,
                'cache_lifetime' => $cacheLifetime,
            ],
        ];

        $this->bulkInsert('{{%theme_parts}}', $baseParts);

        $baseWidgets = [
            [
                // 1
                'name' => Yii::t('app', '1-row header with logo, nav and popup cart'),
                'widget' => 'app\extensions\DefaultTheme\widgets\OneRowHeaderWithCart\Widget',
                'cache_lifetime' => $cacheLifetime,
                'cache_vary_by_session' => 1,
                'configuration_json' => '{}',
                'configuration_model' => 'app\extensions\DefaultTheme\widgets\OneRowHeaderWithCart\ConfigurationModel',
                'configuration_view' => '@app/extensions/DefaultTheme/widgets/OneRowHeaderWithCart/views/_config.php',
            ],
            [
                // 2
                'name' => Yii::t('app', '1-row header with logo, nav'),
                'widget' => 'app\extensions\DefaultTheme\widgets\OneRowHeader\Widget',
                'cache_lifetime' => $cacheLifetime,
                'configuration_json' => '{}',
                'configuration_model' => 'app\extensions\DefaultTheme\widgets\OneRowHeader\ConfigurationModel',
                'configuration_view' => '@app/extensions/DefaultTheme/widgets/OneRowHeader/views/_config.php',
            ],
            [
                // 3
                'name' => Yii::t('app', 'Slider'),
                'widget' => 'app\extensions\DefaultTheme\widgets\Slider\Widget',
                'cache_lifetime' => $cacheLifetime,
                'configuration_json' => '{}',
                'configuration_model' => 'app\extensions\DefaultTheme\widgets\Slider\ConfigurationModel',
                'configuration_view' => '@app/extensions/DefaultTheme/widgets/Slider/views/_config.php',
            ],

        ];
        $this->bulkInsert('{{%theme_widgets}}', $baseWidgets);

        $applying = [
            [
                'widget_id' => 1,
                'part_id' => 2,
            ],
            [
                'widget_id' => 2,
                'part_id' => 2,
            ],
            [
                'widget_id' => 3,
                'part_id' => 4,
            ],
            [
                'widget_id' => 3,
                'part_id' => 6,
            ],
        ];
        $this->bulkInsert('{{%theme_widget_applying}}', $applying);

        $variations = [
            [ // 1
                'name' => Yii::t('app', 'Main page'),
                'by_url' => '/',
                'exclusive' => 1,
            ],
            [ // 2
                'name' => Yii::t('app', 'All pages'),
                'by_url' => '*',
            ],
            [ // 3
                'name' => Yii::t('app', 'Non main page'),
                'by_url' => '/*',
            ],
            [ // 4
                'name' => Yii::t('app', 'Product listing'),
                'by_route' => 'shop/product/list',
            ],
            [ // 5
                'name' => Yii::t('app', 'Product page(show)'),
                'by_route' => 'shop/product/show',
            ],
            [ // 6
                'name' => Yii::t('app', 'Content page listing'),
                'by_route' => 'page/page/list',
            ],
            [ // 7
                'name' => Yii::t('app', 'Content page(show)'),
                'by_route' => 'page/page/show',
            ],
        ];
        $this->bulkInsert('{{%theme_variation}}', $variations);

        $activeWidgets = [
            [
                'widget_id' => 1,
                'part_id' => 2,
                'variation_id' => 2,
            ],
            [
                'widget_id' => 1,
                'part_id' => 2,
                'variation_id' => 1,
            ],
            [
                'widget_id' => 3,
                'part_id' => 4,
                'variation_id' => 1,
            ],
            [
                'widget_id' => 3,
                'part_id' => 6,
                'variation_id' => 3,
            ],
        ];
        $this->bulkInsert('{{%theme_active_widgets}}', $activeWidgets);
    }

    private function bulkInsert($table, $data)
    {
        foreach ($data as $row) {
            $this->insert($table, $row);
        }
    }

    public function down()
    {
        $this->delete('{{%configurable}}', ['module' => 'DefaultTheme']);
        echo "\n\nWARNING! Please remove configurables from default theme by hand!\n\n";

        $this->dropTable('{{%theme_parts}}');
        $this->dropTable('{{%theme_widgets}}');
        $this->dropTable('{{%theme_variation}}');
        $this->dropTable('{{%theme_widget_applying}}');
        $this->dropTable('{{%theme_active_widgets}}');
    }
    

}
