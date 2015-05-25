<?php

namespace app\modules\review\models;

use app\backgroundtasks\traits\SearchModelTrait;
use app\models\Object;
use app\models\Product;
use app\models\PropertyGroup;
use app\modules\page\models\Page;
use app\modules\user\models\User;
use Yii;
use app\models\Submission;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use app\properties\HasProperties;
use yii\helpers\VarDumper;
use app\models\Property;
use app\models\Form;
use yii\db\Query;

/**
 * This is the model class for table "review".
 *
 * @property integer $submission_id
 * @property integer $object_id
 * @property integer $object_model_id
 * @property integer $status
 * @property string $rating_id
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
            [['submission_id','object_id','object_model_id','author_email','review_text'],'required'],
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
                'object_id',
            ],
            'default' => [
                'submission_id',
                'object_model_id',
                'author_email',
                'review_text',
                'object_id',
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

    public static function getByResourceName()
    {
        return VarDumper::dump($_POST, 10,1);
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' =>Yii::t('app', 'ID'),
            'submission_id' => Yii::t('app', 'Submission ID'),
            'object_id' => Yii::t('app', 'Object ID'),
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
                    ['submission' => function ($query) use ($formId){
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
                                \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(Review::className()),
                            ],
                        ]
                    )
                );
            }
        }
        return $models;
    }

    public function search($params)
    {
        /* @var $query \yii\db\ActiveQuery */
        $query = self::find();
        $query->joinWith('submission');
        $dataProvider = new ActiveDataProvider(
          [
              'query' => $query,
              'pagination' => [
                  'pageSize' => 10,
              ],
              'sort' => ['defaultOrder' => ['submission_id' => SORT_DESC]]
          ]
        );
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        $this->addCondition($query, $this->tableName(), 'author_email');
        $this->addCondition($query, $this->tableName(), 'review_text');
        return $dataProvider;
    }



    public function ss($params)
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
