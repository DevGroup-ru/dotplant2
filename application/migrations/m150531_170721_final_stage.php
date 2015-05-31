<?php

use yii\db\Schema;
use yii\db\Migration;

class m150531_170721_final_stage extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $tblStage = \app\modules\shop\models\OrderStage::tableName();
        $tblStageLeaf = \app\modules\shop\models\OrderStageLeaf::tableName();
        $tblEvents = \app\modules\core\models\Events::tableName();
        $tblEventHandlers = \app\modules\core\models\EventHandlers::tableName();

        $this->insert($tblEvents, [
            'owner_class_name' => 'app\modules\shop\ShopModule',
            'event_name' => 'order_stage_final',
            'event_class_name' => 'app\modules\shop\events\StageFinal',
            'selector_prefix' => '',
            'event_description' => '',
            'documentation_link' => '',
        ]);
        $eventId = $this->db->lastInsertID;
        $this->insert($tblEventHandlers, [
            'event_id' => $eventId,
            'sort_order' => 0,
            'handler_class_name' => 'app\modules\shop\helpers\BaseOrderStageHandlers',
            'handler_function_name' => 'handleStageFinal',
            'is_active' => 1,
            'non_deletable' => 1,
            'triggering_type' => 'application_trigger',
        ]);

        $this->insert($tblEvents, [
            'owner_class_name' => 'app\modules\shop\ShopModule',
            'event_name' => 'order_stage_leaf_final',
            'event_class_name' => 'app\modules\shop\events\StageLeafFinal',
            'selector_prefix' => '',
            'event_description' => '',
            'documentation_link' => '',
        ]);
        $eventId = $this->db->lastInsertID;
        $this->insert($tblEventHandlers, [
            'event_id' => $eventId,
            'sort_order' => 0,
            'handler_class_name' => 'app\modules\shop\helpers\BaseOrderStageHandlers',
            'handler_function_name' => 'handleFinal',
            'is_active' => 1,
            'non_deletable' => 1,
            'triggering_type' => 'application_trigger',
        ]);

        $this->insert($tblStage, [
            'name' => 'final',
            'name_frontend' => Yii::t('app', 'Order complete'),
            'name_short' => 'final',
            'is_initial' => 0,
            'is_buyer_stage' => 0,
            'become_non_temporary' => 0,
            'is_in_cart' => 0,
            'immutable_by_user' => 1,
            'immutable_by_manager' => 1,
            'immutable_by_assigned' => 1,
            'reach_goal_ym' => '',
            'reach_goal_ga' => '',
            'event_name' => 'order_stage_final',
            'view' => '',
        ]);
        $stage = $this->db->lastInsertID;

        $lastStage = \app\modules\shop\models\OrderStage::findOne(['name' => 'payment pay']);
        $lastStage = null === $lastStage ? 0 : $lastStage->id;

        $this->insert($tblStageLeaf, [
            'stage_from_id' => $stage,
            'stage_to_id' => $lastStage,
            'sort_order' => 0,
            'button_label' => Yii::t('app', 'Order complete'),
            'button_css_class' => 'btn btn-primary',
            'notify_manager' => 0,
            'notify_new_assigned_user' => 0,
            'role_assignment_policy' => 'random',
            'event_name' => 'order_stage_leaf_final',
        ]);
    }

    public function down()
    {
        echo "m150531_170721_final_stage cannot be reverted.\n";

        return false;
    }
}
?>