<?php

namespace app\modules\seo\models;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "link_anchor_binding".
 *
 * @property integer $id
 * @property integer $link_anchor_id
 * @property string $view_file
 * @property string $params_hash
 * @property string $model_name
 * @property integer $model_id
 */
class LinkAnchorBinding extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%link_anchor_binding}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['link_anchor_id', 'view_file', 'model_name', 'model_id'], 'required'],
            [['link_anchor_id'], 'integer'],
            [['params_hash'], 'string'],
            [['view_file', 'model_name', 'model_id'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'link_anchor_id' => 'Link anchor ID',
            'view_file' => 'View File',
            'params_hash' => 'Route Params',
            'model_name' => 'Model Name',
            'model_id' => 'Model ID',
        ];
    }

    public function getAnchor()
    {
        return $this->hasOne(LinkAnchor::className(), ['id' => 'link_anchor_id']);
    }

    public function getIndex()
    {
        return $this->hasOne(ModelAnchorIndex::className(), ['model_name' => 'model_name', 'model_id' => 'model_id']);
    }
}
