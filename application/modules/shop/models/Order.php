<?php

namespace app\modules\shop\models;

use app\modules\core\helpers\EventTriggeringHelper;
use app\modules\shop\events\OrderCalculateEvent;
use app\modules\shop\helpers\PriceHelper;
use app\modules\user\models\User;
use app\properties\HasProperties;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * This is the model class for table "{{%order}}".
 * Model fields:
 * @property integer $id
 * @property integer $user_id
 * @property integer $customer_id
 * @property integer $contragent_id
 * @property integer $manager_id
 * @property string $start_date
 * @property string $update_date
 * @property string $end_date
 * @property integer $cart_forming_time
 * @property integer $order_stage_id
 * @property integer $payment_type_id
 * @property integer $assigned_id
 * @property integer $tax_id
 * @property string $external_id
 * @property integer $items_count
 * @property double $total_price
 * @property double $total_payed
 * @property string $hash
 * @property bool $is_deleted
 * @property bool $temporary
 * @property bool $show_price_changed_notification
 * @property bool $in_cart
 * Relations:
 * @property \app\properties\AbstractModel $abstractModel
 * @property OrderItem[] $items
 * @property SpecialPriceObject[] $specialPriceObjects
 * @property ShippingOption $shippingOption
 * @property OrderStage $stage
 * @property PaymentType $paymentType
 * @property OrderTransaction[] $transactions
 * @property User $user
 * @property User $manager
 * @property float $fullPrice
 * @property Contragent $contragent
 * @property DiscountCode $code
 * @property Customer $customer
 * @property OrderDeliveryInformation $orderDeliveryInformation
 */
class Order extends \yii\db\ActiveRecord
{
    const IMMUTABLE_NONE = 0;
    const IMMUTABLE_USER = 1;
    const IMMUTABLE_MANAGER = 2;
    const IMMUTABLE_ASSIGNED = 4;
    const IMMUTABLE_ALL = 128;
    const ORDER_STATE_FINISH = 0;
    const ORDER_STATE_IN_PROCESS = 1;

    /**
     * @var Order $order
     */
    protected static $order;
    /** @var OrderStage $orderStage */
    protected $orderStage = null;

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
                    'customer_id',
                    'contragent_id',
                    'order_stage_id',
                    'total_price',
                    'payment_type_id',
                    'in_cart',
                ],
                'required'
            ],
            [
                [
                    'user_id',
                    'customer_id',
                    'contragent_id',
                    'order_stage_id',
                    'payment_type_id',
                    'assigned_id',
                    'tax_id'
                ],
                'integer'
            ],
            [['start_date', 'end_date', 'update_date'], 'safe'],
            [['total_price', 'items_count', 'total_payed'], 'number'],
            [['external_id'], 'string', 'max' => 38],
            [['is_deleted', 'temporary', 'show_price_changed_notification', 'in_cart'], 'boolean'],
            [['user_id', 'customer_id', 'contragent_id', 'in_cart'], 'default', 'value' => 0],
            [['temporary'], 'default', 'value' => 1],
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
                'items_count',
                'total_price',
                'hash',
                'in_cart',
            ],
            'search' => [
                'id',
                'user_id',
                'manager_id',
                'start_date',
                'end_date',
                'order_stage_id',
                'payment_type_id',
                'items_count',
                'total_price',
                'hash',
            ],
            'shippingOption' => ['order_stage_id'],
            'paymentType' => ['payment_type_id', 'order_stage_id'],
            'changeManager' => ['manager_id'],
            'backend' => [
                'user_id',
                'customer_id',
                'contragent_id',
            ],
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
            'customer_id' => Yii::t('app', 'Customer'),
            'contragent_id' => Yii::t('app', 'Contragent'),
            'manager_id' => Yii::t('app', 'Manager'),
            'start_date' => Yii::t('app', 'Start Date'),
            'update_date' => Yii::t('app', 'Update date'),
            'end_date' => Yii::t('app', 'End Date'),
            'cart_forming_time' => Yii::t('app', 'Cart Forming Time'),
            'order_stage_id' => Yii::t('app', 'Stage'),
            'payment_type_id' => Yii::t('app', 'Payment Type'),
            'assigned_id' => Yii::t('app', 'Assigned'),
            'tax_id' => Yii::t('app', 'Tax'),
            'external_id' => Yii::t('app', 'External ID'),
            'items_count' => Yii::t('app', 'Items Count'),
            'total_price' => Yii::t('app', 'Total Price'),
            'total_payed' => Yii::t('app', 'Total payed'),
            'hash' => Yii::t('app', 'Hash'),
            'is_deleted' => Yii::t('app', 'Is deleted'),
            'temporary' => Yii::t('app', 'Temporary'),
            'show_price_changed_notification' => Yii::t('app', 'Show price changed notification'),
            'in_cart' => Yii::t('app', 'In Cart'),
        ];
    }

    public function getItems()
    {
        return $this->hasMany(OrderItem::className(), ['order_id' => 'id']);
    }

    /**
     * @return OrderStage|null
     */
    public function getStage()
    {
        if (null === $this->orderStage) {
            $this->orderStage = $this->hasOne(OrderStage::className(), ['id' => 'order_stage_id']);
        }
        return $this->orderStage;
    }

    public function getShippingOption()
    {
        $orderDelivery = OrderDeliveryInformation::getByOrderId($this->id);
        return empty($orderDelivery) ? null : $orderDelivery->shippingOption;
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

    public function getCode()
    {
        return $this->hasOne(OrderCode::className(), ['order_id'=>'id']);
    }

    /**
     * @return Customer|null
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }

    public function getContragent()
    {
        return $this->hasOne(Contragent::className(), ['id' => 'contragent_id']);
    }

    public function getManager()
    {
        return $this->hasOne(User::className(), ['id' => 'manager_id']);
    }

    public function getSpecialPriceObjects()
    {
        return SpecialPriceObject::find()
            ->leftJoin(
                SpecialPriceList::tableName(),
                SpecialPriceList::tableName() . '.id =' . SpecialPriceObject::tableName() . '.special_price_list_id'
            )
            ->where(
                [
                    SpecialPriceObject::tableName() . '.object_model_id' => $this->id,
                    SpecialPriceList::tableName() . '.object_id' => $this->object->id
                ]
            )
            ->all();
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
     * @return OrderDeliveryInformation|null
     */
    public function getOrderDeliveryInformation()
    {
        return $this->hasOne(OrderDeliveryInformation::className(), ['order_id' => 'id']);
    }

    /**
     * Первое удаление в корзину, второе из БД
     *
     * @return bool
     */
    public function beforeDelete()
    {
        if (Yii::$app->getModule('shop')->deleteOrdersAbility === 0) {
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
        $customer = $this->getCustomer();
        if (0 === intval($customer->user_id)) {
            $customer->delete();
        }

        return true;
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
     * Create a new order.
     * @param bool $throwException Throw an exception if a order has not been saved
     * @param bool $assignToUser Assign to a current user
     * @return Order
     * @throws Exception
     */
    public static function create($throwException = true, $assignToUser = true, $dummyObject = false)
    {
        TagDependency::invalidate(Yii::$app->cache, ['Session:' . Yii::$app->session->id]);
        $initialOrderStage = OrderStage::getInitialStage();
        if (is_null($initialOrderStage)) {
            throw new Exception('Initial order stage not found');
        }
        $model = new static;
        $model->loadDefaultValues(false);
        $model->user_id = !Yii::$app->user->isGuest && $assignToUser ? Yii::$app->user->id : 0;
        $model->order_stage_id = $initialOrderStage->id;
        $model->in_cart = 1;
        $model->customer_id = 0;
        $model->contragent_id = 0;
        mt_srand();
        $model->hash = md5(mt_rand() . uniqid());
        if (false === $dummyObject) {
            if (!$model->save()) {
                if ($throwException) {
                    throw new Exception('Cannot create a new order.');
                } else {
                    return null;
                }
            }
        }
        return $model;
    }

    /**
     * Get current order.
     * @param bool $create Create order if it does not exist
     * @return Order
     * @throws Exception
     */
    public static function getOrder($create = false)
    {
        Yii::beginProfile("GetOrder");
        if (is_null(self::$order) && Yii::$app->session->has('orderId')) {
            self::$order = self::find()
                ->where(['id' => Yii::$app->session->get('orderId')])
                ->one();
        }
        if (is_null(self::$order) && !Yii::$app->user->isGuest) {
            self::$order = self::find()
                ->where(['user_id' => Yii::$app->user->id, 'in_cart' => 1])
                ->orderBy(['start_date' => SORT_DESC, 'id' => SORT_DESC])
                ->one();
        }
        if ((is_null(self::$order) || is_null(self::$order->stage) || self::$order->in_cart == 0)
            && $create === true
        ) {
            $model = self::create();
            self::$order = $model;
            Yii::$app->session->set('orderId', $model->id);

            $sessionOrders = Yii::$app->session->get('orders', []);
            $sessionOrders[] = $model->id;
            Yii::$app->session->set('orders', $sessionOrders);

        }
        Yii::endProfile("GetOrder");
        return self::$order;
    }

    public static function clearStaticOrder()
    {
        self::$order = null;
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
        foreach ($this->items as $item) {
            if (null === OrderItem::findOne(['id' => $item->id])) {
                $item->delete();
                continue;
            }
            if ($deleteNotActiveProducts && (null === $item->product || $item->product->active == 0)) {
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
        }

        $event = new OrderCalculateEvent();
        $event->order = $this;
        $event->price = PriceHelper::getOrderPrice($this, SpecialPriceList::TYPE_CORE);
        EventTriggeringHelper::triggerSpecialEvent($event);

        $this->items_count = $itemsCount;
        $this->total_price = PriceHelper::getOrderPrice($this);

        $event->state = OrderCalculateEvent::AFTER_CALCULATE;
        EventTriggeringHelper::triggerSpecialEvent($event);

        TagDependency::invalidate(Yii::$app->cache, ['Session:' . Yii::$app->session->id]);
        return $callSave ? $this->save(true, ['items_count', 'total_price', 'total_price_with_shipping']) : true;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $this->calculate();
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        TagDependency::invalidate(Yii::$app->cache, ['Session:' . Yii::$app->session->id]);
        parent::afterSave($insert, $changedAttributes);

        if (!$insert && !empty($changedAttributes['user_id']) && 0 === intval($changedAttributes['user_id'])) {
            if (!empty($this->customer)) {
                $customer = $this->customer;
                $customer->user_id = 0 === intval($customer->user_id) ? $this->user_id : $customer->user_id;
                $customer->save();
            }
        }
    }

    public function afterDelete()
    {
        self::deleteOrderElements($this);
        return parent::afterDelete();
    }

    public static function deleteOrderElements(Order $order)
    {
        foreach ($order->items as $item) {
            $item->delete();
        }
        if ($order->code !== null) {
            $order->code->delete();
        }
        SpecialPriceObject::deleteAllByObject($order);
    }

    /**
     * @param integer|null $checkWith
     * @return int
     */
    public function getImmutability($checkWith = null)
    {
        $stage = $this->stage;
        $checkWith = intval($checkWith);
        $flag = intval($stage->immutable_by_user)
            | (intval($stage->immutable_by_manager) << 1)
            | (intval($stage->immutable_by_assigned) << 2);
        return $checkWith > 0 ? $checkWith === ($checkWith & $flag) : $flag;
    }

    /**
     * @return int
     */
    public function getOrderState()
    {
        $stage = $this->stage;
        return 1 === ($stage->immutable_by_user & $stage->immutable_by_manager & $stage->immutable_by_assigned)
            ? Order::ORDER_STATE_FINISH
            : Order::ORDER_STATE_IN_PROCESS;
    }
}
