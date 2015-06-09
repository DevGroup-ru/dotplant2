<?php

use yii\db\Schema;
use yii\db\Migration;

class m150531_134103_theme_part_improvements extends Migration
{
    public function up()
    {
        $this->addColumn('{{%theme_active_widgets}}', 'configuration_json', Schema::TYPE_TEXT);
        $this->insert(
            '{{%theme_widgets}}',
            [
                'name' => Yii::t('app', 'Categories list'),
                'widget' => 'app\extensions\DefaultTheme\widgets\CategoriesList\Widget',
                'configuration_model' => 'app\extensions\DefaultTheme\widgets\CategoriesList\ConfigurationModel',
                'configuration_view' => '@app/extensions/DefaultTheme/widgets/CategoriesList/views/_config.php',
                'configuration_json' => '{}',
                'is_cacheable' => 0,
                'cache_tags' => \app\modules\shop\models\Category::className(),
            ]
        );
        $categoriesListWidgetId = $this->db->lastInsertID;

        $this->insert(
            '{{%theme_widgets}}',
            [
                'name' => Yii::t('app', 'Filter widget'),
                'widget' => 'app\extensions\DefaultTheme\widgets\FilterSets\Widget',
                'configuration_model' => 'app\extensions\DefaultTheme\widgets\FilterSets\ConfigurationModel',
                'configuration_view' => '@app/extensions/DefaultTheme/widgets/FilterSets/views/_config.php',
                'configuration_json' => '{}',
                'is_cacheable' => 0,
                'cache_tags' => '',
            ]
        );
        $filterSetsWidget = $this->db->lastInsertID;

        $this->insert(
            '{{%theme_widgets}}',
            [
                'name' => Yii::t('app', 'Pages list'),
                'widget' => 'app\extensions\DefaultTheme\widgets\PagesList\Widget',
                'configuration_model' => 'app\extensions\DefaultTheme\widgets\PagesList\ConfigurationModel',
                'configuration_view' => '@app/extensions/DefaultTheme/widgets/PagesList/views/_config.php',
                'configuration_json' => '{}',
                'is_cacheable' => 1,
                'cache_lifetime' => 86400,
                'cache_tags' => \app\modules\page\models\Page::className(),
            ]
        );
        $pagesList = $this->db->lastInsertID;

        $this->insert(
            '{{%theme_widgets}}',
            [
                'name' => Yii::t('app', 'Content block'),
                'widget' => 'app\extensions\DefaultTheme\widgets\ContentBlock\Widget',
                'configuration_model' => 'app\extensions\DefaultTheme\widgets\ContentBlock\ConfigurationModel',
                'configuration_view' => '@app/extensions/DefaultTheme/widgets/ContentBlock/views/_config.php',
                'configuration_json' => '{}',
                'is_cacheable' => 1,
                'cache_lifetime' => 86400,
                'cache_tags' => \app\modules\page\models\Page::className(),
            ]
        );
        $contentBlock = $this->db->lastInsertID;

        $allBlocks = [
            $categoriesListWidgetId,
            $filterSetsWidget,
            $pagesList,
            $contentBlock
        ];
        foreach ($allBlocks as $widget_id) {
            // left sidebar
            $this->insert(
                '{{%theme_widget_applying}}',
                [
                    'widget_id' => $widget_id,
                    'part_id' => 5,
                ]
            );
            // right sidebar
            $this->insert(
                '{{%theme_widget_applying}}',
                [
                    'widget_id' => $widget_id,
                    'part_id' => 8,
                ]
            );
        }
    }

    public function down()
    {
        $this->dropColumn('{{%theme_active_widgets}}', 'configuration_json');
        $this->delete('{{%theme_widgets}}', [
            'widget' => 'app\extensions\DefaultTheme\widgets\CategoriesList\Widget',
        ]);
        $this->delete('{{%theme_widgets}}', [
            'widget' => 'app\extensions\DefaultTheme\widgets\FilterSets\Widget',
        ]);
        $this->delete('{{%theme_widgets}}', [
            'widget' => 'app\extensions\DefaultTheme\widgets\PagesList\Widget',
        ]);
        $this->delete('{{%theme_widgets}}', [
            'widget' => 'app\extensions\DefaultTheme\widgets\ContentBlock\Widget',
        ]);
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
