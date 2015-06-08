<?php

use yii\db\Schema;
use yii\db\Migration;

class m150608_070448_new_theme_widgets extends Migration
{
    public function up()
    {
        mb_internal_encoding("UTF-8");
        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB'
            : null;

        $this->insert('{{%theme_widgets}}', [
            'name' => Yii::t('app', 'Footer'),
            'widget' => 'app\extensions\DefaultTheme\widgets\Footer\Widget',
            'configuration_model' => 'app\extensions\DefaultTheme\widgets\Footer\ConfigurationModel',
            'configuration_view' => '@app/extensions/DefaultTheme/widgets/Footer/views/_config.php',
            'configuration_json' => '{}',
            'is_cacheable' => 1,
            'cache_tags' => '',
        ]);

        $widgetId = $this->db->lastInsertID;
        $this->insert('{{%theme_widget_applying}}', [
            'widget_id' => $widgetId,
            'part_id' => 9, //pre-footer
        ]);
        $this->insert('{{%theme_widget_applying}}', [
            'widget_id' => $widgetId,
            'part_id' => 10, //footer
        ]);
        $this->insert('{{%theme_widget_applying}}', [
            'widget_id' => $widgetId,
            'part_id' => 11, //post-footer
        ]);

        // add footer to main page and non-main pages in footer part
        $this->insert('{{%theme_active_widgets}}', [
            'widget_id' => $widgetId,
            'part_id' => 10,
            'variation_id' => 1,
        ]);

        $this->insert('{{%theme_active_widgets}}', [
            'widget_id' => $widgetId,
            'part_id' => 10,
            'variation_id' => 2,
        ]);

        // categories list
        $this->insert('{{%theme_active_widgets}}', [
            'widget_id' => 4,
            'part_id' => 5,
            'variation_id' => 2,
        ]);
    }

    public function down()
    {
        echo "m150608_070448_new_theme_widgets cannot be reverted.\n";

        return false;
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
