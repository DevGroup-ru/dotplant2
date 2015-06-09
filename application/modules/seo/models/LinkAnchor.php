<?php

namespace app\modules\seo\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "link_anchor".
 *
 * @property integer $id
 * @property string $modelName
 * @property integer $modelId
 * @property string $anchor
 * @property integer $sort_order
 */
class LinkAnchor extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%link_anchor}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['model_name'], 'required'],
            [['sort_order'], 'integer'],
            [['model_id', 'anchor'], 'string', 'max' => 255],
            [['model_name'], 'string', 'max' => 60]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'model_name' => 'Model Name',
            'model_id' => 'Model ID',
            'anchor' => 'Anchor',
            'sort_order' => 'Sort Order',
        ];
    }
}
