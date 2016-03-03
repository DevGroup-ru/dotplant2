<?php
/**
 * @var \app\modules\shop\models\OrderItem[] $allItems
 * @var \app\modules\shop\models\OrderItem[] $items
 * @var \yii\web\View $this
 */

use kartik\editable\Editable;
use kartik\helpers\Html;
use app\modules\shop\components\ProductEntity;
use app\modules\shop\models\Product;

?>
<?php foreach ($items as $item): ?>
    <?php
        if (false === $item->entity instanceof ProductEntity) {
            continue ;
        }
        /** @var Product $product */
        $product = $item->entity->model;
        $_url = null === $product
            ? null
            : \yii\helpers\Url::toRoute(['/shop/backend-product/edit', 'id' => $product->id]);
    ?>
    <tr>
        <td>
        <?php if (null === $_url): ?>
            <?=$item->entity->getName()?>
        <?php else: ?>
            <a href="<?= $_url; ?>" target="_blank"><?=$item->entity->getName()?></a>
        <?php endif; ?>
        </td>
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
        <?= $this->render('sub-items', ['allItems' => $allItems, 'items' => $allItems[$item->id]]) ?>
    <?php endif; ?>
<?php endforeach; ?>
