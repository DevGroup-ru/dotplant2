<?php

use app\backgroundtasks\models\NotifyMessage;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\backgroundtasks\models\NotifyMessage $model
 */

$this->title = 'Notification';
$this->params['breadcrumbs'][] = ['label' => 'Notifications', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<h1><?= Html::encode($this->title) ?></h1>
<div class="notification-index">

	<div class="panel <?= $class ?>">
		<div class="panel-heading">
			<h2 class="panel-title"><?= 'Task "'.(($model->task !== null) ?
                    $model->task->name
                    :
                    '[deleted]').'" is completed with '.NotifyMessage::getStatuses()[$model->result_status] ?>
                    <span class="pull-right">[<?= Yii::t('app', 'Received') ?>: <?= $model->ts; ?>]</span></h2>
		</div>
		<div class="panel-body">
			<?= $model->result ?>
		</div>
	</div>

</div>