<?php

use yii\db\Migration;

class m150831_132548_LastViewedProducts_widget extends Migration
{

    public function up()
    {
        mb_internal_encoding("UTF-8");
        $this->insert('{{%theme_widgets}}', [
            'name' => Yii::t('app', 'Last Viewed Products'),
            'widget' => 'app\extensions\DefaultTheme\widgets\LastViewedProducts\Widget',
            'configuration_model' => 'app\extensions\DefaultTheme\widgets\LastViewedProducts\ConfigurationModel',
            'configuration_view' => '@app/extensions/DefaultTheme/widgets/LastViewedProducts/views/_config.php',
            'configuration_json' => '{}',
            'is_cacheable' => 0,
            'cache_tags' => '',
        ]);
        $widgetId = $this->db->lastInsertID;
        $this->insert('{{%theme_widget_applying}}', [
            'widget_id' => $widgetId,
            'part_id' => 7, //after-inner-content
        ]);
        $this->insert('{{%theme_widget_applying}}', [
            'widget_id' => $widgetId,
            'part_id' => 9, //pre-footer
        ]);
    }

    public function down()
    {
        echo "m150831_132548_LastViewedProducts_widget cannot be reverted.\n";

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
