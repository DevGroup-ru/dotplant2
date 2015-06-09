<?php

namespace app\extensions\DefaultTheme\models;

use app\traits\IdentityMap;
use Yii;
use \devgroup\TagDependencyHelper\ActiveRecordHelper;

/**
 * This is the model class for table "{{%theme_widget_applying}}".
 *
 * @property integer $id
 * @property integer $widget_id
 * @property integer $part_id
 * @part ThemeParts $part
 */
class ThemeWidgetApplying extends \yii\db\ActiveRecord
{
    use IdentityMap;
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => ActiveRecordHelper::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%theme_widget_applying}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['widget_id', 'part_id'], 'integer'],
            [['widget_id', 'part_id'], 'unique', 'targetAttribute' => ['widget_id', 'part_id'], 'message' => 'The combination of Widget ID and Part ID has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'widget_id' => Yii::t('app', 'Widget ID'),
            'part_id' => Yii::t('app', 'Part ID'),
        ];
    }

    /**
     * Relation to ThemeParts that we can apply to
     * @return \yii\db\ActiveQuery
     */
    public function getPart()
    {
        return $this->hasOne(ThemeParts::className(), ['id' => 'part_id']);
    }
}
