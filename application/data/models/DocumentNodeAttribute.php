<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%onec_id}}".
 *
 * @property integer $id
 * @property string $onec
 * @property integer $inner_id
 * @property string $entity_id
 */
class DocumentNodeAttribute extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%document_node_attributes}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
        	[['ownerNode'], 'integer'],
            [['key'], 'string', 'max' => 100],
        	[['value'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'ownerNode' => Yii::t('app', 'Owner Node'),
        	'key' => Yii::t('app', 'Key'),
        	'value' => Yii::t('app', 'Value')
        ];
    }

}
