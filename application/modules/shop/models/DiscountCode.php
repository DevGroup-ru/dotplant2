<?php
namespace app\modules\shop\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "{{%discount_code}}".
 *
 * @property integer $id
 * @property string $code
 * @property integer $discount_id
 * @property string $valid_from
 * @property string $valid_till
 * @property integer $maximum_uses
 */
class DiscountCode extends AbstractDiscountType
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%discount_code}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'discount_id'], 'required'],
            [['code'], 'unique'],
            [['discount_id', 'maximum_uses'], 'integer'],
            [['valid_from', 'valid_till'], 'safe'],
            [['code'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'code' => Yii::t('app', 'Code'),
            'discount_id' => Yii::t('app', 'Discount ID'),
            'valid_from' => Yii::t('app', 'Valid From'),
            'valid_till' => Yii::t('app', 'Valid Till'),
            'maximum_uses' => Yii::t('app', 'Maximum Uses'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFullName()
    {
        return "{$this->code} [{$this->valid_from} - {$this->valid_till}] [maximum: {$this->maximum_uses}]";
    }

    /**
     * @inheritdoc
     */
    public function checkDiscount(Discount $discount, Product $product = null, Order $order = null)
    {
        if (null === $order) {
            return false;
        }

        $q = (new Query())
            ->from(OrderCode::tableName() . ' as oc')
            ->leftJoin(self::tableName() . ' as dc', 'dc.id = oc.discount_code_id')
            ->where(
                'oc.order_id = :ocoid AND dc.discount_id = :dcdid AND oc.status = 1',
                [
                    ':ocoid' => $order->id,
                    ':dcdid' => $discount->id
                ]
            )
            ->count();
        if (0 === intval($q)) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        OrderCode::updateAll(['status' => 0], ['discount_code_id' => $this->id]);
        return parent::beforeDelete();
    }
}
