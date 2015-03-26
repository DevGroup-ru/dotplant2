<?php

use yii\db\Schema;
use yii\db\Migration;

class m150320_080911_eav_property_group_id_add extends Migration
{

    public function up()
    {
        $objects = \app\models\Object::find()->asArray()->all();
        foreach ($objects as $object) {
            if ($object['name'] == 'Submission'){
                continue;
            } else if ($object['name'] == 'Form') {
                $submissionObject = \app\models\Object::findOne(['name' => 'Submission']);
                $this->addColumn($object['eav_table_name'], 'property_group_id', 'INT UNSIGNED NOT NULL AFTER `object_model_id`');
                $this->addColumn($submissionObject->eav_table_name, 'property_group_id', 'INT UNSIGNED NOT NULL AFTER `object_model_id`');
                $groups = \app\models\PropertyGroup::find()
                    ->where(['object_id' => $object['id']])
                    ->asArray()
                    ->all();
                foreach ($groups as $group) {
                    $forms = \app\models\ObjectPropertyGroup::find()
                        ->select('object_model_id')
                        ->where(['property_group_id' => $group['id']])
                        ->asArray()
                        ->all();
                    $formIDs = [];
                    foreach ($forms as $formID) {
                        if (!in_array($formID['object_model_id'], $formIDs)) {
                            $formIDs[] = $formID['object_model_id'];
                        }
                    }
                    $submissionIDs = \app\models\Submission::find()
                        ->select('id')
                        ->where(['form_id' => $formIDs])
                        ->asArray()
                        ->all();
                    $subIDs = [];
                    foreach ($submissionIDs as $submission) {
                        $subIDs[] = $submission['id'];
                    }
                    $properties = \app\models\Property::find()
                        ->select(['id', 'key'])
                        ->where(['property_group_id' => $group['id'], 'is_eav' => 1])
                        ->asArray()
                        ->all();
                    foreach ($properties as $property) {
                        $this->update($submissionObject->eav_table_name, ['property_group_id' => $group['id']], ['key' => $property['key']]);
                    }

                }
            } else {
                $this->addColumn($object['eav_table_name'], 'property_group_id', 'INT UNSIGNED NOT NULL AFTER `object_model_id`');
                $groups = \app\models\PropertyGroup::find()
                    ->where(['object_id' => $object['id']])
                    ->asArray()
                    ->all();
                foreach ($groups as $group) {
                    $properties = \app\models\Property::find()
                        ->select(['id', 'key'])
                        ->where(['property_group_id' => $group['id'], 'is_eav' => 1])
                        ->asArray()
                        ->all();
                    foreach ($properties as $property) {
                        $this->update($object['eav_table_name'], ['property_group_id' => $group['id']], ['key' => $property['key']]);
                    }
                }
            }
        }
    }

    public function down()
    {
        $objects = \app\models\Object::find()->asArray()->all();
        foreach ($objects as $object) {
            $this->dropColumn($object['eav_table_name'], 'property_group_id');
        }
    }

}
