<?php

namespace app\modules\seo\models;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use yii\base\Event;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "seo_counter".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property string $code
 */
class Counter extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%seo_counter}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description', 'code'], 'string'],
            [['code'], 'required'],
            [['name'], 'string', 'max' => 255]
        ];
    }

    public function behaviors()
    {
        return [
            ActiveRecordHelper::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => \Yii::t('app', 'ID'),
            'name' => \Yii::t('app', 'Name'),
            'description' => \Yii::t('app', 'Description'),
            'code' => \Yii::t('app', 'Code'),
        ];
    }

    public static function renderCounters(Event $event)
    {
        if (isset($event->sender->context->module)
            && in_array($event->sender->context->module->id.'/'.$event->sender->context->id, $event->data)) {
            $counter_str = '';
            /* @var $counters Counter[] */
            if (false === $counters = \Yii::$app->getCache()->get(\Yii::$app->getModule('seo')->cacheConfig['counterCache']['name'])) {
                $counters = self::find()->all();
                \Yii::$app->getCache()->set(
                    \Yii::$app->getModule('seo')->cacheConfig['counterCache']['name'],
                    $counters,
                    \Yii::$app->getModule('seo')->cacheConfig['counterCache']['expire'],
                    new TagDependency(
                        [
                            'tags' => [
                                ActiveRecordHelper::getCommonTag(self::className()),
                            ],
                        ]
                    )
                );
            }
            foreach ($counters as $counter) {
                $counter_str .= "\n<!-- $counter->name counter -->\n";
                $counter_str .= $counter->code;
                $counter_str .= "\n<!-- /$counter->name counter -->\n";
            }
            echo $counter_str;
        }
    }

    public function scenarios()
    {
        return [
            'default' => ['id', 'name', 'description', 'code'],
        ];
    }


    /**
     * Search counters
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $this->load($params);
        $query = self::find();
        foreach ($this->attributes as $name => $value) {
            if (!empty($value)) {
                if ($name == 'id') {
                    $query->andWhere("`$name` = :$name", [":$name" => $value]);
                } else {
                    $query->andWhere("`$name` LIKE :$name", [":$name" => "%$value%"]);
                }
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
}
