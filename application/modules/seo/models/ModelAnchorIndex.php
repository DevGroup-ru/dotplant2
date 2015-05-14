<?php

namespace app\modules\seo\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "model_anchor_index".
 *
 * @property string $model_name
 * @property integer $model_id
 * @property string $next_index
 */
class ModelAnchorIndex extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%model_anchor_index}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['model_name', 'model_id', 'next_index'], 'required'],
            [['next_index'], 'integer'],
            [['model_name', 'model_id'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'model_name' => 'Model Name',
            'model_id' => 'Model ID',
            'next_index' => 'Next Index',
        ];
    }
}
