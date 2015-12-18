<?php

namespace app\modules\shop\models;

use app\traits\SortModels;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "{{%addon_bindings}}".
 *
 * @property integer $id
 * @property integer $addon_id
 * @property integer $appliance_object_id
 * @property integer $object_model_id
 * @property integer $sort_order
 */
class AddonBindings extends \yii\db\ActiveRecord
{
    use SortModels;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%addon_bindings}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['addon_id', 'appliance_object_id', 'object_model_id'], 'required'],
            [['addon_id', 'appliance_object_id', 'object_model_id'], 'integer'],
            [['sort_order'], 'number'],
            [['appliance_object_id', 'object_model_id'], 'unique', 'targetAttribute' => ['appliance_object_id', 'object_model_id'], 'message' => 'The combination of Appliance Object ID and Object Model ID has already been taken.', 'on'=>'insert'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'addon_id' => Yii::t('app', 'Addon ID'),
            'appliance_object_id' => Yii::t('app', 'Appliance Object ID'),
            'object_model_id' => Yii::t('app', 'Object Model ID'),
            'sort_order' => Yii::t('app', 'Sort Order'),
        ];
    }
    /**
     * Search tasks
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /** @var $query \yii\db\ActiveQuery */
        $query = self::find();

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]
        );
        if (!($this->load($params))) {
            return $dataProvider;
        }
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['addon_id' => $this->addon_id]);
        return $dataProvider;
    }

    public function getAddon()
    {
        return $this->hasOne(Addon::className(), ['id'=>'addon_id']);
    }
}
