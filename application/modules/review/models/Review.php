<?php

namespace app\modules\review\models;

use app\actions\SubmitFormAction;
use app\backgroundtasks\traits\SearchModelTrait;
use app\models\Object;
use app\models\Product;
use app\models\PropertyGroup;
use app\modules\page\models\Page;
use app\modules\user\models\User;
use Yii;
use yii\base\Exception;
use app\models\Submission;
use yii\base\Model;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use app\properties\HasProperties;
use yii\helpers\VarDumper;
use app\models\Property;
use yii\base\ModelEvent;

/**
 * This is the model class for table "review".
 *
 * @property integer $submission_id
 * @property integer $object_model_id
 * @property integer $status
 * @property string $rating_id
 * @property \app\modules\user\models\User $user
 * @property Product $product
 */
class Review extends \yii\db\ActiveRecord
{
    use SearchModelTrait;

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
        return [
            [['submission_id','object_model_id','author_email','review_text'],'required'],
            [['submission_id','object_model_id'],'integer'],
            [['review_text','rating_id','status'],'string'],
            ['author_email', 'email']
        ];
////        if ($this->useCaptcha) {
////            $rules[] = [['captcha'], 'captcha', 'captchaAction' => '/default/captcha'];
////            $rules[] = [['captcha'], 'required'];
////        }
//        return $rules;
    }
    public function scenarios()
    {
        return [
            'check' => [
                'review_text',
                'author_email',
                'object_model_id',
            ],
            'default' => [
                'submission_id',
                'object_model_id',
                'author_email',
                'review_text'
            ],
            'search' => [
                'status',
            ]
        ];
    }


    public function behaviors()
    {
        return [
            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
            ],
            [
                'class' => HasProperties::className(),
            ],
        ];
    }

    public static function getStatuses()
    {
        return [
            self::STATUS_NEW => 'new',
            self::STATUS_APPROVED => 'approved',
            self::STATUS_NOT_APPROVED => 'not approved',
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' =>Yii::t('app', 'ID'),
            'submission_id' => Yii::t('app', 'Submission ID'),
            'object_model_id' => Yii::t('app', 'Object Model ID'),
            'author_email' => Yii::t('app', 'Author email'),
            'review_text' => Yii::t('app', 'Review text'),
            'status' => Yii::t('app', 'Status'),
            'rating_id' => 'Rating id',
        ];
    }

    public function getSubmission()
    {
        return $this->hasOne(Submission::className(), ['id' => 'submission_id']);
    }

    /**
     * Получает отзывы по конкретной инстанции модели
     */
    public static function getForObjectModel($object_model_id, $sort = SORT_ASC)
    {
        /**@var $models \app\modules\review\models\Review[] */
        $models = Review::find()
            ->with(
                ['submission' => function ($query) {
                    $query->andWhere('spam != :spam', [':spam' => Yii::$app->formatter->asBoolean(true)]);
                    $query->andWhere(['is_deleted' => 0]);
                    }
                ]
            )
            ->where(
                [
                    'object_model_id' => $object_model_id,
                    //'status' => self::STATUS_APPROVED,
                ]
            )
            ->all();
        /**
         *@var $model \app\modules\review\models\Review
         *@var $submission \app\models\Submission
         */
        foreach ($models as $model) {
            if (null !== $submission = Submission::findOne(['id' => $model->submission_id])) {
                VarDumper::dump($submission->abstractModel->attributes, 3, true);
                $submissionObject = Object::getForClass(Submission::className());
                $groups = PropertyGroup::getForModel($submissionObject->id, $submission->id);
                foreach ($groups as $group) {
                    $properties = Property::getForGroupId($group->id);
                    VarDumper::dump($properties, 10,1);
                }

            }
        }
        return $models;



        $cacheKey = 'Reviews: ' . (int)$object_id . ':' . (int)$object_model_id . ':' . (int)$sort;
        $models = Yii::$app->cache->get($cacheKey);
        if ($models === false) {
            $models = Review::find()
                ->where(
                    [
                        'object_id' => $object_id,
                        'object_model_id' => $object_model_id,
                        'status' => self::STATUS_APPROVED,
                    ]
                )
                ->orderBy(['date_submitted' => $sort])
                ->all();
            $object = Object::findById($object_id);
            if ($object !== null) {
                Yii::$app->cache->set(
                    $cacheKey,
                    $models,
                    86400,
                    new TagDependency(
                        [
                            'tags' => [
                                \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag($object, $object_id),
                                \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag($object->object_class, $object_model_id),
                                \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(Review::className()),
                            ],
                        ]
                    )
                );
            }
        }
        return $models;
    }

    public function productSearch($params)
    {
        /* @var $query \yii\db\ActiveQuery */
        $query = self::find();
        $query->joinWith('user');
        $object = Object::getForClass(Product::className());
        $query->andWhere(['object_id' => $object->id]);
        $query->joinWith('product');
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                ],
                'sort'=> ['defaultOrder' => ['id'=>SORT_DESC]]
            ]
        );
        $dataProvider->sort->attributes['username'] = [
            'asc' => [User::tableName() . '.username' => SORT_ASC],
            'desc' => [\app\modules\user\models\User::tableName() . '.username' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['name'] = [
            'asc' => [Product::tableName() . '.name' => SORT_ASC],
            'desc' => [Product::tableName() . '.name' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['slug'] = [
            'asc' => [Product::tableName() . '.slug' => SORT_ASC],
            'desc' => [Product::tableName() . '.slug' => SORT_DESC],
        ];
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        $this->addCondition($query, $this->tableName(), 'object_id');
        $this->addCondition($query, Product::tableName(), 'name', true);
        $this->addCondition($query, Product::tableName(), 'slug', true);
        $this->addCondition($query, \app\modules\user\models\User::tableName(), 'username', true);
        $this->addCondition($query, $this->tableName(), 'author_name', true);
        $this->addCondition($query, $this->tableName(), 'author_email', true);
        $this->addCondition($query, $this->tableName(), 'author_phone', true);
        $this->addCondition($query, $this->tableName(), 'status');
        $this->addCondition($query, $this->tableName(), 'text', true);
        $this->addCondition($query, $this->tableName(), 'rate');
        return $dataProvider;
    }

    public function pageSearch($params)
    {
        /* @var $query \yii\db\ActiveQuery */
        $query = self::find();
        $query->joinWith('user');
        $object = Object::getForClass(Page::className());
        $query->andWhere(['object_id' => $object->id]);
        $query->joinWith('page');
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                ],
                'sort'=> ['defaultOrder' => ['id'=>SORT_DESC]]
            ]
        );
        $dataProvider->sort->attributes['username'] = [
            'asc' => [User::tableName() . '.username' => SORT_ASC],
            'desc' => [\app\modules\user\models\User::tableName() . '.username' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['name'] = [
            'asc' => [Page::tableName() . '.name' => SORT_ASC],
            'desc' => [Page::tableName() . '.name' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['slug'] = [
            'asc' => [Page::tableName() . '.slug' => SORT_ASC],
            'desc' => [Page::tableName() . '.slug' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['slug_compiled'] = [
            'asc' => [Page::tableName() . '.slug_compiled' => SORT_ASC],
            'desc' => [Page::tableName() . '.slug_compiled' => SORT_DESC],
        ];
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        $this->addCondition($query, $this->tableName(), 'object_id');
        $this->addCondition($query, Page::tableName(), 'name', true);
        $this->addCondition($query, Page::tableName(), 'slug', true);
        $this->addCondition($query, Page::tableName(), 'slug_compiled', true);
        $this->addCondition($query, \app\modules\user\models\User::tableName(), 'username', true);
        $this->addCondition($query, $this->tableName(), 'author_name', true);
        $this->addCondition($query, $this->tableName(), 'author_email', true);
        $this->addCondition($query, $this->tableName(), 'author_phone', true);
        $this->addCondition($query, $this->tableName(), 'status');
        $this->addCondition($query, $this->tableName(), 'text', true);
        $this->addCondition($query, $this->tableName(), 'rate');
        return $dataProvider;
    }
}
