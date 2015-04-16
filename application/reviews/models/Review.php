<?php

namespace app\reviews\models;

use app\backgroundtasks\traits\SearchModelTrait;
use app\models\Object;
use app\models\Product;
use app\models\Page;
use app\modules\user\models\User;
use Yii;
use yii\base\Exception;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "review".
 *
 * @property integer $id
 * @property integer $object_id
 * @property integer $object_model_id
 * @property string $date_submitted
 * @property integer $author_user_id
 * @property string $author_name
 * @property string $author_email
 * @property string $author_phone
 * @property integer $status
 * @property string $text
 * @property integer $rate
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

    public $name;
    public $slug;
    public $slug_compiled;
    public $username;
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
            [['author_name', 'text'], 'required', 'on' => 'default'],
            [['object_id', 'object_model_id', 'author_user_id', 'rate'], 'integer'],
            [['date_submitted', 'username', 'name', 'slug'], 'safe'],
            [['author_name', 'author_email', 'author_phone', 'text', 'status', 'rating_id'], 'string'],
            [['author_email'], 'email'],
        ];
        if ($this->useCaptcha) {
            $rules[] = [['captcha'], 'captcha', 'captchaAction' => '/default/captcha'];
            $rules[] = [['captcha', 'object_model_id'], 'required'];
        } else {
            $rules[] = [['object_model_id'], 'required'];
        }
        return $rules;
    }

    public function behaviors()
    {
        return [
            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
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
            'id' => Yii::t('app', 'ID'),
            'object_id' => Yii::t('app', 'Object ID'),
            'object_model_id' => Yii::t('app', 'Object Model ID'),
            'date_submitted' => Yii::t('app', 'Date Submitted'),
            'author_user_id' => Yii::t('app', 'Author User ID'),
            'author_name' => Yii::t('app', 'Username'),
            'author_email' => Yii::t('app', 'Email'),
            'author_phone' => Yii::t('app', 'Phone'),
            'status' => Yii::t('app', 'Status'),
            'text' => Yii::t('app', 'Text'),
            'rate' => Yii::t('app', 'Rate'),
            'slug' => Yii::t('app', 'Slug'),
            'slug_compiled' => Yii::t('app', 'Slug Compiled'),
            'rating_id' => 'Rating id',
            'name' => Yii::t('app', 'Name'),
            'username' => 'Login',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(\app\modules\user\models\User::className(), ['id' => 'author_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     * @throws Exception
     */
    public function getProduct()
    {
        $object = Object::getForClass(Product::className());
        if ($object !== null) {
            return $this->hasOne(Product::className(), ['id' => 'object_model_id']);
        } else {
            throw new Exception('Object not found in database');
        }
    }
    public function getPage()
    {
        $object = Object::getForClass(Page::className());
        if ($object !== null) {
            return $this->hasOne(Page::className(), ['id' => 'object_model_id']);
        } else {
            throw new Exception('Object not found in database');
        }
    }

    /**
     * Получает отзывы по конкретной инстанции модели
     */
    public static function getForObjectModel($object_id, $object_model_id, $sort = SORT_ASC)
    {
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

    public function scenarios()
    {
        return [
            'default' => [
                'object_id',
                'object_model_id',
                'author_user_id',
                'author_name',
                'author_email',
                'author_phone',
                'text',
                'rate',
                'captcha',
            ],
            'search' => [
                'object_id',
                'name',
                'slug',
                'slug_compiled',
                'username',
                'author_name',
                'author_email',
                'author_phone',
                'status',
                'text',
                'rate',
            ]
        ];
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
