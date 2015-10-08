<?php
/**
 * Use existent form
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\Contragent $model
 * @var \app\modules\shop\models\Customer $customer
 * @var boolean $immutable
 * @var string $action
 * @var \yii\bootstrap\ActiveForm $form
 * @var array $additional
 */
use yii\helpers\Html;
?>

<?php
    /** @var \app\modules\shop\models\Order $order */
    if (empty($customer)) {
        return '';
    }
?>
    <table class="table table-striped table-bordered">
        <tbody>
            <tr>
                <th><?= $model->getAttributeLabel('type'); ?></th>
                <td><?= $model->type; ?></td>
            </tr>
            <?php
                /** @var \app\properties\AbstractModel $abstractModel */
                $abstractModel = $model->getAbstractModel();
                $abstractModel->setArrayMode(false);
                $_tpl = '<tr><th>%s</th><td>%s</td></tr>' . PHP_EOL;
                $_html = '';
                foreach ($abstractModel->attributes() as $attr) {
                    $_html .= sprintf($_tpl, $abstractModel->getAttributeLabel($attr), $abstractModel->getPropertyValueByAttribute($attr));
                }
                echo $_html;
            ?>
            <tr><th colspan="2"><?= Yii::t('app', 'Delivery information'); ?></th></tr>
            <?php
                $deliveryInformation = !empty($model->deliveryInformation)
                    ? $model->deliveryInformation
                    : ($model->isNewRecord
                        ? \app\modules\shop\models\DeliveryInformation::createNewDeliveryInformation($model)
                        : \app\modules\shop\models\DeliveryInformation::createNewDeliveryInformation($model, false)
                    );
            ?>
            <tr>
                <th><?= $deliveryInformation->getAttributeLabel('country_id'); ?></th>
                <td><?= null !== $deliveryInformation->country ? Html::encode($deliveryInformation->country->name) : ''; ?></td>
            </tr>
            <tr>
                <th><?= $deliveryInformation->getAttributeLabel('city_id'); ?></th>
                <td><?= null !== $deliveryInformation->city ? Html::encode($deliveryInformation->city->name) : ''; ?></td>
            </tr>
            <tr>
                <th><?= $deliveryInformation->getAttributeLabel('zip_code'); ?></th>
                <td><?= Html::encode($deliveryInformation->zip_code); ?></td>
            </tr>
            <tr>
                <th><?= $deliveryInformation->getAttributeLabel('address'); ?></th>
                <td><?= Html::encode($deliveryInformation->address); ?></td>
            </tr>
        </tbody>
    </table>
