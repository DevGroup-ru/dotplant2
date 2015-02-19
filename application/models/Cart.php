<?php

namespace app\models;

use Yii;
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
            [['id', 'user_id'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['items_json'], 'string'],
            [['total_price', 'items_count'], 'number']
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

    /**
     * @param bool $create
     * @return Cart
     */
    public static function getCart($create = false)
    {
        Yii::beginProfile("GetCart");
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
        Yii::endProfile("GetCart");
        return self::$cart;
    }

    /**
     * @param bool $saveProducts
     * @return bool
     */
    public function reCalc($saveProducts = false)
    {
        $totalPrice = 0;
        $itemsCount = 0;
        $products = Product::find()->where(['in', 'id', array_keys($this->items)])->all();
        $cartCountsUniqueProducts = Config::getValue('shop.cartCountsUniqueProducts', '0') === '1';
        
        $items = [];
        if ($saveProducts) {
            $this->products = [];
        }
        foreach ($products as $product) {
            //backward compatibility
            if(!is_array($this->items[$product->id])){
                $this->items[$product->id] = [
                    'quantity' => $this->items[$product->id],
                    'additionalParams' => '{"additionalPrice":0}',
                ];
            }
            $items[$product->id] = $this->items[$product->id];
            if(array_key_exists('additionalParams', $items[$product->id])){
                $additionalParams = json_decode($items[$product->id]['additionalParams']);
            }else{
                $additionalParams =  json_decode('{"additionalPrice":0}');
            }
            if ($cartCountsUniqueProducts === true) {
                $itemsCount++;
            } else {
                $itemsCount += $this->items[$product->id]['quantity'];
            }
            $totalPrice += $this->items[$product->id]['quantity'] * ($product->price + $additionalParams->additionalPrice);
            if ($saveProducts) {
                $this->products[$product->id] = $product;
            }
        }
        $this->total_price = $totalPrice;
        $this->items_count = $itemsCount;
        $this->items = $items;
        return $this->save();
    }

    public function getProducts()
    {
        return $this->products;
    }
}
