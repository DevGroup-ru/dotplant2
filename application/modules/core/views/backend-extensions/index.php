<?php

use yii\helpers\Json;
use kartik\dynagrid\DynaGrid;
use kartik\icons\Icon;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var \app\models\Form $searchModel
 */

$this->title = Yii::t('app', 'Extensions');
$this->params['breadcrumbs'][] = $this->title;

?>
<?php $this->beginBlock('add-button'); ?>
    <?=
        \yii\helpers\Html::a(
            \kartik\icons\Icon::show('plus') . ' ' . Yii::t('app', 'Install new extension'),
            ['/core/backend-extensions/explore', 'returnUrl' => \app\backend\components\Helper::getReturnUrl()],
            [
                'class' => 'btn btn-success',
            ]
        )
    ?>

<?php $this->endBlock(); ?>

<?= DynaGrid::widget([
    'options' => [
        'id' => 'extensions-grid',
    ],
    'columns' => [
        [
            'class' => \kartik\grid\CheckboxColumn::className(),
            'options' => [
                'width' => '10px',
            ],
        ],
        'id',
        'name',
        'type.name',
        'force_version',
        'current_package_version_timestamp',
        [
            'class' => 'app\backend\columns\BooleanStatus',
            'attribute' => 'is_active',
        ],
        [
            'class' => 'app\backend\components\ActionColumn',
            'urlCreator' => function($action, $model, $key, $index) {

                $params = [
                    '/core/backend-extensions/' . $action,
                    'name' => $model->name,
                ];

                $params['returnUrl'] = app\backend\components\Helper::getReturnUrl();

                return yii\helpers\Url::toRoute($params);
            },
            'buttons' => [
                [
                    'url' => 'show-package',
                    'icon' => 'eye',
                    'class' => 'btn-info btn-show-package',
                    'label' => Yii::t('app', 'View'),
                ],
            ],
            'options' => [
                'width' => '125px',
            ]
        ],
    ],
    'theme' => 'panel-default',
    'gridOptions'=>[
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'hover'=>true,
        'panel'=>[
            'heading'=>'<h3 class="panel-title">' . Icon::show('puzzle-piece') .$this->title.'</h3>',
            'after' => $this->blocks['add-button'],
        ],

    ]
]); ?>

<?php
$extensionInformation = Json::encode(Yii::t('app', 'Extension information'));
$js = <<<JS
$(".btn-show-package").click(function(){
    var that = $(this),
        url = that.attr('href');

    that.dialogAction(
        url,
        {
            title: $extensionInformation
        }
    );
    return false;
})
JS;

$this->registerJs($js);