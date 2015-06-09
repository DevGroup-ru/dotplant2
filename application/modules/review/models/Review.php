<?php

namespace app\modules\review\models;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use app\models\Submission;
use yii\caching\TagDependency;
use app\properties\HasProperties;
use yii\db\ActiveQuery;

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
 */
class Review extends \yii\db\ActiveRecord
{
    const STATUS_NEW = 'NEW';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_NOT_APPROVED = 'NOT APPROVED';

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
            [['submission_id','object_model_id'],'integer'],
            [['review_text','rating_id','status'],'string'],
            ['author_email', 'email']
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
            ],
            'search' => [
                'object_id',
                'status',
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
        $cacheKey = 'Reviews: ' . (int)$object_model_id . ':' . (int)$formId . ':' . (int)$sort;
        $models = Yii::$app->cache->get($cacheKey);
        if (false === $models) {
            $models = Review::find()
                ->with(
                    [
                        'submission' => function ($query) use ($formId) {
                            /** @var ActiveQuery $query */
                            $query->andWhere('spam != :spam', [':spam' => Yii::$app->formatter->asBoolean(true)]);
                            $query->andWhere(['is_deleted' => 0]);
                            $query->andWhere(['form_id' => $formId]);
                        }
                    ]
                )
                ->where(
                    [
                        'object_model_id' => $object_model_id,
                        'object_id' => $object_id,
                        'status' => self::STATUS_APPROVED,
                    ]
                )
                ->all();
            if (false!== empty($models)) {
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
}
