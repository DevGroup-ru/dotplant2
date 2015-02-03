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
                <td><?= Html::label(Yii::t('app', 'Main currency'), 'main_currency') ?></td>
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
                <td><?= Html::label(Yii::t('app', 'To show all properties of a product in YML'), 'show_all_properties') ?></td>
                <td><?= \kartik\widgets\SwitchInput::widget(['name' => $formName . '[show_all_properties]', 'value' => $show_all_properties]) ?></td>
            </tr>
            <tr>
                <td><?= Html::label(Yii::t('app', 'Description type by default'), 'default_offer_type') ?></td>
                <td><?= Html::dropDownList($formName . '[default_offer_type]', $default_offer_type,
                        [
                            'simplified' => Yii::t('app', 'The simplified description'),
                            'vendor.model' => Yii::t('app', 'Any goods'),
                            'book' => Yii::t('app', 'Books'),
                            'audiobook' => Yii::t('app', 'Audiobooks'),
                            'artist.title' => Yii::t('app', 'Musical and video production'),
                            'tour' => Yii::t('app', 'Tours'),
                            'event-ticket' => Yii::t('app', 'Tickets for event'),
                        ],
                        [ 'class' => 'form-control' ]
                    ) ?></td>
            </tr>
            <tr>
                <td><?= Html::label(Yii::t('app', 'Total cost of delivery for the region in which the shop is located'), 'local_delivery_cost') ?></td>
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