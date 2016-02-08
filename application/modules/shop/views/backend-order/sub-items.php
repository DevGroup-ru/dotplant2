<?php
/**
 * @var \app\modules\shop\models\OrderItem[] $allItems
 * @var \app\modules\shop\models\OrderItem[] $items
 * @var \yii\web\View $this
 */

use kartik\editable\Editable;
use kartik\helpers\Html;

$level = isset($level) ? $level : 1;

?>
<?php foreach ($items as $item): ?>
    <tr class="warning">
        <td><?= str_repeat('<i class="fa fa-level-up fa-rotate-90"></i>&nbsp;', $level)?><?=$item->product->name?></td>
        <td><?=$item->price_per_pcs?></td>
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
        <td><?=$item->total_price?></td>
        <td><?=Html::a(
                \kartik\icons\Icon::show('remove'),
                ['delete-order-item', 'id' => $item->id],
                ['class' => 'btn btn-primary btn-xs do-not-print']
            )?></td>
    </tr>
    <?php if (isset($allItems[$item->id])): ?>
        <?=
        $this->render(
            'sub-items',
            ['allItems' => $allItems, 'items' => $allItems[$item->id], 'level' => $level + 1]
        )
        ?>
    <?php endif; ?>
<?php endforeach; ?>
