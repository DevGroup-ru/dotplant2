<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\Json;

/**
 * This is the model class for table "cart".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $create_time
 * @property string $update_time
 * @property string $items_json
 * @property integer $items_count
 * @property float $total_price
 * @property array $items
 */
class Cart extends ActiveRecord
{
    protected static $cart;
    protected $products;
    public $items = [];

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'create_time',
                'updatedAtAttribute' => 'update_time',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cart}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'items_count'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['items_json'], 'string'],
            [['total_price'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('shop', 'ID'),
            'user_id' => Yii::t('shop', 'User ID'),
            'create_time' => Yii::t('shop', 'Create Time'),
            'update_time' => Yii::t('shop', 'Update Time'),
            'items' => Yii::t('shop', 'Items'),
            'items_count' => Yii::t('shop', 'Items Count'),
            'total_price' => Yii::t('shop', 'Total Price'),
        ];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $this->items_json = Json::encode($this->items);
        if ($insert || !isset($this->oldAttributes['items_count']) || $this->oldAttributes['items_count'] == 0) {
            $this->create_time = new Expression("NOW()");
        }
        return true;
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->items = Json::decode($this->items_json);
    }

    public static function getCart($create = false)
    {
        if (is_null(self::$cart) && Yii::$app->session->has('cartId')) {
            self::$cart = self::findOne(['id' => Yii::$app->session->get('cartId')]);
        }
        if (is_null(self::$cart) && !Yii::$app->user->isGuest) {
            self::$cart = self::findOne(['user_id' => Yii::$app->user->id]);
        }
        if (is_null(self::$cart) && $create) {
            $cart = new static;
            $cart->user_id = !Yii::$app->user->isGuest ? Yii::$app->user->id : 0;
            if ($cart->save()) {
                self::$cart = $cart;
                Yii::$app->session->set('cartId', $cart->id);
            }
        }
        return self::$cart;
    }

    public function reCalc($saveProducts = false)
    {
        $totalPrice = 0;
        $itemsCount = 0;
        $transaction = Yii::$app->db->beginTransaction();
        $products = Product::find()->where(['in', 'id', array_keys($this->items)])->all();
        $items = [];
        if ($saveProducts) {
            $this->products = [];
        }
        try {
            foreach ($products as $product) {
                $items[$product->id] = $this->items[$product->id];
                $itemsCount += $this->items[$product->id];
                $totalPrice += $this->items[$product->id] * $product->price;
                if ($saveProducts) {
                    $this->products[$product->id] = $product;
                }
            }
            $this->total_price = $totalPrice;
            $this->items_count = $itemsCount;
            $this->items = $items;
            if (!$this->save()) {
                throw new \Exception('Can\'t save the object');
            }
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }

    public function getProducts()
    {
        return $this->products;
    }
}
