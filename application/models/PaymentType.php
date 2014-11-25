<?php

namespace app\models;


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
            'id' => Yii::t('shop', 'ID'),
            'name' => Yii::t('shop', 'Name'),
            'class' => Yii::t('shop', 'Class'),
            'params' => Yii::t('shop', 'Params'),
            'logo' => Yii::t('shop', 'Logo'),
            'commission' => Yii::t('shop', 'Commission'),
            'active' => Yii::t('shop', 'Active'),
            'payment_available' => Yii::t('shop', 'Payment Available'),
            'sort' => Yii::t('shop', 'Sort'),
        ];
    }

    public function getPayment()
    {
        if (is_null($this->payment)) {
            $className = $this->class;
            if (!class_exists($className)) {
                throw new UnknownClassException;
            }
            try {
                $params = Json::decode($this->params);
            } catch (Exception $e) {
                $params = [];
            }
            $this->payment = new $className($params);
        }
        return $this->payment;
    }
}
