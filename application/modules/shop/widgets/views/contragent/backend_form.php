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
    $order = $additional['order'];

    if (empty($order) || empty($customer)) {
        return '';
    }

    $contragents = array_reduce($customer->contragents,
        function ($result, $item) use ($customer)
        {
            /** @var \app\modules\shop\models\Contragent $item */
            $result[$item->id] = $item;
            return $result;
        }, [0 => \app\modules\shop\models\Contragent::createEmptyContragent($customer)]
    );

    echo $form->field($order, 'contragent_id')->dropDownList(array_reduce($contragents,
            function ($result, $item)
            {
                /** @var \app\modules\shop\models\Contragent $item */
                if ($item->isNewRecord) {
                    $result[0] = Yii::t('app', 'New payer');
                } else {
                    $result[$item->id] = $item->type;
                }
                return $result;
            }, [])
        , ['class' => 'contragents', 'readonly' => $immutable]);
?>
    <hr />
    <div class="contragents_list">
        <?php
        foreach ($contragents as $key => $contragent) {
            if ($immutable) {
                $contragent->setScenario('readonly');
                $contragent->getAbstractModel()->setScenario('readonly');
            }

            /** @var \app\modules\shop\models\Contragent $contragent */
            $_content = $form->field($contragent, 'type')
                ->dropDownList(['Individual' => 'Individual', 'Self-employed' => 'Self-employed', 'Legal entity' => 'Legal entity'],
                    ['readonly' => $immutable]
                );
            /** @var \app\properties\AbstractModel $abstractModel */
            $abstractModel = $contragent->getAbstractModel();
            $abstractModel->setArrayMode(false);
            foreach ($abstractModel->attributes() as $attr) {
                $_content .= $form->field($abstractModel, $attr)->textInput(['readonly' => $immutable]);
            }

            $_content .= Html::tag('h5', Yii::t('app', 'Delivery information'));
            $deliveryInformation = !empty($contragent->deliveryInformation)
                ? $contragent->deliveryInformation
                : ($contragent->isNewRecord
                    ? \app\modules\shop\models\DeliveryInformation::createNewDeliveryInformation($contragent)
                    : \app\modules\shop\models\DeliveryInformation::createNewDeliveryInformation($contragent, false)
                );
            $_content .= $form->field($deliveryInformation, 'country_id')
                ->dropDownList(\app\components\Helper::getModelMap(\app\models\Country::className(), 'id', 'name'),
                    ['readonly' => $immutable]
                );
            $_content .= $form->field($deliveryInformation, 'city_id')
                ->dropDownList(\app\components\Helper::getModelMap(\app\models\City::className(), 'id', 'name'),
                    ['readonly' => $immutable]
                );
            $_content .= $form->field($deliveryInformation, 'zip_code')->textInput(['readonly' => $immutable]);
            $_content .= $form->field($deliveryInformation, 'address')->textInput(['readonly' => $immutable]);

            echo Html::tag('div', $_content, [
                'class' => "contragent contragent_$key" . ($key === intval($order->contragent_id) ? '' : ' hide')
            ]);
        }
        ?>
    </div>
