<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\icons\Icon;

/**
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\Product[]|array $products
 */

    $properties = array_reduce($products,
        function ($result, $item)
        {
            /** @var \app\modules\shop\models\Product|\app\properties\HasProperties $item */
            /** @var \app\properties\AbstractModel $model */
            $model = $item->getAbstractModel();
            foreach ($model->attributes() as $attr) {
                $result[$attr] = $model->getAttributeLabel($attr);
            }
            return $result;
        },
    []);
    $blank = array_fill_keys(array_keys($properties), '');
    $compares = [];
?>

<div class="row">
    <div class="span9">
        <?=
        \yii\helpers\Html::a(
            Yii::t('app', 'Print version'),
            ['/shop/product-compare/print'],
            ['class' => 'btn pull-right']
        );
        ?>
    </div>
</div>

<div class="compare-products">
        <?php
            foreach ($products as $key => $item) {
                $column = $blank;
                /** @var \app\modules\shop\models\Product|\app\properties\HasProperties $item */
                /** @var \app\properties\AbstractModel $model */
                $model = $item->getAbstractModel();
                foreach ($model->attributes() as $attr) {
                    $column[$attr] = $model->getPropertyValueByAttribute($attr);
                }
                $compares[] = $column;
            }
        ?>
        <table class="table table-striped table-hover table-condensed table-bordered">
            <thead>
                <tr>
                    <th></th>
                <?= array_reduce($products,
                        function ($result, $item)
                        {
                            /** @var \app\modules\shop\models\Product $item */
                            $html = app\modules\image\widgets\ObjectImageWidget::widget([
                                'viewFile' => '@app/modules/shop/widgets/views/product-compare/img-tpl',
                                'model' => $item,
                                'thumbnailOnDemand' => true,
                                'thumbnailWidth' => 180,
                                'thumbnailHeight' => 180,
                                'limit' => 1,
                                'additional' => [
                                    'blank' => 'http://placehold.it/180x180?text=No+image',
                                ]
                            ]);
                            $html .= Html::a($item->name, Url::to([
                                    'product/show',
                                    'model' => $item,
                                    'last_category_id' => $item->main_category_id,
                                    'category_group_id' => $item->category->category_group_id,
                                ])
                            );
                            $html .= Html::tag('div', \Yii::t('app', 'Price') . ': ' . $item->nativeCurrencyPrice(false, false));
                            $html .= Html::tag('div',
                                Html::a(Yii::t('app', Icon::show('shopping-cart')), '#', [
                                    'data-action' => "add-to-cart",
                                    'data-id' => $item->id
                                ])
                                . Html::a(Yii::t('app', Icon::show('trash')), Url::toRoute([
                                    '/shop/product-compare/remove', 'id' => $item->id,
                                    'backUrl' => Yii::$app->request->url,
                                ])),
                                ['class' => 'controls']
                            );
                            $result .= Html::tag('th', $html) . PHP_EOL;
                            return $result;
                        },
                    '');
                ?>
                </tr>
            </thead>
            <tbody>
            <?php
                foreach ($properties as $key => $prop) {
                    $_flag = false;
                    $result = '<tr><th>'. $prop .'</th>';
                    $result .= array_reduce(array_column($compares, $key),
                        function ($result, $item) use (&$_flag)
                        {
                            if (!empty(strval($item))) {
                                $_flag = true;
                            }
                            $result .= '<td>'. $item .'</td>';
                            return $result;
                        },
                    '');
                    $result .= '</tr>' . PHP_EOL;
                    if ($_flag) {
                        echo $result;
                    }
                }
            ?>
            </tbody>
        </table>
</div>

<div class="row">
    <div class="span9">
        <?=
        \kartik\helpers\Html::a(
            Yii::t('app', 'Remove all'),
            [
                '/shop/product-compare/remove-all',
                'backUrl' => Yii::$app->request->url,
            ],
            [
                'class' => 'btn btn-danger',
            ]
        )
        ?>
    </div>
</div>

<style>
    .compare-products .table-striped > tbody > tr:nth-of-type(odd) {
        background-color: #f0f0f0;
    }
</style>