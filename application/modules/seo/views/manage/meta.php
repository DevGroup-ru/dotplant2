<?php

use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var \app\modules\seo\models\Meta $searchModel
 */

$this->title = Yii::t('app', 'Meta tags');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'SEO'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="meta-tags-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Meta tag'), ['create-meta', 'returnUrl' => \app\backend\components\Helper::getReturnUrl()], ['class' => 'btn btn-success']) ?>
        <?= Html::button(Yii::t('app', 'Delete selected'), ['class'=> 'btn btn-danger', 'id' => 'deleteTasks']); ?>
        <?= Yii::$app->user->can('cache manage') ? Html::button(Yii::t('app', 'Delete Meta Cache') . ' <span class="fa"></span>', ['class'=> 'btn btn-warning pull-right', 'id' => 'flushCache']) : ''; ?>
    </p>

    <?= $this->render('_metaGrid', ['dataProvider' => $dataProvider, 'searchModel' => $searchModel, 'id' => 'meta-tags']); ?>

</div>

<script type="text/javascript">
    $(function() {
	    $('#deleteTasks').on('click', function() {
		    $.ajax({
			    'url' : '/seo/manage/delete-metas',
			    'type': 'post',
			    'data': {
				    'metas' : $('.grid-view').yiiGridView('getSelectedRows')
			    },
			    success: function(data) {
				    if(data)
					    location.reload();
			    }
		    });
	    });

	    $('#flushCache').on('click', function() {
		    $.ajax({
			    'url' : '/seo/manage/flush-meta-cache',
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