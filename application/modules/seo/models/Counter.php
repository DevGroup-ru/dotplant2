<?php

namespace app\modules\seo\models;

use app\backend\BackendModule;
use app\backend\components\BackendController;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use yii\base\Event;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\web\View;

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
    const POSITION_AT_END_OF_BODY = 0;
    const POSITION_AT_BEGIN_OF_BODY = 1;
    const POSITION_AT_HEAD = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%seo_counter}}';
    }

    /**
     * @param $counter
     * @return string
     */
    private static function renderCounter($counter)
    {
        return
            "\n<!-- {$counter->name} counter -->\n"
            . "{$counter->code}"
            . "\n<!-- /{$counter->name} counter -->\n";
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description', 'code'], 'string'],
            [['code'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['position'], 'integer']
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
            'position' => \Yii::t('app', 'Position')
        ];
    }

    public static function renderCountersAtHead(Event $event)
    {
        $counters = self::find()->where(["position" => self::POSITION_AT_HEAD])->all();
        foreach ($counters as $counter) {
            $event->sender->jsFiles[View::POS_HEAD][] = static::renderCounter($counter);
        }
    }

    public static function renderCountersAtBeginningOfBody(Event $event)
    {
        static::renderCounters($event, static::POSITION_AT_BEGIN_OF_BODY);
    }

    public static function renderCountersAtEndOfBody(Event $event)
    {
        static::renderCounters($event, static::POSITION_AT_END_OF_BODY);
    }

    public function getPositionVariants()
    {
        return [
            self::POSITION_AT_END_OF_BODY => \Yii::t("app", "End of body tag"),
            self::POSITION_AT_BEGIN_OF_BODY => \Yii::t("app", "Beginning of body tag"),
            self::POSITION_AT_HEAD => \Yii::t("app", "Inside of head tag")
        ];
    }

    public static function renderCounters(Event $event, $mode = self::POSITION_AT_END_OF_BODY)
    {
        if (
            \Yii::$app->request->isAjax === false &&
            \Yii::$app->controller->module instanceof BackendModule === false &&
            \Yii::$app->controller instanceof BackendController === false
        ) {
            $cacheKey = \Yii::$app->getModule('seo')->cacheConfig['counterCache']['name'] . $mode;
            $counter_str = '';
            /* @var $counters Counter[] */
            if (false === $counters = \Yii::$app->getCache()->get($cacheKey)) {
                $counters = self::find()->where(["position" => $mode])->all();
                \Yii::$app->getCache()->set(
                    $cacheKey,
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
                $counter_str .= self::renderCounter($counter);
            }
            echo $counter_str;
        }
    }

    public
    function scenarios()
    {
        return [
            'default' => ['id', 'name', 'description', 'code', 'position'],
        ];
    }


    /**
     * Search counters
     * @param $params
     * @return ActiveDataProvider
     */
    public
    function search(
        $params
    ) {
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
