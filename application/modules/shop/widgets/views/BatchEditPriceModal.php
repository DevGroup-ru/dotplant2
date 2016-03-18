<?php
/**
 * @param $modalFormId string  - html id of modal form
 * @param $currencies [] - format: [currency_id => 'currency_iso_code']
 * @param $contextId string
 */

use yii\helpers\Html;

$headColumn = ' col-md-3';
$bodyColumn = ' col-md-9';
$modalConfirmId = $modalFormId . '-confirm';

// MAIN FORM
\yii\bootstrap\Modal::begin(
    [
        'id' => $modalFormId,
        'footer' => Html::button(
            Yii::t('app', 'Change prices'),
            [
                'class' => 'btn btn-success',
                'data-action' => 'edit-prices',
            ]
        )
            . Html::button(
                Yii::t('app', 'Cancel'),
                [
                    'class' => 'btn btn-danger',
                    'data-dismiss' => 'modal',
                ]
            ),
        'header' => Yii::t('app', 'Batch editing prices'),
    ]
);
?>

<?=
    Html::input(
        'hidden',
        'context',
        $contextId,
        ['id' => 'el_context', 'class' => 'form-control inline']
    )
?>
<div class="row" id="charge_kind">
    <?=
        Html::label(
            Yii::t('app', 'Charge kinds'),
            'el_charge_kind',
            ['class' => 'control-label' . $headColumn]
        )
    ?>
    <div class="<?= $bodyColumn ?>">
        <?=
            Html::dropDownList(
                'charge_kind',
                null,
                [
                    'fixed' => Yii::t('app', 'Fixed'),
                    'percentage' => Yii::t('app', 'Percentage'),
                ],
                [
                    'id' => 'el_charge_kind',
                    'class' => 'form-control' . $bodyColumn,
                ]
            )
        ?>
    </div>
</div>


<div class="row" id="type">
    <?=
        Html::label(
            Yii::t('app', 'Operation type'),
            'el_type',
            ['class' => 'control-label' . $headColumn]
        )
    ?>
    <div class="<?= $bodyColumn ?>">
        <?=
            Html::dropDownList(
                'type',
                null,
                [
                    'normal' => Yii::t('app', 'Normal change'),
                    'relative' => Yii::t('app', 'Relative change')
                ],
                [
                    'id' => 'el_type',
                    'class' => 'form-control' . $bodyColumn,
                ]
            )
        ?>
    </div>
</div>

<div class="row" id="operation">
    <?=
        Html::label(
            Yii::t('app', 'Operation'),
            'el_operation',
            ['class' => 'control-label' . $headColumn]
        )
    ?>
    <div class="<?= $bodyColumn ?>">
        <?=
            Html::dropDownList(
                'operation',
                null,
                [
                    'inc' => Yii::t('app', 'Increase'),
                    'dec' => Yii::t('app', 'Decrease')
                ],
                [
                    'id' => 'el_operation',
                    'class' => 'form-control' . $bodyColumn
                ]
            )
        ?>
    </div>
</div>
<div class="row" id="apply_for">
    <span class="normal">
        <?=
            Html::label(
                Yii::t('app', 'Apply to field'),
                'el_apply_for',
                ['class' => 'control-label' . $headColumn]
            )
        ?>
    </span>
    <span class="relative">
        <?=
            Html::label(
                Yii::t('app', 'Basic field is'),
                'el_apply_for',
                ['class' => 'control-label' . $headColumn]
            )
        ?>
    </span>
    <div class="<?= $bodyColumn ?>">
        <?=
            Html::dropDownList(
                'apply_for',
                null,
                [
                    'price' => Yii::t('app', 'Price'),
                    'old_price' => Yii::t('app', 'Old Price'),
                    'all' => Yii::t('app', 'To both fields')
                ],
                [
                    'id' => 'el_apply_for',
                    'class' => 'form-control' . $bodyColumn
                ]
            )
        ?>
    </div>
</div>

<div class="row" id="value">
    <?=
        Html::label(
            Yii::t('app', 'Charge value'),
            'el_value',
            ['class' => 'control-label' . $headColumn]
        )
    ?>
    <div class="<?= $bodyColumn ?>" id="vcont">
        <?=
            Html::input(
                'text',
                'value',
                '0.0',
                [
                    'id' => 'el_value',
                    'class' => 'form-control inline'
                ]
            )
        ?>
        <span class="percent_text"><?= Yii::t('app', '% for price in') ?></span>
        <?=
            Html::dropDownList(
                'currency',
                null,
                $currencies,
                [
                    'class' => 'form-control inline',
                    'id' => 'el_currency'
                ]
            )
        ?>
    </div>
</div>

<div class="row" id="round">
    <?=
        Html::label(
            Yii::t('app', 'Rounding'),
            'el_round',
            ['class' => 'control-label' . $headColumn]
        )
    ?>
    <div class="<?= $bodyColumn ?>">
        <?=
            Html::checkbox(
                'round',
                true,
                [
                    'id' => 'el_round',
                    'class' => 'form-control inline'
                ]
            )
        ?>
        <span class="round_options inline">
            <?=
                Html::input(
                    'text',
                    'round',
                    '2',
                    [
                        'id' => 'el_round_val',
                        'class' => 'form-control inline'
                    ]
                )
            ?>
            <?= Yii::t('app', 'Decimal symbols') ?>
        </span>
    </div>
</div>

<?php if ($contextId == 'backend-category') : ?>

    <div class="row" id="child">
        <?=
            Html::label(
                Yii::t('app', 'Apply to child categories'),
                'el_child',
                ['class' => 'control-label' . $headColumn]
            )
        ?>
        <div class="<?= $bodyColumn ?>">
            <?=
                Html::checkbox(
                    'child',
                    false,
                    [
                        'id' => 'el_child',
                        'class' => 'form-control inline'
                    ]
                )
            ?>
        </div>
    </div>

<?php endif; ?>

<div class="hidden" id="to_hidden"></div>

<?php \yii\bootstrap\Modal::end() ?>



<?php
// CONFIRM FORM
\yii\bootstrap\Modal::begin(
    [
        'id' => $modalConfirmId,
        'footer' => Html::button(
            Yii::t('app', 'Yes'),
            [
                'class' => 'btn btn-success',
                'data-action' => 'edit-prices-confirm',
                'id' => 'main_actions'
            ]
        )
        . Html::button(
            Yii::t('app', 'Cancel'),
            [
                'class' => 'btn btn-danger',
                'data-dismiss' => 'edit-prices-cancel',
                'id' => 'main_actions'
            ]
        )
        . Html::button(
            Yii::t('app', 'Ok'),
            [
                'class' => 'btn btn-default',
                'data-dismiss' => 'modal',
                'id' => 'close_rep'
            ]
        ),
        'header' => Yii::t('app', 'Confirm edit item'),
    ]
);
?>
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-info"></div>
        <div class="alert alert-success"></div>
        <div class="alert alert-danger"></div>
    </div>
</div>
<?php \yii\bootstrap\Modal::end() ?>
