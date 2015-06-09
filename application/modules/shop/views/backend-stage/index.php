<?php
/**
 * @var \yii\web\View $this
 */
use app\backend\widgets\BackendWidget;
use \yii\helpers\Html;
use \yii\helpers\Url;
?>
<?php
    BackendWidget::begin([
        'title' => Yii::t('app', 'Order stage subsystem'),
        'icon' => 'cogs',
        'footer' => ''
    ]);
?>
    <?= Html::a(Yii::t('app', 'Order stages'), Url::to(['stage-index']), ['class' => 'btn btn-primary']); ?>
    <?= Html::a(Yii::t('app', 'Order stages leafs'), Url::to(['leaf-index']), ['class' => 'btn btn-primary']); ?>
    <?= Html::a(Yii::t('app', 'Render graph'), Url::to(['render-graph']), ['class' => 'btn btn-primary']); ?>
<?php BackendWidget::end(); ?>