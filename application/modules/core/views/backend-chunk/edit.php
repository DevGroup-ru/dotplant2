<?php

use app\modules\core\models\ContentBlockGroup;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use kartik\icons\Icon;
use vova07\imperavi\Widget as ImperaviWidget;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 * @var \app\modules\core\models\ContentBlock $model
 * @var yii\widgets\ActiveForm $form
 */
$this->title = $model->isNewRecord ? Yii::t('app', 'Create Chunk') : Yii::t('app', 'Edit: content block');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Content Blocks'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="row">
    <div class="col-xs-12">
        <h1><?= Html::encode($this->title) ?></h1>
    </div>
</div>
<div class="row">
    <div class="col-xs-6">
        <div class="chunk-create">
            <?php $this->beginBlock('submit'); ?>
            <?= \app\backend\components\Helper::saveButtons($model) ?>
            <?php $this->endBlock(); ?>
            <div class="chunk-form">
                <?php $form = ActiveForm::begin(['id' => 'chunk-form']); ?>
                <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
                <?= $form->field($model, 'key')->textInput(['maxlength' => 255]) ?>
                <?= $form->field($model, 'value')->widget(
                    Yii::$app->getModule('core')->wysiwyg_class_name(),
                    Yii::$app->getModule('core')->wysiwyg_params()
                ); ?>
                <?= $form->field($model, 'group_id')->dropDownList(ArrayHelper::map(
                    ContentBlockGroup::find()->all()
                    , 'id', 'name'
                )) ?>

                <?= $form->field($model, 'newGroup'); ?>

                <?= $form->field($model, 'preload')->checkbox() ?>
                <?= $this->blocks['submit'] ?>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>