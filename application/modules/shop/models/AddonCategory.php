<?php

namespace app\modules\shop\models;


use app\traits\FindById;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "{{%addon_category}}".
 *
 * @property integer $id
 * @property string $name
 */
class AddonCategory extends \yii\db\ActiveRecord
{
    use FindById;

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
        return '{{%addon_category}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string'],
            [['sort_order'], 'integer'],
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
            'sort_order' => Yii::t('app', 'Sort order'),
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
        $query->andFilterWhere(['like', 'name', $this->name]);
        return $dataProvider;
    }

    public static function availableAddons($id)
    {
        $cacheKey = 'Addons4'.$id;
        $addons = Yii::$app->cache->get($cacheKey);
        if ($addons === false) {
            $addons = Addon::findAll(['addon_category_id'=>$id]);
            Yii::$app->cache->set(
                $cacheKey,
                $addons,
                86400,
                new TagDependency([
                    'tags' => [
                        ActiveRecordHelper::getCommonTag(Addon::className()),
                        ActiveRecordHelper::getCommonTag(AddonCategory::className()),
                    ]
                ])
            );
        }
        return $addons;
    }
}
