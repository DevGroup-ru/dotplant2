<?php

namespace app\models;

use app\modules\user\models\User;
use app\properties\HasProperties;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\Json;

/**
 * This is the model class for table "order".
 * @property integer $id
 * @property integer $user_id
 * @property integer $manager_id
 * @property string $start_date
 * @property string $end_date
 * @property integer $order_status_id
 * @property integer $shipping_option_id
 * @property integer $payment_type_id
 * @property string $external_id
 * @property integer $items_count
 * @property double $total_price
 * @property string $hash
 * @property \app\properties\AbstractModel $abstractModel
 * @property OrderStatus $status
 * @property OrderItem[] $items
 * @property ShippingOption $shippingOption
 * @property PaymentType $paymentType
 * @property OrderTransaction[] $transactions
 * @property User $user
 * @property User $manager
 * @property float $fullPrice
 */
class Order extends \yii\db\ActiveRecord
{
    const STATUS_DONE = 6;
    const STATUS_CANCEL = 7;

    public function behaviors()
    {
        return [
            [
                'class' => HasProperties::className(),
            ],
            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
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
                    'order_status_id',
                    'total_price',
                    'shipping_option_id',
                    'payment_type_id',
                ],
                'required'
            ],
            [['user_id', 'order_status_id', 'shipping_option_id', 'payment_type_id'], 'integer'],
            [['start_date', 'end_date'], 'safe'],
            [['total_price', 'items_count'], 'number'],
            [['external_id'], 'string', 'max' => 38]
        ];
    }

    public function scenarios()
    {
        return [
            'default' => [
                'user_id',
                'cart_forming_time',
                'order_status_id',
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
                'order_status_id',
                'shipping_option_id',
                'payment_type_id',
                'items_count',
                'total_price',
                'hash',
            ],
            'shippingOption' => ['shipping_option_id', 'order_status_id'],
            'paymentType' => ['payment_type_id', 'order_status_id'],
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
            'end_date' => Yii::t('app', 'End Date'),
            'cart_forming_time' => Yii::t('app', 'Cart Forming Time'),
            'order_status_id' => Yii::t('app', 'Order Status'),
            'shipping_option_id' => Yii::t('app', 'Shipping Option'),
            'payment_type_id' => Yii::t('app', 'Payment Type'),
            'external_id' => Yii::t('app', 'External ID'),
            'items_count' => Yii::t('app', 'Items Count'),
            'total_price' => Yii::t('app', 'Total Price'),
            'hash' => Yii::t('app', 'Hash'),
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        if (isset($changedAttributes['order_status_id']) && !is_null(
                $changedAttributes['order_status_id']
            ) && $this->order_status_id == 3
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

    public function getStatus()
    {
        return $this->hasOne(OrderStatus::className(), ['id' => 'order_status_id']);
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

        if (intval(Config::getValue('shop.AbilityDeleteOrders')) !== 1 ) {
            return false;
        }

        $result = parent::beforeDelete();
        if (0 === intval($this->is_deleted)) {
            $this->is_deleted = 1;
            $this->save();

            return false;
        }
        return $result;
    }
    /**
     * Отмена удаления объекта
     *
     * @return bool Restore result
     */
    public function restoreFromTrash()
    {
        $this->is_deleted = 0;
        return $this->save();
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
}
