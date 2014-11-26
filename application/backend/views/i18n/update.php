<?php

/**
 * @var $alias string
 * @var $file string
 * @var $messages string
 * @var $this \yii\web\View
 */
use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;

$this->title = Yii::t('app', 'Update messages "{alias}"', ['alias' => $alias]);
$this->params['breadcrumbs'] = [
    [
        'label' => Yii::t('app', 'I18n'),
        'url' => '/backend/i18n',
    ],
    $this->title,
];

?>
<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
    <?= Html::beginForm() ?>
        <?php
            BackendWidget::begin(
                [
                    'icon' => 'language',
                    'title'=> $this->title,
                    'footer' => Html::submitButton(
                        Icon::show('save') . Yii::t('app', 'Save'),
                        ['class' => 'btn btn-primary']
                    ),
                ]
            );
        ?>
            <?=
                \devgroup\jsoneditor\Jsoneditor::widget(
                    [
                        'editorOptions' => [
                            'modes' => ['tree'],
                        ],
                        'name' => 'messages',
                        'options' => [
                            'style' => 'height: 600px',
                        ],
                        'value' => $messages,
                    ]
                )
            ?>
            <div>
                <?= Html::checkbox('ksort', Yii::$app->request->cookies->getValue('sortMessages'), ['id' => 'ksort']) ?>
                <?= Html::label(Yii::t('app', 'Sort by source messages'), 'ksort') ?>
            </div>
        <?php BackendWidget::end(); ?>
    <?= Html::endForm() ?>
</div>
