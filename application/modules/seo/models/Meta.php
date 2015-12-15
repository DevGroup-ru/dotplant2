<?php

namespace app\modules\seo\models;

use app\backend\BackendModule;
use app\backend\components\BackendController;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "seo_meta".
 *
 * @property string $key
 * @property string $name
 * @property integer $content
 */
class Meta extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%seo_meta}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['key', 'name', 'content'], 'required'],
            [['key'], 'unique'],
            [['key', 'name', 'content'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'key' => \Yii::t('app', 'Key'),
            'name' => \Yii::t('app', 'Name'),
            'content' => \Yii::t('app', 'Content'),
        ];
    }

    /**
     * Search meta tags
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $this->load($params);
        $query = self::find();
        foreach ($this->attributes as $name => $value) {
            if (!empty($value)) {
                $query->andWhere("`$name` LIKE :$name", [":$name" => "%$value%"]);
            }
        }
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]
        );
        return $dataProvider;
    }

    public static function registrationMeta()
    {

        if (
            \Yii::$app->request->isAjax === false &&
            \Yii::$app->controller->module instanceof BackendModule === false &&
            \Yii::$app->controller instanceof BackendController === false
        ) {
           $cacheName = \Yii::$app->getModule('seo')->cacheConfig['metaCache']['name'];
           $cacheExpire = \Yii::$app->getModule('seo')->cacheConfig['metaCache']['expire'];

           if (\Yii::$app->getCache()->get($cacheName)) {
               $metas = \Yii::$app->getCache()->get($cacheName);
           } else {
               $metas = Meta::find()->all();
               \Yii::$app->getCache()->set($cacheName, $metas, $cacheExpire);
           }
           foreach ($metas as $meta) {
               \Yii::$app->controller->getView()->registerMetaTag(
                   [
                       'name' => $meta->name,
                       'content' => $meta->content,
                   ],
                   $meta->key
               );
           }
       }


    }
}
