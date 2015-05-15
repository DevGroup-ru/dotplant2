<?php

namespace app\modules\shop\models;

use app\models\Config;
use app\modules\user\models\User;
use app\properties\HasProperties;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%order}}".
 * Model fields:
 * @property integer $id
 * @property integer $user_id
 * @property integer $manager_id
 * @property string $start_date
 * @property string $update_date
 * @property string $end_date
 * @property integer $cart_forming_time
 * @property integer $order_stage_id
 * @property integer $shipping_option_id
 * @property integer $payment_type_id
 * @property integer $assigned_id
 * @property integer $tax_id
 * @property string $external_id
 * @property integer $items_count
 * @property double $total_price
 * @property double $shipping_price
 * @property double $total_price_with_shipping
 * @property double $total_payed
 * @property string $hash
 * @property bool $is_deleted
 * @property bool $temporary
 * @property bool $show_price_changed_notification
 * Relations:
 * @property \app\properties\AbstractModel $abstractModel
 * @property OrderItem[] $items
 * @property ShippingOption $shippingOption
 * @property OrderStage $stage
 * @property PaymentType $paymentType
 * @property OrderTransaction[] $transactions
 * @property User $user
 * @property User $manager
 * @property float $fullPrice
 */
class Order extends \yii\db\ActiveRecord
{
    /**
     * @var Order $order
     */
    protected static $order;
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => HasProperties::className(),
            ],
            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
            ],
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'start_date',
                'updatedAtAttribute' => 'update_date',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'user_id',
                    'order_stage_id',
                    'total_price',
                    'shipping_option_id',
                    'payment_type_id',
                ],
                'required'
            ],
            [['user_id', 'order_stage_id', 'shipping_option_id', 'payment_type_id', 'assigned_id', 'tax_id'], 'integer'],
            [['start_date', 'end_date', 'update_date'], 'safe'],
            [['start_date', 'end_date', 'update_date'], 'safe'],
            [['total_price', 'items_count', 'shipping_price', 'total_price_with_shipping', 'total_payed'], 'number'],
            [['external_id'], 'string', 'max' => 38],
            [['is_deleted', 'temporary', 'show_price_changed_notification'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            'default' => [
                'user_id',
                'cart_forming_time',
                'order_stage_id',
                'shipping_option_id',
                'items_count',
                'total_price',
                'hash',
            ],
            'search' => [
                'id',
                'user_id',
                'manager_id',
                'start_date',
                'end_date',
                'order_stage_id',
                'shipping_option_id',
                'payment_type_id',
                'items_count',
                'total_price',
                'hash',
            ],
            'shippingOption' => ['shipping_option_id', 'order_stage_id'],
            'paymentType' => ['payment_type_id', 'order_stage_id'],
            'changeManager' => ['manager_id'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User'),
            'manager_id' => Yii::t('app', 'Manager'),
            'start_date' => Yii::t('app', 'Start Date'),
            'update_date' => Yii::t('app', 'Update date'),
            'end_date' => Yii::t('app', 'End Date'),
            'cart_forming_time' => Yii::t('app', 'Cart Forming Time'),
            'order_stage_id' => Yii::t('app', 'Stage'),
            'shipping_option_id' => Yii::t('app', 'Shipping Option'),
            'payment_type_id' => Yii::t('app', 'Payment Type'),
            'assigned_id' => Yii::t('app', 'Assigned'),
            'tax_id' => Yii::t('app', 'Tax'),
            'external_id' => Yii::t('app', 'External ID'),
            'items_count' => Yii::t('app', 'Items Count'),
            'total_price' => Yii::t('app', 'Total Price'),
            'shipping_price' => Yii::t('app', 'Shipping price'),
            'total_price_with_shipping' => Yii::t('app', 'Total price with shipping'),
            'total_payed' => Yii::t('app', 'Total payed'),
            'hash' => Yii::t('app', 'Hash'),
            'is_deleted' => Yii::t('app', 'Is deleted'),
            'temporary' => Yii::t('app', 'Temporary'),
            'show_price_changed_notification' => Yii::t('app', 'Show price changed notification'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if (isset($changedAttributes['order_stage_id']) && !is_null(
                $changedAttributes['order_stage_id']
            ) && $this->order_stage_id == 3
        ) {
            if (isset($this->abstractModel->attributes['email']) && !empty($this->abstractModel->attributes['email'])
            ) {
                try {
                    Yii::$app->mail->compose(
                            Config::getValue(
                                'shop.clientOrderEmailTemplate',
                                '@app/views/cart/client-order-email-template'
                            ),
                            [
                                'order' => $this,
                            ]
                        )->setTo(trim($this->abstractModel->email))->setFrom(
                            Yii::$app->mail->transport->getUsername()
                        )->setSubject(Yii::t('app', 'Order #{orderId}', ['orderId' => $this->id]))->send();
                } catch (\Exception $e) {
                    // do nothing
                }
            }
            $orderEmail = Config::getValue('shop.orderEmail', null);
            // @todo Implement via OrderStageLeaf
            if (!empty($orderEmail)) {
                try {
                    Yii::$app->mail->compose(
                            Config::getValue(
                                'shop.orderEmailTemplate',
                                '@app/views/cart/order-email-template'
                            ),
                            [
                                'order' => $this,
                            ]
                        )->setTo(explode(',', $orderEmail))->setFrom(
                            Yii::$app->mail->transport->getUsername()
                        )->setSubject(Yii::t('app', 'Order #{orderId}', ['orderId' => $this->id]))->send();
                } catch (\Exception $e) {
                    // do nothing

                }
            }
        }
        parent::afterSave($insert, $changedAttributes);
    }

    public function getItems()
    {
        return $this->hasMany(OrderItem::className(), ['order_id' => 'id']);
    }

    public function getStage()
    {
        return $this->hasOne(OrderStage::className(), ['id' => 'order_stage_id']);
    }

    public function getShippingOption()
    {
        return $this->hasOne(ShippingOption::className(), ['id' => 'shipping_option_id']);
    }

    public function getPaymentType()
    {
        return $this->hasOne(PaymentType::className(), ['id' => 'payment_type_id']);
    }

    public function getTransactions()
    {
        return $this->hasMany(OrderTransaction::className(), ['order_id' => 'id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getManager()
    {
        return $this->hasOne(User::className(), ['id' => 'manager_id']);
    }

    public function getFullPrice()
    {
        $fullPrice = $this->total_price;
        if (!is_null($this->shippingOption)) {
            $fullPrice += $this->shippingOption->cost;
        }
        return $fullPrice;
    }

    /**
     * Первое удаление в корзину, второе из БД
     *
     * @return bool
     */
    public function beforeDelete()
    {
        if (intval(Config::getValue('shop.AbilityDeleteOrders')) !== 1) {
            return false;
        }
        if (!parent::beforeDelete()) {
            return false;
        }
        if (0 === intval($this->is_deleted)) {
            $this->is_deleted = 1;
            $this->save();
            return false;
        }
        return true;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function reCalc()
    {
        $totalPrice = 0;
        $itemsCount = 0;
        $cartCountsUniqueProducts = Config::getValue('shop.cartCountsUniqueProducts', '0') === '0';

        foreach ($this->items as $item) {
            if (is_null($item->product)) {
                $item->delete();
            } else {
                $options = Json::decode($item['additional_options']);
                $totalPrice += $item->quantity * ($item->product->convertedPrice() + $options['additionalPrice']);
                if ($cartCountsUniqueProducts === true) {
                    $itemsCount ++;
                } else {
                    $itemsCount += $item->quantity;
                }
            }
        }
        $this->total_price = $totalPrice;
        $this->items_count = $itemsCount;
        return $this->save(true, ['total_price', 'items_count']);
    }

    /**
     * Search tasks
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /** @var $query \yii\db\ActiveQuery */
        $query = self::find();

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]
        );
        if (!($this->load($params))) {
            return $dataProvider;
        }
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['is_deleted' => $this->is_deleted]);
        return $dataProvider;
    }

    /**
     * Get current order.
     * @param bool $create Create order if it does not exist
     * @return Order
     */
    public static function getOrder($create = false)
    {
        Yii::beginProfile("GetOrder");
        if (is_null(self::$order) && Yii::$app->session->has('orderId')) {
            self::$order = self::findOne(['id' => Yii::$app->session->get('orderId')]);
        }
        if (is_null(self::$order) && !Yii::$app->user->isGuest) {
            self::$order = self::findOne(['user_id' => Yii::$app->user->id]);
        }
        if ((is_null(self::$order) || is_null(self::$order->stage) || self::$order->stage->is_in_cart == 0)
            && $create === true
        ) {
            $initialOrderStage = OrderStage::getInitialStage();
            if (is_null($initialOrderStage)) {
                throw new Exception('Initial order stage not found');
            }
            $order = new static;
            $order->user_id = !Yii::$app->user->isGuest ? Yii::$app->user->id : 0;
            $order->order_stage_id = $initialOrderStage->id;
            $order->temporary = 1;
            mt_srand();
            $order->hash = md5(mt_rand() . uniqid());
            if ($order->save(true, ['user_id', 'temporary', 'hash', 'order_stage_id'])) {
                self::$order = $order;
                Yii::$app->session->set('orderId', $order->id);
            }
        }
        Yii::endProfile("GetOrder");
        return self::$order;
    }

    /**
     * Calculate order total price and items count with all additional markups.
     * @param bool $callSave Call save method after calculating.
     * @param bool $deleteNotActiveProducts Delete Order Item if product is not active or is not exist.
     * @return bool
     */
    public function calculate($callSave = false, $deleteNotActiveProducts = true)
    {
        $itemsCount = 0;
        $totalPrice = 0;
        foreach ($this->items as $item) {
            if ($deleteNotActiveProducts && (!isset($item->product) || $item->product->active == 0)) {
                $item->delete();
                continue;
            }
            if (Yii::$app->getModule('shop')->countChildrenProducts == 1) {
                $itemsCount += Yii::$app->getModule('shop')->countUniqueProductsOnly == 1 ? 1 : $item->quantity;
            } else {
                if ($item->parent_id == 0) {
                    $itemsCount += Yii::$app->getModule('shop')->countUniqueProductsOnly == 1 ? 1 : $item->quantity;
                }
            }
            // @todo get order item discount
            $totalPrice += $item->total_price;
        }
        if (!is_null($this->shippingOption)) {
            // @todo get shipping price
        }
        // @todo get order discount
        $this->items_count = $itemsCount;
        $this->total_price = $totalPrice;
        $this->total_price_with_shipping = $totalPrice + $this->shipping_price;
        $this->total_price_with_shipping = $totalPrice + $this->shipping_price;
        return $callSave ? $this->save(true, ['items_count', 'total_price', 'total_price_with_shipping']) : true;
    }
}
