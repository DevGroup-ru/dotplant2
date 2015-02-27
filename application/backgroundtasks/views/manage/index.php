<?php

use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\backgroundtasks\models\Task $searchModel
 */

$this->title = Yii::t('app', 'Tasks');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tasks-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="form-group">
        <div class="btn-toolbar grid-toolbar" role="toolbar">
            <div class="btn-group">
                <?= Html::a(Yii::t('app', 'Create Task'), ['create', 'returnUrl' => \app\backend\components\Helper::getReturnUrl()], ['class' => 'btn btn-success']) ?>
            </div>
            <div class="btn-group">
                <?= Html::button(
                    Yii::t('app', 'Delete selected'),
                    [
                        'id' => 'deleteTasks',
                        'class'=> 'btn btn-danger',
                        'data-pjax' => '0',
                    ]
                ); ?>
            </div>
        </div>
    </div>

    <?= $this->render('_tasksGrid', ['dataProvider' => $dataProvider, 'searchModel' => $searchModel, 'id' => 'tasks']); ?>

</div>

<script type="text/javascript">
    $(function() {
        $('#deleteTasks').on('click', function() {
            if (confirm('Are you sure you want to delete selected items?')) {
                $.ajax({
                    'url' : '/background/manage/delete-tasks',
                    'type': 'post',
                    'data': {
                        'tasks' : $('.grid-view').yiiGridView('getSelectedRows')
                    },
                    success: function(data) {
                        if(data)
                            location.reload();
                    }
                });
            }
        });
    });
</script>