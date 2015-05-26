<?php

namespace app\modules\shop\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "category_group_route_templates".
 *
 * @property integer $id
 * @property integer $category_group_id
 * @property integer $route_id
 * @property string $template_json
 */
class CategoryGroupRouteTemplates extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%category_group_route_templates}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_group_id', 'route_id', 'template_json'], 'required'],
            [['category_group_id', 'route_id'], 'integer'],
            [['template_json'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'category_group_id' => Yii::t('app', 'Category Group ID'),
            'route_id' => Yii::t('app', 'Route ID'),
            'template_json' => Yii::t('app', 'Template Json'),
        ];
    }
}
