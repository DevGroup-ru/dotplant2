<?php

/**
 * @var yii\web\View $this
 */

use app\backend\widgets\BackendWidget;
use kartik\dynagrid\DynaGrid;
use kartik\helpers\Html;
use kartik\icons\Icon;
$this->title = Yii::t('app', 'Editing rating group {groupname}', [
    'groupname' => $group['rating_group'],
]);
$this->params['breadcrumbs'][] = ['url' => [\yii\helpers\Url::toRoute(['index'])], 'label' => Yii::t('app', 'Rating groups')];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php $this->beginBlock('add-button'); ?>
<?=
\yii\helpers\Html::a(
    \kartik\icons\Icon::show('plus') . Yii::t('app', 'Add'),
    ['item-edit', 'group' => $group['rating_group'], 'returnUrl' => \app\backend\components\Helper::getReturnUrl()],
    ['class' => 'btn btn-success']
)
?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('submit'); ?>
<div class="form-group no-margin">
    <?=
    Html::a(
        Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
        Yii::$app->request->get('returnUrl', ['index']),
        ['class' => 'btn btn-danger']
    )
    ?>
    <?=
    Html::submitButton(
        Icon::show('save') . Yii::t('app', 'Save'),
        [
            'class' => 'btn btn-primary',
            'name' => 'action',
            'value' => 'save',
        ]
    )
    ?>
</div>
<?php $this->endBlock('submit'); ?>

<?= Html::beginForm(Yii::$app->request->url, 'post', ['class' => 'form-horizontal']); ?>
<section id="widget-grid">
    <div class="row">
        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
            <?php BackendWidget::begin(['title' => Yii::t('app', 'Common'), 'icon' => 'pencil', 'footer' => $this->blocks['submit']]); ?>
            <div class="form-group">
                <?= Html::label(Yii::t('app', 'Group name'), 'group-name', ['class' => 'col-md-2 control-label']); ?>
                <div class="col-md-10"><?= Html::input('text', 'group-name', $group['rating_group'], ['class' => 'form-control']); ?></div>
            </div>
            <div class="form-group">
                <?= Html::label(Yii::t('app', 'Require review'), 'group-require-review', ['class' => 'col-md-2 control-label']); ?>
                <div class="col-md-10"><?= Html::checkbox('group-require-review', $group['require_review'], ['class' => '']); ?></div>
            </div>
            <div class="form-group">
                <?= Html::label(Yii::t('app', 'Allow guest user to rate'), 'group-allow-guest', ['class' => 'col-md-2 control-label']); ?>
                <div class="col-md-10"><?= Html::checkbox('group-allow-guest', $group['allow_guest'], ['class' => '']); ?></div>
            </div>
            <?php BackendWidget::end(); ?>
        </article>
    </div>
</section>
<?= Html::endForm(); ?>

<div class="rating-group-edit">
    <?=
    DynaGrid::widget(
        [
            'options' => [
                'id' => 'rating-group-grid',
            ],
            'theme' => 'panel-default',
            'gridOptions' => [
                'dataProvider' => $data_provider,
                'hover' => true,
                'panel' => [
                    'heading' => Html::tag('h3', $this->title, ['class' => 'panel-title']),
                    'after' => $this->blocks['add-button'],
                ],
            ],
            'columns' => [
                'name',
                'min_value',
                'max_value',
                'step_value',
                [
                    'class' => 'app\backend\components\ActionColumn',
                    'buttons' => function($model, $key, $index, $parent) {
                        return [
                            [
                                'url' => 'item-edit',
                                'icon' => 'pencil',
                                'class' => 'btn-primary',
                                'label' => Yii::t('app', 'Edit'),
                            ],
                            [
                                'url' => 'item-delete',
                                'icon' => 'trash-o',
                                'class' => 'btn-danger',
                                'label' => Yii::t('app', 'Delete'),
                                'options' => [
                                    'data-action' => 'delete',
                                ],
                            ],
                        ];
                    },
                ],
            ],
        ]
    );
    ?>
</div>