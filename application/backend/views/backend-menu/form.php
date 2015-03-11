<?php

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;
use yii\helpers\Url;

    /**
     * @var $this \yii\web\View
     * @var $model \app\backend\models\BackendMenu
     */
    $this->title = Yii::t('app', 'Backend menu item edit');

    $this->params['breadcrumbs'][] = [
        'url' => ['/backend/backend-menu/index'],
        'label' => Yii::t('app', 'Backend menu items')
    ];
    if (
        ($model->parent_id > 0)
        && (null !== $parent = \app\backend\models\BackendMenu::findById($model->parent_id))
    ) {
        $this->params['breadcrumbs'][] = [
            'url' => [
                '/backend/backend-menu/index',
                'id' => $parent->id,
                'parent_id' => $parent->parent_id
            ],
            'label' => $parent->name
        ];
    }
    $this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<?php $this->beginBlock('submit'); ?>
<div class="form-group no-margin">
    <?=
    Html::a(
        Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
        Yii::$app->request->get('returnUrl', ['/backend/backend-menu/index']),
        ['class' => 'btn btn-danger']
    )
    ?>
    <?php if ($model->isNewRecord): ?>
        <?=
        Html::submitButton(
            Icon::show('save') . Yii::t('app', 'Save & Go next'),
            [
                'class' => 'btn btn-success',
                'name' => 'action',
                'value' => 'next',
            ]
        )
        ?>
    <?php endif; ?>
    <?= Html::submitButton(
        Icon::show('save') . Yii::t('app', 'Save & Go back'),
        [
            'class' => 'btn btn-warning',
            'name' => 'action',
            'value' => 'back',
        ]
    ); ?>
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

<?php $form = ActiveForm::begin(['id' => 'backend-menu-form', 'type'=>ActiveForm::TYPE_HORIZONTAL]); ?>

<section id="widget-grid">
    <div class="row">

        <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">

            <?php BackendWidget::begin(
                [
                    'title' => Yii::t('app', 'Backend menu item'),
                    'icon' =>'tree',
                    'footer' => $this->blocks['submit']
                ]
            ); ?>

                <?= $form->field($model, 'name') ?>

                <?= $form->field($model, 'route') ?>                

                <?= $form->field($model, 'icon') ?>

                <?= $form->field($model, 'translation_category') ?>

                <?= $form->field($model, 'added_by_ext') ?>

                <?= $form->field($model, 'css_class') ?>

                <?= $form->field($model, 'rbac_check') ?>
                
                <?= $form->field($model, 'sort_order') ?>

            <?php BackendWidget::end(); ?>

        </article>

    </div>
</section>

<?php ActiveForm::end(); ?>
