<?php

use yii\db\Schema;
use yii\db\Migration;

class m150615_105848_navigation_widget extends Migration
{
    public function up()
    {
        mb_internal_encoding("UTF-8");
        $this->insert('{{%theme_widgets}}', [
            'name' => Yii::t('app', 'Navigation'),
            'widget' => 'app\extensions\DefaultTheme\widgets\Navigation\Widget',
            'configuration_model' => 'app\extensions\DefaultTheme\widgets\Navigation\ConfigurationModel',
            'configuration_view' => '@app/extensions/DefaultTheme/widgets/Navigation/views/_config.php',
            'configuration_json' => '{}',
            'is_cacheable' => 1,
            'cache_tags' => '',
        ]);
        $widgetId = $this->db->lastInsertID;
        $this->insert('{{%theme_widget_applying}}', [
            'widget_id' => $widgetId,
            'part_id' => 5, //left-sidebar
        ]);
        $this->insert('{{%theme_widget_applying}}', [
            'widget_id' => $widgetId,
            'part_id' => 8, //right-sidebar
        ]);
    }

    public function down()
    {
        echo "m150615_105848_navigation_widget cannot be reverted.\n";

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
