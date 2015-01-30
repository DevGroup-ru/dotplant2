<?php

use \yii\helpers\Html;
use \kartik\icons\Icon;

$this->title = Yii::t('app', 'Settings');
$this->params['breadcrumbs'][] = [
    'label' => 'YML',
    'url' => ['index']
];
$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>


<div class="row">
    <div class="col-md-6">
        <?php $formName = "yml"; ?>

        <?= Html::beginForm('', 'post',
            [
                'id' => 'form-yml',
                'name' => $formName
            ]
        ); ?>

        <table class="table">
            <tr>
                <td><?= Html::label(Yii::t('app', 'Основная валюта'), 'main_currency') ?></td>
                <td><?= Html::dropDownList($formName .'[main_currency]', $main_currency,
                        [
                            'RUR' => 'RUR',
                            'USD' => 'USD',
                            'EUR' => 'EUR',
                            'UAH' => 'UAH',
                            'KZT' => 'KZT',
                        ],
                        [ 'class' => 'form-control' ]
                    ) ?></td>
            </tr>
            <tr>
                <td><?= Html::label(Yii::t('app', 'Выводить все свойства продукта'), 'show_all_properties') ?></td>
                <td><?= \kartik\widgets\SwitchInput::widget(['name' => $formName . '[show_all_properties]', 'value' => $show_all_properties]) ?></td>
            </tr>
            <tr>
                <td><?= Html::label(Yii::t('app', 'Тип описания по умолчанию'), 'default_offer_type') ?></td>
                <td><?= Html::dropDownList($formName . '[default_offer_type]', $default_offer_type,
                        [
                            'simplified' => 'Упрощенное описание',
                            'vendor.model' => 'Произвольный товар',
                            'book' => 'Книги',
                            'audiobook' => 'Аудиокниги',
                            'artist.title' => 'Музыкальная и видео продукция',
                            'tour' => 'Туры',
                            'event-ticket' => 'Билеты на мероприятие',
                        ],
                        [ 'class' => 'form-control' ]
                    ) ?></td>
            </tr>
        </table>

        <?= Html::submitButton(
            Icon::show('save') . Yii::t('app', 'Save'),
            ['class' => 'btn btn-primary']
        ) ?>

        <?= Html::endForm() ?>
    </div>
</div>