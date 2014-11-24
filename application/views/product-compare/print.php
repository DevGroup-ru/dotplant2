<?php

/**
 * @var $error integer
 * @var $message string
 * @var $object \app\models\Object
 * @var $products \app\models\Product
 * @var $this \yii\web\View
 */

?>
<?php if (isset($error) && $error): ?>
    <?= $message ?>
<?php else: ?>
    <table style="margin: 25px auto;">
        <tr>
        <?php
        $counter = 1;
        foreach ($products as $product):
        ?>
            <?php
            $url = \yii\helpers\Url::to(
                [
                    'product/show',
                    'model' => $product,
                    'last_category_id' => $product->main_category_id,
                    'category_group_id' => $product->category->category_group_id,
                ]
            );
            $img = app\widgets\ImgSearch::widget(
                [
                    'objectId' => $object->id,
                    'objectModelId' => $product->id,
                    'limit' => 1,
                ]
            );
            ?>
            <td style="padding: 15px">
                <table>
                    <tr>
                        <td><b><?= $product->name ?></b></td>
                    </tr>
                    <tr>
                        <td><?= $img ?></td>
                    </tr>
                    <tr>
                        <td>
                            <?= Yii::t('shop', 'Price') ?>:
                            <span style="color:green;font-weight:bold; font-size:20px;"><?= Yii::$app->formatter->asDecimal($product->price, 2) ?>
                                <?= Yii::$app->params['currency'] ?>
                            </span>
                        <td>
                    </tr>
                    <tr>
                        <td>
                            <?=
                            \app\properties\PropertiesWidget::widget(
                                [
                                    'model' => $product,
                                    'form' => null,
                                    'viewFile' => 'show-properties-widget',
                                ]
                            );
                            ?>
                        </td>
                    </tr>
                </table>
            </td>
            <?php
            if ((($counter++ % 3) == 0)) {
                echo '</tr><tr>';
            }
            ?>
        <?php endforeach; ?>
        </tr>
    </table>
<?php endif; ?>
