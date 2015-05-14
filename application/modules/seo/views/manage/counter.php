<?php

use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var \app\modules\seo\models\Counter $searchModel
 */

$this->title = Yii::t('app', 'Counters');
$this->params['breadcrumbs'][] = ['label' => 'SEO', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="counter-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Counter'), ['create-counter', 'returnUrl' => \app\backend\components\Helper::getReturnUrl()], ['class' => 'btn btn-success']) ?>
        <?= Html::button(Yii::t('app', 'Delete selected'), ['class'=> 'btn btn-danger', 'id' => 'deleteCounters']); ?>
        <?= Yii::$app->user->can('cache manage') ? Html::button(Yii::t('app', 'Delete Counter Cache') . ' <span class="fa"></span>', ['class'=> 'btn btn-warning pull-right', 'id' => 'flushCache']) : ''; ?>
    </p>

    <?= $this->render('_counterGrid', ['dataProvider' => $dataProvider, 'searchModel' => $searchModel, 'id' => 'counters']); ?>

</div>
<script type="text/javascript">
    $(function() {
        $('#deleteCounters').on('click', function() {
            $.ajax({
                'url' : '/seo/manage/delete-counters',
                'type': 'post',
                'data': {
                    'counters' : $('.grid-view').yiiGridView('getSelectedRows')
                },
                success: function(data) {
                    if(data)
                        location.reload();
                }
            });
        });
        $('#flushCache').on('click', function() {
            $.ajax({
                'url' : '/seo/manage/flush-counter-cache',
                'type': 'post',
                success: function(data) {
                    $('#flushCache').removeClass('btn-warning').removeClass('btn-danger').addClass('btn-success').find('span.fa').removeClass('fa-times').addClass('fa-check');
                },
                error: function() {
                    $('#flushCache').removeClass('btn-warning').removeClass('btn-success').addClass('btn-danger').find('span.fa').removeClass('fa-check').addClass('fa-times');
                }
            });
        });
    });
</script>