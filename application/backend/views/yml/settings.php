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
                            'simplified' => Yii::t('app', 'Упрощенное описание'),
                            'vendor.model' => Yii::t('app', 'Произвольный товар'),
                            'book' => Yii::t('app', 'Книги'),
                            'audiobook' => Yii::t('app', 'Аудиокниги'),
                            'artist.title' => Yii::t('app', 'Музыкальная и видео продукция'),
                            'tour' => Yii::t('app', 'Туры'),
                            'event-ticket' => Yii::t('app', 'Билеты на мероприятие'),
                        ],
                        [ 'class' => 'form-control' ]
                    ) ?></td>
            </tr>
            <tr>
                <td><?= Html::label(Yii::t('app', 'Общая стоимость доставки для региона, в котором расположен магазин'), 'local_delivery_cost') ?></td>
                <td><?= Html::input('text', 'yml[local_delivery_cost]', $local_delivery_cost, [ 'class' => 'form-control' ]) ?></td>
            </tr>
        </table>

        <?=
        Html::submitButton(
            Icon::show('save') . Yii::t('app', 'Save'),
            ['class' => 'btn btn-primary']
        )
        ?>

        <?=
        Html::button(
            Icon::show('code') . Yii::t('app', 'Create YML'),
            ['class' => 'btn btn-primary', 'id' => 'create_yml']
        )
        ?>

        <?= Html::endForm() ?>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div id="yml_link" style="font-weight: bold; font-size: 16px; padding: 15px; margin: 3px;"><label><?= Yii::t('app', 'It will be available according to the link:')?> <b>/yml/get</b></label></div>
    </div>
</div>

<script language="JavaScript">
    $(document).ready(function(){
        $('#create_yml').click(function(){
            $.ajax({
                url: "/yml/get?regenerate=yes",
                success: function(data, textStatus, jqXHR){
                    console.log("Received: " + textStatus);
                    if (data !== "busy" && data !== "file not exist") {
                        $('#yml_link').css({"color": "green"});
                        $('#yml_link').html(textStatus);
                    } else {
                        $('#yml_link').css({"color": "green"});
                        $('#yml_link').html(data);
                    }
                }
            });
        });
    });
</script>