<?php

namespace app\modules\core\models;

use app;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%events}}".
 *
 * @property integer $id
 * @property string $owner_class_name
 * @property string $event_name
 * @property string $event_class_name
 * @property string $selectorPrefix
 * @property string $event_description
 * @property string $documentation_link
 */
class Events extends \yii\db\ActiveRecord
{
    /**
     * @var Events[] Identity map, key is class name
     */
    public static $identity_map_by_classname = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%events}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['owner_class_name', 'event_name', 'event_class_name', 'event_description'], 'required'],
            [['event_description','selector_prefix'], 'string'],
            [['owner_class_name', 'event_name', 'event_class_name', 'documentation_link'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'owner_class_name' => Yii::t('app', 'Owner Class Name'),
            'event_name' => Yii::t('app', 'Event Name'),
            'event_class_name' => Yii::t('app', 'Event Class Name'),
            'selector_prefix' => Yii::t('app', 'Selector Prefix'),
            'event_description' => Yii::t('app', 'Event Description'),
            'documentation_link' => Yii::t('app', 'Documentation Link'),
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
     * Finds Event by class name using cache and identity map.
     * @param string $class_name
     * @return Events
     * @throws \Exception
     */
    public static function findByClassName($class_name)
    {
        if (isset(static::$identity_map_by_classname[$class_name]) === false) {
            static::$identity_map_by_classname[$class_name] = self::getDb()->cache(
                function($db) use ($class_name) {
                    return self::find()
                        ->where(['event_class_name' => $class_name])
                        ->with('handlers')
                        ->one($db);
                },
                86400,
                new TagDependency([
                    'tags' => [
                        ActiveRecordHelper::getObjectTag(static::className(), $class_name),
                    ]
                ])
            );
        }
        return static::$identity_map_by_classname[$class_name];
    }

    /**
     * Relation to handlers of event!
     * @return \yii\db\ActiveQuery
     */
    public function getHandlers()
    {
        return $this->hasMany(EventHandlers::className(), ['event_id' => 'id'])->orderBy(['sort_order' => SORT_ASC]);
    }

    /**
     * Returns Events model by name using identity map by classname and cache
     *
     * @param null|string $name Event name to find
     * @return Events|null
     */
    public static function findByName($name = null)
    {
        if (empty($name)) {
            return null;
        }

        foreach (static::$identity_map_by_classname as $class_name => $model) {
            if ($model->event_name === $name) {
                return $model;
            }
        }
        $cacheKey = "Event:byName:$name";
        $model = Yii::$app->cache->get($cacheKey);
        if ($model === false) {
            $model = self::find()
                ->where(['event_name' => $name])
                ->one();
            if ($model !== null) {
                Yii::$app->cache->set(
                    $cacheKey,
                    $model,
                    86400,
                    new TagDependency([
                        'tags' => [
                            ActiveRecordHelper::getObjectTag(static::className(), $model->event_class_name),
                        ]
                    ])
                );
            }
        }
        static::$identity_map_by_classname[$model->event_class_name] = $model;
        return $model;
    }

    /**
     * Returns Events models by names using identity map by classname and cache
     * @param string[] $names Array of event names to find
     * @return Events[]
     */
    public static function findByNames($names)
    {
        $result = [];

        foreach (static::$identity_map_by_classname as $class_name => $model) {
            if (in_array($model->name, $names) === true) {
                $result[] = $model;
                if(($key = array_search($model->name, $names)) !== false) {
                    unset($names[$key]);
                }
            }
        }
        if (count($names) === 0) {
            return $result;
        }
        $cacheKey = "Event:byNames:".implode(',', $names);
        $models = Yii::$app->cache->get($cacheKey);
        if ($models === false) {
            $models = self::find()
                ->where(['event_name' => $names])
                ->with('handlers')
                ->all();

            Yii::$app->cache->set(
                $cacheKey,
                $models,
                86400,
                new TagDependency([
                    'tags' => [
                        ActiveRecordHelper::getCommonTag(Events::className())
                    ]
                ])
            );

        }
        foreach ($models as $model) {
            static::$identity_map_by_classname[$model->event_class_name] = $model;
            $result[] = $model;
        }

        return $result;
    }
}