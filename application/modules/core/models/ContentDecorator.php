<?php

namespace app\modules\core\models;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%content_decorators}}".
 *
 * @property integer $id
 * @property string $added_by_ext
 * @property integer $post_decorator
 * @property string $class_name
 */
class ContentDecorator extends \yii\db\ActiveRecord
{
    private static $allDecorators = null;

    private $object = null;

    const TYPE_PRE_DECORATOR = 0;
    const TYPE_POST_DECORATOR = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%content_decorators}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['added_by_ext', 'class_name'], 'required'],
            [['post_decorator', 'sort_order',], 'integer'],
            [['added_by_ext', 'class_name'], 'string', 'max' => 255],
            [['post_decorator',], 'default', 'value' => self::TYPE_PRE_DECORATOR,],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'added_by_ext' => Yii::t('app', 'Added By Ext'),
            'post_decorator' => Yii::t('app', 'Post Decorator'),
            'class_name' => Yii::t('app', 'Class Name'),
            'sort_order' => Yii::t('app', 'Sort order'),
        ];
    }

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
     * Retrieves all decorators from db, using cache and static variable caching
     * @return ContentDecorator[]
     * @throws \Exception
     */
    public static function getAllDecorators()
    {
        if (static::$allDecorators === null) {
            static::$allDecorators = self::getDb()->cache(
                function($db) {
                    return self::find()
                        ->orderBy([
                            'sort_order' => SORT_ASC,
                        ])->all($db);
                },
                86400,
                new TagDependency([
                    'tags' => ActiveRecordHelper::getCommonTag(self::className()),
                ])
            );
        }
        return static::$allDecorators;
    }

    /**
     * Returns decorators by type
     * @param int $post TYPE_PRE_DECORATOR for pre decorators, TYPE_POST_DECORATOR for post decorators
     * @return ContentDecorator[]
     */
    public static function getDecorators($post = 0)
    {
        $decorators = static::getAllDecorators();
        return array_filter(
            $decorators,
            function ($value) use ($post) {
                /** @var ContentDecorator $value */
                return intval($value->post_decorator) === $post;
            }
        );
    }

    /**
     * @return \app\modules\core\decorators\BaseDecorator
     * @throws \yii\base\InvalidConfigException
     */
    public function getObject()
    {
        if ($this->object === null) {
            $this->object = Yii::createObject($this->class_name);
        }
        return $this->object;
    }

    /**
     * @param \yii\base\Application $app
     * @param \yii\base\Controller $controller
     * @return void
     */
    public function subscribe($app, $controller)
    {
        $this->getObject()->subscribe($app, $controller);
    }
}
