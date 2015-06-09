<?php

/** @var $remains \app\modules\shop\models\WarehouseProduct[] */
/** @var $model app\modules\shop\models\Product */
/** @var $this \yii\web\View */

use kartik\helpers\Html;
use yii\helpers\Url;

?>

<table class="table table-condensed table-striped table-hover">
    <thead>
        <tr>
            <th>
                <?= Yii::t('app', 'Warehouse') ?>
            </th>
            <th>
                <?= Yii::t('app', 'In warehouse') ?>
            </th>
            <th>
                <?= Yii::t('app', 'Reserved count') ?>
            </th>
            <th>
                <?= Yii::t('app', 'SKU') ?>
            </th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($remains as $warehouse_id => $remain): ?>
        <tr>
            <td>
                <?= $remain->warehouse->name ?>
            </td>
            <td>
                <?=
                Html::textInput(
                    'remain[' . $remain->id . '][in_warehouse]',
                    $remain->in_warehouse,
                    [
                        'class' => 'warehouse-remain-input form-control',
                        'placeholder' => Yii::t('app', 'In warehouse'),
                    ]
                ) ?>
            </td>
            <td>
                <?=
                Html::textInput(
                    'remain[' . $remain->id . '][reserved_count]',
                    $remain->reserved_count,
                    [
                        'class' => 'warehouse-remain-input form-control',
                        'placeholder' => Yii::t('app', 'Reserved count'),
                    ]
                ) ?>
            </td>
            <td>
                <?=
                Html::textInput(
                    'remain[' . $remain->id . '][sku]',
                    $remain->sku,
                    [
                        'class' => 'warehouse-remain-input form-control',
                        'placeholder' => Yii::t('app', 'SKU'),
                    ]
                ) ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php $this->beginBlock('warehousesWidget'); ?>
        $('.warehouse-remain-input').change(function(){
            var formData = {},
                $this = $(this);

            formData[$this.attr('name')] = $this.val();

            $.ajax({
                url: "<?= Url::toRoute(['/shop/backend-warehouse/update-remains']) ?>",
                data: formData,
                method: 'POST',
                success: function(data, textStatus, jqXHR) {
                    $this.parent().addClass('has-success');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $this.parent().addClass('has-error');
                }
            });
            return true;
        })
<?php $this->endBlock(); ?>
<?php $this->registerJs($this->blocks['warehousesWidget'], \yii\web\View::POS_READY); ?>
