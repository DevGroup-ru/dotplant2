<?php

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $permissions
 * @var yii\data\ActiveDataProvider $roles
 * @var bool $isRules
 */

use app\backend\widgets\BackendWidget;
use app\backend\widgets\flushcache\FlushCacheButton;
use kartik\helpers\Html;
use yii\bootstrap\Tabs;

$this->title = Yii::t('app', 'Rbac');
$this->params['breadcrumbs'][] = $this->title;

?>
<?php $this->beginBlock('buttonGroup'); ?>
<div class="btn-toolbar" role="toolbar">
    <div class="btn-group">
        <?=
            Html::a(
                Yii::t('app', 'Create Permission'),
                ['create', 'returnUrl' => \app\backend\components\Helper::getReturnUrl(), 'type' => \yii\rbac\Item::TYPE_PERMISSION],
                ['class' => 'btn btn-success']
            )
        ?>
        <?=
            Html::a(
                Yii::t('app', 'Create Role'),
                ['create', 'returnUrl' => \app\backend\components\Helper::getReturnUrl(), 'type' => \yii\rbac\Item::TYPE_ROLE],
                ['class' => 'btn btn-success']
            )
        ?>
    </div>
    <?= Html::button(Yii::t('app', 'Delete selected'), ['class'=> 'btn btn-danger', 'id' => 'deleteItems']); ?>
</div>
<?php $this->endBlock(); ?>
<div class="user-index">
    <?php
        BackendWidget::begin(
            [
                'icon' => 'lock',
                'title'=> $this->title,
                'footer' => $this->blocks['buttonGroup'],
            ]
        );
    ?>
        <?= Tabs::widget([
            'items' => [
                [
                    'label' => Yii::t('app', 'Permissions'),
                    'content' => $this->render('_rbacGrid', ['data' => $permissions, 'isRules' => $isRules, 'id' => 'operations']),
                    'active' => true,
                ],
                [
                    'label' => Yii::t('app', 'Roles'),
                    'content' => $this->render('_rbacGrid', ['data' => $roles, 'isRules' => $isRules, 'id' => 'roles']),
                ],
            ],
        ]); ?>
    <?php BackendWidget::end(); ?>
</div>


<?php

$js = <<<JS
    "use strict";

    $('#deleteItems').on('click', function() {
        $.ajax({
            'url' : '/backend/rbac/remove-items',
            'type': 'post',
            'data': {
                'items' : $('.grid-view').yiiGridView('getSelectedRows')
            },
            success: function(data) {
                location.reload();
            }
        });
    });
JS;

$this->registerJs($js);

?>
