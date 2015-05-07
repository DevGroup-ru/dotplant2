<?php

use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\modules\seo\models\Config $model
 */

$this->title = $model->key;
$this->params['breadcrumbs'][] = ['label' => 'SEO', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<h1><?= Html::encode($this->title) ?></h1>
<?= Yii::$app->user->can('cache manage') ? Html::button(Yii::t('app', 'Delete Robots Cache') . ' <span class="fa"></span>', ['class'=> 'btn btn-warning', 'id' => 'flushCache']) : ''; ?>

<?= $this->render('_configForm', ['model' => $model]); ?>

<script type="text/javascript">
    $(function() {
        $('#flushCache').on('click', function() {
            $.ajax({
                'url' : '/seo/manage/flush-robots-cache',
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