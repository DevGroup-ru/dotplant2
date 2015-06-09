<?php
/**
 * @var \app\modules\shop\models\OrderItem[] $allItems
 * @var \app\modules\shop\models\OrderItem[] $items
 * @var \yii\web\View $this
 */

use kartik\editable\Editable;
use kartik\helpers\Html;

?>
<?php foreach ($items as $item): ?>
    <?php if (empty($item->product)) continue; ?>
    <tr>
        <td><?=$item->product->name?></td>
        <td><?=$item->product->convertedPrice()?></td>
        <td>
            <?=
            Editable::widget(
                [
                    'attribute' => 'quantity',
                    'options' => [
                        'id' => 'edit-quantity' . $item->id,
                    ],
                    'formOptions' => [
                        'action' => [
                            'change-order-item-quantity',
                            'id' => $item->id,
                        ],
                    ],
                    'inputType' => Editable::INPUT_TEXT,
                    'model' => $item,
                ]
            )
            ?>
        </td>
        <td><?=Yii::$app->formatter->asDecimal(
                $item->quantity * $item->product->convertedPrice(),
                2
            )?></td>
        <td><?=Html::a(
                \kartik\icons\Icon::show('remove'),
                ['delete-order-item', 'id' => $item->id],
                ['class' => 'btn btn-primary btn-xs do-not-print']
            )?></td>
    </tr>
    <?php if (isset($allItems[$item->id])): ?>
        <?= $this->render('sub-items', ['allItems' => $allItems, 'items' => $allItems[$item->id]]) ?>
    <?php endif; ?>
<?php endforeach; ?>
