<?php

use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var \app\backgroundtasks\models\NotifyMessage $searchModel
 */

$this->title = 'Notifications';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="notifications-index">

	<h1><?= Html::encode($this->title) ?></h1>

	<?= $this->render(
        '_notificationsGrid',
        ['dataProvider' => $dataProvider, 'searchModel'=> $searchModel, 'id' => 'notifications']
    ); ?>

</div>