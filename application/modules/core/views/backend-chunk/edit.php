<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use kartik\icons\Icon;
use vova07\imperavi\Widget as ImperaviWidget;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 * @var app\modules\seo\models\Counter $model
 * @var yii\widgets\ActiveForm $form
 */
$this->title = Yii::t('app', 'Create Chunk');
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
                <?=$form->field($model, 'value')->widget(
                    ImperaviWidget::className(),
                    [
                        'settings' => [
                            'replaceDivs' => false,
                            'minHeight' => 200,
                            'paragraphize' => false,
                            'pastePlainText' => true,
                            'buttonSource' => true,
                            'imageManagerJson' => Url::to(['/backend/dashboard/imperavi-images-get']),
                            'plugins' => [
                                'table',
                                'fontsize',
                                'fontfamily',
                                'fontcolor',
                                'video',
                                'imagemanager',
                            ],
                            'replaceStyles' => [],
                            'replaceTags' => [],
                            'deniedTags' => [],
                            'removeEmpty' => [],
                            'imageUpload' => Url::to(['/backend/dashboard/imperavi-image-upload']),
                        ],
                    ]
                );?>
                <?= $form->field($model, 'preload')->checkbox() ?>
                <?= $this->blocks['submit'] ?>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>