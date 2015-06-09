<?php

use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var \app\modules\seo\models\Redirect $searchModel
 */

$this->title = Yii::t('app', 'Redirects');
$this->params['breadcrumbs'][] = ['label' => 'SEO', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="meta-tags-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Redirect'), ['create-redirect', 'returnUrl' => \app\backend\components\Helper::getReturnUrl()], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Create Redirects'), ['create-redirects', 'returnUrl' => \app\backend\components\Helper::getReturnUrl()], ['class' => 'btn btn-warning']) ?>
        <?= Html::button(Yii::t('app', 'Delete selected'), ['class'=> 'btn btn-danger', 'id' => 'deleteRedirects']); ?>
    </p>

    <?= $this->render('_redirectGrid', ['dataProvider' => $dataProvider, 'searchModel' => $searchModel, 'id' => 'redirects']); ?>

    <p>
        <?= Html::button(Yii::t('app', 'Generate Redirect File'), ['class'=> 'btn btn-danger', 'id' => 'generateButton']); ?>
        <?= Html::button(Yii::t('app', 'Delete Redirect File'), ['class'=> 'btn btn-danger', 'id' => 'deleteFileButton']); ?>
        <div id="generating" class="alert" style="display: none;"></div>
    </p>

</div>

<?php
$script = <<<JS
$('#deleteRedirects').on('click', function() {
    $.ajax({
        'url' : '/seo/manage/delete-redirects',
        'type': 'post',
        'data': {
            'redirects' : $('.grid-view').yiiGridView('getSelectedRows')
        },
        success: function(data) {
            if(data)
                location.reload();
        }
    });
});

$('#generateButton').on('click', function() {
    $.ajax({
        'url' : '/seo/manage/generate-redirect-file',
        'type': 'post',
        success: function(data) {
            $('#generating').stop(true, true).removeClass('alert-danger').addClass('alert-success').html('<strong>OK!</strong> File generated.').css('display', 'block').fadeOut(1500);
        },
        error: function() {
            $('#generating').stop(true, true).removeClass('alert-success').addClass('alert-danger').html('<strong>ERROR!</strong> File not generated.').css('display', 'block').fadeOut(1500);
        }
    });
});

$('#deleteFileButton').on('click', function() {
    $.ajax({
        'url' : '/seo/manage/delete-redirect-file',
        'type': 'post',
        success: function(data) {
            if(data) {
                $('#generating').stop(true, true).removeClass('alert-danger').addClass('alert-success').html('<strong>OK!</strong> File deleted.').css('display', 'block').fadeOut(1500);
            } else {
                $('#generating').stop(true, true).removeClass('alert-success').addClass('alert-danger').html('<strong>ERROR!</strong> File can not deleted.').css('display', 'block').fadeOut(1500);
            }
        },
        error: function() {
            $('#generating').stop(true, true).removeClass('alert-success').addClass('alert-danger').html('<strong>ERROR!</strong> File can not deleted.').css('display', 'block').fadeOut(1500);
        }
    });
});
JS;
$this->registerJs($script);