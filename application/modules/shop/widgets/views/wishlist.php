<?php

/**
 * @var $id integer
 * @var $wishlists array
 * @var $model Wishlist
 */

use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;
use app\modules\shop\models\Wishlist;
use yii\helpers\Html;
use kartik\icons\Icon;

Modal::begin([
    'header' => '<h4>' . Yii::t('app', 'Adding to Wish List') . '</h4>',
    'size' => Modal::SIZE_SMALL,
    'id' => 'wishlist',
    'options' => [
        'style' => [
            'text-align' => 'left',
        ],
    ],
    'headerOptions' => [
        'style' => [
            'text-align' => 'center',
        ],
    ],
]);

$form = ActiveForm::begin([
    'id' => 'wishlist-form',
    'action' => null,
]);
foreach ($wishlists as $wishlist) {
    /** @var Wishlist $wishlist */
    echo Html::tag('div',
        Html::label(Html::radio('wishlistId', ($wishlist->default) ? true : false, ['value' => $wishlist->id]) . Html::encode($wishlist->title) . '<span>(' . count($wishlist->items) . ')</span>'),
        [
            'class' => 'form-group',
        ]
    );
}
echo Html::tag('div',
    Html::label(Html::radio('wishlistId', false, ['value' => 0]) . $form->field($model, 'title', [
            'inputOptions' => [
                'placeholder' => Yii::t('app', 'Enter title'),
                'name' => 'title',
            ],
            'options' => [
                'style' => [
                    'float' => 'right',
                ]
            ]
        ])->label('')),
    [
        'class' => 'form-group required',
    ]
);

echo Html::button(Icon::show('check') . Yii::t('app', 'Save'), [
    'class' => 'btn btn-success',
    'data-action' => 'add-to-wishlist',
    'data-id' => $id,
    'style' => [
        'margin' => '0 auto',
        'display' => 'block',
    ],
]);
ActiveForm::end();
Modal::end();

$js = <<<JS
    $('form#wishlist-form [type=text]').on('focus', function(){
        $(this).parent().siblings('[type=radio]').prop("checked", true);
    });
JS;
$this->registerJs($js);
