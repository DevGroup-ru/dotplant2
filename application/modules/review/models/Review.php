<?php

namespace app\modules\review\models;

use app\models\Object;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use app\models\Submission;
use yii\caching\TagDependency;
use app\properties\HasProperties;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "review".
 *
 * @property integer $submission_id
 * @property integer $object_id
 * @property integer $object_model_id
 * @property integer $status
 * @property string $rating_id
 * @property string $author_email
 * @property string $review_text
 * @property integer $parent_id
 * @property integer $root_id
 *
 * @property Object $targetObject
 * @property ActiveRecord $targetObjectModel
 * @property Review[]|array $children
 * @property Review|null $child
 */
class Review extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 'NEW';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_NOT_APPROVED = 'NOT APPROVED';
    protected $targetObject;
    protected $targetObjectModel;
    public $captcha;
    public $useCaptcha = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%review}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['submission_id','object_id','object_model_id','author_email','review_text'],'required'],
            [['submission_id','object_model_id', 'parent_id', 'root_id'], 'integer'],
            [['review_text','rating_id','status'],'string'],
            ['author_email', 'email'],
            [['parent_id', 'root_id'], 'default', 'value' => 0],
        ];
        if ($this->useCaptcha) {
            $rules[] = [['captcha'], 'captcha', 'captchaAction' => '/default/captcha'];
            $rules[] = [['captcha'], 'required'];
        }
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'check' => [
                'review_text',
                'author_email',
                'object_model_id',
                'object_id',
                'captcha',
            ],
            'default' => [
                'submission_id',
                'object_model_id',
                'author_email',
                'review_text',
                'object_id',
                'status',
            ],
            'search' => [
                'object_id',
                'status',
                'object_model_id'
            ]
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
            [
                'class' => HasProperties::className(),
            ],
        ];
    }

    public static function getStatuses()
    {
        return [
            self::STATUS_NEW => Yii::t('app', 'New'),
            self::STATUS_APPROVED => Yii::t('app', 'Approved'),
            self::STATUS_NOT_APPROVED => Yii::t('app', 'Not approved'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'submission_id' => Yii::t('app', 'Submission ID'),
            'object_id' => Yii::t('app', 'Object ID'),
            'object_model_id' => Yii::t('app', 'Object Model ID'),
            'author_email' => Yii::t('app', 'Author email'),
            'review_text' => Yii::t('app', 'Review text'),
            'status' => Yii::t('app', 'Status'),
            'rating_id' => Yii::t('app', 'Rating ID'),
            'parent_id' => Yii::t('app', 'Parent ID'),
            'root_id' => Yii::t('app', 'Root ID'),
        ];
    }

    public function getSubmission()
    {
        return $this->hasOne(Submission::className(), ['id' => 'submission_id']);
    }

    /**
     * Получает отзывы по конкретной инстанции модели
     * @var $models \app\modules\review\models\Review[]
     * @return array
     */
    public static function getForObjectModel($object_model_id, $object_id, $formId, $sort = SORT_ASC)
    {
        $cacheKey = implode(':', ['Reviews', $object_id, $object_model_id, $formId, $sort]);
        $models = Yii::$app->cache->get($cacheKey);
        if (false === $models) {
            $models = Review::find()
                ->with('submission')
                ->where(
                    [
                        'object_model_id' => $object_model_id,
                        'object_id' => $object_id,
                        'status' => self::STATUS_APPROVED,
                    ]
                )
                ->all();
            if (!empty($models)) {
                Yii::$app->cache->set(
                    $cacheKey,
                    $models,
                    86400,
                    new TagDependency(
                        [
                            'tags' => [
                                ActiveRecordHelper::getCommonTag(Review::className()),
                            ],
                        ]
                    )
                );
            }
        }
        return $models;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if (true === $insert) {
            if (0 === $this->root_id) {
                if (0 === $this->parent_id) {
                    $this->root_id = $this->id;
                } elseif (null !== $parent = static::findOne(['id' => $this->parent_id])) {
                    $this->root_id = $parent->id;
                }
                $this->save();
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (false === parent::beforeDelete()) {
            return false;
        }

        foreach ($this->children as $child) {
            /** @var Review $child */
            $child->delete();
        }

        return true;
    }

    /**
     * @return ActiveRecord|null
     */
    public function getTargetObjectModel()
    {
        if ($this->targetObjectModel !== null) {
            return $this->targetObjectModel;
        }
        if ($this->getTargetObject() === null) {
            return null;
        }
        /** @var ActiveRecord $class */
        $class = $this->getTargetObject()->object_class;
        $this->targetObjectModel = $class::findOne(['id' => $this->object_model_id]);
        return $this->targetObjectModel;
    }

    /**
     * @return Object|null
     */
    public function getTargetObject()
    {
        if ($this->targetObject === null) {
            $this->targetObject = Object::findById($this->object_id);
        }
        return $this->targetObject;
    }

    /**
     * @return ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(static::className(), ['parent_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getChild()
    {
        return $this->hasOne(static::className(), ['parent_id' => 'id']);
    }
}
