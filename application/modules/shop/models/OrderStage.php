<?php

namespace app\modules\shop\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "{{%order_stage}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $name_frontend
 * @property string $name_short
 * @property integer $is_initial
 * @property integer $is_buyer_stage
 * @property integer $become_non_temporary
 * @property integer $is_in_cart
 * @property integer $immutable_by_user
 * @property integer $immutable_by_manager
 * @property integer $immutable_by_assigned
 * @property string $reach_goal_ym
 * @property string $reach_goal_ga
 * @property string $event_name
 * @property string $view
 * Relations:
 * @property OrderStageLeaf[] $nextLeafs
 * @property OrderStageLeaf[] $prevLeafs
 */
class OrderStage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_stage}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['is_initial', 'is_buyer_stage', 'become_non_temporary', 'is_in_cart', 'immutable_by_user', 'immutable_by_manager', 'immutable_by_assigned'], 'integer'],
            [['name', 'name_frontend', 'name_short', 'reach_goal_ym', 'reach_goal_ga', 'event_name', 'view'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'name_frontend' => Yii::t('app', 'Name Frontend'),
            'name_short' => Yii::t('app', 'Name Short'),
            'is_initial' => Yii::t('app', 'Is Initial'),
            'is_buyer_stage' => Yii::t('app', 'Is Buyer Stage'),
            'become_non_temporary' => Yii::t('app', 'Become Non Temporary'),
            'is_in_cart' => Yii::t('app', 'Is In Cart'),
            'immutable_by_user' => Yii::t('app', 'Immutable By User'),
            'immutable_by_manager' => Yii::t('app', 'Immutable By Manager'),
            'immutable_by_assigned' => Yii::t('app', 'Immutable By Assigned'),
            'reach_goal_ym' => Yii::t('app', 'Reach Goal Ym'),
            'reach_goal_ga' => Yii::t('app', 'Reach Goal Ga'),
            'event_name' => Yii::t('app', 'Event Name'),
            'view' => Yii::t('app', 'View'),
        ];
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = static::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 25,
            ],
        ]);

        if ($this->load($params)) {
            $query->andFilterWhere([
                'name' => $this->name,
                'name_frontend' => $this->name_frontend,
                'name_short' => $this->name_short,
                'event_name' => $this->event_name,
            ]);
        }

        return $dataProvider;
    }

    /**
     * Check if current stage is initial
     * @return bool
     */
    public function isInitial()
    {
        return $this->is_initial === 1 ? true : false;
    }

    /**
     * @return OrderStage|null
     */
    public static function getInitialStage()
    {
        return static::findOne(['is_initial' => 1]);
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        $result = parent::beforeSave($insert);
        if ($this->validate() && 1 === intval($this->is_initial)) {
            static::updateAll(['is_initial' => 0], ['is_initial' => 1]);
        }
        return $result;
    }

    /**
     * @return OrderStageLeaf[]
     */
    public function getNextLeafs()
    {
        return $this->hasMany(OrderStageLeaf::className(), ['stage_from_id' => 'id'])
            ->addOrderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }

    /**
     * @return OrderStageLeaf[]
     */
    public function getPrevLeafs()
    {
        return $this->hasMany(OrderStageLeaf::className(), ['stage_to_id' => 'id'])
            ->addOrderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]);
    }
}
?>