<?php

namespace app\modules\shop\models;


use app\components\payment\AbstractPayment;
use Yii;
use yii\base\Exception;
use yii\base\UnknownClassException;
use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * This is the model class for table "payment_type".
 *
 * @property integer $id
 * @property string $name
 * @property string $class
 * @property string $params
 * @property string $logo
 * @property double $commission
 * @property integer $active
 * @property integer $payment_available
 * @property integer $sort
 * Relations:
 * @property AbstractPayment $payment
 */
class PaymentType extends ActiveRecord
{
    private $payment;

    public function behaviors()
    {
        return [
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
        return '{{%payment_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'class', 'params'], 'required'],
            [['params'], 'string'],
            [['commission'], 'number'],
            [['active', 'payment_available', 'sort'], 'integer'],
            [['name', 'class', 'logo'], 'string', 'max' => 255]
        ];
    }

    public function scenarios()
    {
        return [
            'default' => ['name', 'class', 'params', 'logo', 'active', 'payment_available', 'commission', 'sort'],
            'search' => ['id', 'name', 'class', 'active', 'payment_available', 'commission', 'sort'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'class' => Yii::t('app', 'Class'),
            'params' => Yii::t('app', 'Params'),
            'logo' => Yii::t('app', 'Logo'),
            'commission' => Yii::t('app', 'Commission'),
            'active' => Yii::t('app', 'Active'),
            'payment_available' => Yii::t('app', 'Payment Available'),
            'sort' => Yii::t('app', 'Sort'),
        ];
    }

    /**
     * @return AbstractPayment
     * @throws UnknownClassException
     */
    public function getPayment(Order $order = null, OrderTransaction $transaction = null)
    {
        if (null !== $this->payment) {
            return $this->payment;
        }

        $className = $this->class;
        if (!class_exists($className)) {
            throw new UnknownClassException();
        }
        try {
            $params = Json::decode($this->params);
        } catch (Exception $e) {
            $params = [];
        }
        $params['order'] = $order;
        $params['transaction'] = $transaction;
        return $this->payment = new $className($params);
    }

    /**
     * @param int $isActive
     * @return PaymentType[]|array
     */
    public static function getPaymentTypes($isActive = 1)
    {
        $models = static::find()->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC]);
        if (null !== $isActive) {
            $models->andWhere(['active' => $isActive]);
        }

        return $models->all();
    }
}
?>