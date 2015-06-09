<?php

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;
use yii\helpers\Url;
/** @var \app\extensions\DefaultTheme\models\ThemeWidgets $model */
/** @var $this \yii\web\View */
$this->title = Yii::t('app', 'Theme widget edit');
$this->params['breadcrumbs'][] = ['url' => [Url::toRoute('index')], 'label' => Yii::t('app', 'Default theme configuration')];
$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<?php $form = ActiveForm::begin(['id' => 'view-form', 'type'=>ActiveForm::TYPE_HORIZONTAL]); ?>

<?php $this->beginBlock('submit'); ?>

<?= \app\backend\components\Helper::saveButtons($model) ?>

<?php $this->endBlock(); ?>

    <section id="widget-grid">
        <div class="row">

            <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">

                <?php BackendWidget::begin(['title'=> Yii::t('app', 'Common'), 'icon'=>'pencil', 'footer'=>$this->blocks['submit']]); ?>

                <?= $form->field($model, 'name'); ?>
                <?= $form->field($model, 'widget'); ?>
                <?= $form->field($model, 'configuration_model'); ?>
                <?= $form->field($model, 'configuration_view'); ?>
                <?= $form->field($model, 'configuration_json')->widget(\devgroup\jsoneditor\Jsoneditor::className()) ?>



                <?php BackendWidget::end(); ?>

            </article>
            <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                <?php BackendWidget::begin(['title'=> Yii::t('app', 'Cache settings'), 'icon'=>'database', 'footer'=>$this->blocks['submit']]); ?>
                <?= $form->field($model, 'is_cacheable')->checkbox(); ?>
                <?= $form->field($model, 'cache_lifetime'); ?>
                <?= $form->field($model, 'cache_tags')->textarea(); ?>
                <?= $form->field($model, 'cache_vary_by_session')->checkbox(); ?>
                <?php BackendWidget::end(); ?>
                <?php BackendWidget::begin(['title'=> Yii::t('app', 'Applicable parts'), 'icon'=>'puzzle-piece', 'footer'=>$this->blocks['submit']]); ?>
                <fieldset>
                    <legend>
                        <?= Yii::t('app', 'Current applicable parts') ?>
                    </legend>
                    <table class="table table-condensed table-hover">
                    	<thead>
                    		<tr>
                    			<th>
                                    <?= Yii::t('app', 'Part') ?>
                                </th>
                                <th>
                                    <?= Yii::t('app', 'Actions') ?>
                                </th>
                    		</tr>
                    	</thead>
                    	<tbody>
                        <?php foreach ($model->applying as $widget_applying):?>
                    		<tr>
                    			<td>
                                    <?= Html::encode($widget_applying->part->name) ?>
                                </td>
                                <td>
                                    <?= Html::a(
                                        Icon::show('trash-o') . ' ' . Yii::t('app', 'Remove'),
                                        [
                                            'remove-applying',
                                            'part_id' => $widget_applying->part->id,
                                            'id' => $model->id,
                                        ],
                                        [
                                            'class' => 'btn btn-xs btn-danger'
                                        ]
                                    ) ?>
                                </td>
                    		</tr>
                        <?php endforeach;?>
                    	</tbody>
                    </table>
                </fieldset>
                <?=
                Html::dropDownList(
                    'new-part',
                    null,
                    [ 0 => Yii::t('app', 'Select theme part to add') ]
                        + \yii\helpers\ArrayHelper::map(\app\extensions\DefaultTheme\models\ThemeParts::getAllParts(), 'id', 'name'),
                    [
                        'class' => 'new-part-select',
                    ]
                ) ?>
                <?php BackendWidget::end() ?>
            </article>

        </div>
    </section>

<?php ActiveForm::end(); ?>
