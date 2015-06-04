<?php

namespace app\modules\seo\models;

use app\models\Object;
use Yii;

/**
 * This is the model class for table "{{%open_graph_object}}".
 *
 * @property integer $id
 * @property integer $object_id
 * @property integer $active
 * @property string $relation_data
 * @property Object $object
 */
class OpenGraphObject extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%open_graph_object}}';
    }


    public function getObject()
    {
        return $this->hasOne(Object::className(), ['id'=>'object_id']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_id', 'relation_data'], 'required'],
            [['object_id', 'active'], 'integer'],
            [['relation_data'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'object_id' => Yii::t('app', 'Object ID'),
            'active' => Yii::t('app', 'Active'),
            'relation_data' => Yii::t('app', 'Relation Data'),
        ];
    }
}
