<?php

namespace app\modules\core\models;

use app;
use Yii;
use \devgroup\TagDependencyHelper\ActiveRecordHelper;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%wysiwyg}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $class_name
 * @property string $params
 * @property string $configuration_model
 */
class Wysiwyg extends \yii\db\ActiveRecord
{
    use \app\traits\FindById;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wysiwyg}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'class_name'], 'required'],
            [['params'], 'string'],
            [['name', 'class_name', 'configuration_model'], 'string', 'max' => 255],
            [
                ['class_name', 'configuration_model'],
                app\validators\ClassnameValidator::className(),
            ]
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
            'class_name' => Yii::t('app', 'Class Name'),
            'params' => Yii::t('app', 'Params'),
            'configuration_model' => Yii::t('app', 'Configuration Model'),
        ];
    }

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
     * Search slides
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /* @var $query \yii\db\ActiveQuery */
        $query = static::find();
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageSize' => 100,
                ],
            ]
        );
        if (!($this->load($params))) {
            return $dataProvider;
        }
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'name', $this->name]);
        $query->andFilterWhere(['like', 'class_name', $this->class_name]);
        return $dataProvider;
    }

    /**
     * Returns class name and params of widget for specified wysiwyg record id
     * @param integer $id ID of Wysiwyg record
     * @return array of 'class_name' and 'params'
     */
    public static function getClassNameAndParamsById($id)
    {
        $cacheKey = 'WysiwygClassName:' . $id;
        $data = Yii::$app->cache->get($cacheKey);
        if (is_array($data) === false) {

            $data = self::getDb()
                ->createCommand('select class_name, params from {{%wysiwyg}} where id = :id', [':id' => $id])
                ->queryOne();
            $data['params'] = empty($data['params']) ? [] : Json::decode($data['params']);

            Yii::$app->cache->set(
                $cacheKey,
                $data,
                86400,
                new TagDependency([
                    'tags' => [
                        ActiveRecordHelper::getObjectTag(self::className(), $id),
                    ]
                ])
            );
        }

        return $data;
    }

    public static function itemsForSelect()
    {
        $cacheKey = 'AllWysiwygForSelect';
        $items = Yii::$app->cache->get($cacheKey);
        if (is_array($items) === false) {
            $items = ArrayHelper::map(
                self::find()->select(['id', 'name'])->asArray()->all(),
                'id',
                'name'
            );
            Yii::$app->cache->set(
                $cacheKey,
                $items,
                86400,
                new TagDependency([
                    'tags' => [
                        ActiveRecordHelper::getCommonTag(self::className())
                    ]
                ])
            );
        }
        return $items;
    }
}
