<?php

namespace app\models;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "prefiltered_pages".
 *
 * @property integer $id
 * @property string $slug
 * @property integer $active
 * @property string $params
 * @property string $title
 * @property string $announce
 * @property string $content
 * @property string $h1
 * @property string $meta_description
 * @property string $breadcrumbs_label
 * @property integer $view_id
 */
class PrefilteredPages extends \yii\db\ActiveRecord
{


    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [

            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
            ],

        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%prefiltered_pages}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['active', 'view_id', 'last_category_id'], 'integer'],
            [['params', 'announce', 'content'], 'string'],
            [['slug', 'title', 'h1', 'meta_description', 'breadcrumbs_label'], 'string', 'max' => 255],
            [['active'], 'default', 'value'=>1],
            [['last_category_id'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'slug' => Yii::t('app', 'Slug'),
            'active' => Yii::t('app', 'Active'),
            'params' => Yii::t('app', 'Params'),
            'title' => Yii::t('app', 'Title'),
            'announce' => Yii::t('app', 'Announce'),
            'content' => Yii::t('app', 'Content'),
            'h1' => Yii::t('app', 'H1'),
            'meta_description' => Yii::t('app', 'Meta Description'),
            'breadcrumbs_label' => Yii::t('app', 'Breadcrumbs Label'),
            'view_id' => Yii::t('app', 'View'),
            'last_category_id' => Yii::t('app', 'Category'),
        ];
    }

    /**
     * Search tasks
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /* @var $query \yii\db\ActiveQuery */
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
        $query->andFilterWhere(['like', 'title', $this->title]);
        $query->andFilterWhere(['like', 'slug', $this->slug]);
        $query->andFilterWhere(['like', 'h1', $this->h1]);
        $query->andFilterWhere(['like', 'meta_description', $this->meta_description]);
        $query->andFilterWhere(['like', 'slug', $this->slug]);

        return $dataProvider;
    }

    /**
     * Returns active prefiltered page as array for specified URL (exact match).
     * Used by ObjectRule
     * @param $url
     * @return null|array
     */
    public static function getActiveByUrl($url)
    {
        $cacheKey = "12PrefilteredPage:$url";

        $model = Yii::$app->cache->get($cacheKey);
        if ($model === false) {
            $model = static::find()
                ->where([
                    'slug' => $url,
                    'active' => 1
                ])
                ->asArray()
                ->one();
            $dependency = new TagDependency(
                [
                    'tags'=>ActiveRecordHelper::getCommonTag(static::className())
                ]
            );
            if ($model !== null) {
                $dependency = new TagDependency(
                    [
                        'tags'=>ActiveRecordHelper::getObjectTag(static::className(), $model['id'])
                    ]
                );
            }
            Yii::$app->cache->set(
                $cacheKey,
                $model,
                86400,
                $dependency
            );

        }
        return $model;
    }
}
