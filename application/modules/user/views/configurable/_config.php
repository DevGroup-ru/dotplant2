<?php

/** @var \app\modules\config\models\Configurable $configurable */
/** @var \app\backend\components\ActiveForm $form */
/** @var \app\modules\user\models\ConfigConfigurationModel $model */
use \app\modules\user\models\AuthClientConfig;
use \yii\helpers\Html;
use \kartik\icons\Icon;
use app\backend\widgets\BackendWidget;

$authClientConfigModel = new AuthClientConfig();

?>

<div>
    <div class="col-md-6">
        <?php BackendWidget::begin(['title' => Yii::t('app', 'Main settings'), 'options' => ['class' => 'visible-header']]); ?>
        <?= $form->field($model, 'loginSessionDuration') ?>

        <?= $form->field($model, 'passwordResetTokenExpire') ?>

        <?= $form->field($model, 'postRegistrationLayout') ?>
        <?php BackendWidget::end() ?>
    </div>
</div>



<div class="row">
    <div class="col-md-8">
        <h3 class="no-margin">
            <?= Yii::t('app', 'Auth clients') ?>
        </h3>
    </div>
    <div class="col-md-4">
        <?=
        Html::a(
            Icon::show('plus') . ' ' . Yii::t('app', 'Add auth client'),
            '#add-auth-client-modal',
            [
                'class' => 'btn btn-sm btn-primary pull-right',
                'data-toggle' => 'modal',
                'data-target' => '#add-auth-client-modal',
            ]
        )
        ?>
    </div>
</div>

<?php foreach ($model->authClients as $index => $config): ?>
    <div class="panel panel-default authclient-<?=$index?>">
        <div class="panel-heading">
            <div class="row">
                <div class="col-md-10 col-sm-12">
                    <?=
                        \kartik\widgets\TypeaheadBasic::widget([
                            'model' => $config,
                            'attribute' => "[$index]class_name",
                            'data' => AuthClientConfig::classNameAutoComplete(),
                            'pluginOptions' => ['highlight'=>true],
                        ])
                    ?>
                </div>
                <div class="col-md-2 col-sm-12">
                    <?=
                    Html::a(
                        Icon::show('times') . ' ' . Yii::t('app', 'Delete'),
                        '#',
                        [
                            'class' => 'remove-auth-client btn btn-sm btn-danger pull-right',
                            'data-index' => $index,
                        ]
                    )
                    ?>
                </div>
            </div>
            <?php
//                $form->field(
//                    $config,
//                    "[$index]class_name",
//                    [
//                        'options' => [
//                            'class' => 'pull-left text-block',
//                        ],
//                    ]
//                )->label(false)->widget(
//                    \kartik\widgets\TypeaheadBasic::className(),
//                    [
//                        'data' => AuthClientConfig::classNameAutoComplete(),
//                        'pluginOptions' => ['highlight'=>true],
//                    ]
//                )

            ?>


        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <?php
                    if ($config->clientType === AuthClientConfig::TYPE_OAUTH1) {
                        echo $form->field(
                            $config,
                            "[$index]consumerKey"
                        );
                        echo $form->field(
                            $config,
                            "[$index]consumerSecret"
                        );
                    } elseif ($config->clientType === AuthClientConfig::TYPE_OAUTH2) {
                        echo $form->field(
                            $config,
                            "[$index]clientId"
                        );
                        echo $form->field(
                            $config,
                            "[$index]clientSecret"
                        );
                    }
                    ?>
                </div>
                <div class="col-md-6 col-sm-12">
                    <?php
                    echo $form->field(
                        $config,
                        "[$index]id"
                    );
                    echo $form->field(
                        $config,
                        "[$index]name"
                    );
                    echo $form->field(
                        $config,
                        "[$index]title"
                    );

                    ?>

                </div>
            </div>



        </div>
    </div>

<?php endforeach; ?>

<div class="modal fade" id="add-auth-client-modal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title"><?= Yii::t('app', 'Add auth client') ?></h4>
			</div>
			<div class="modal-body">

                <?=
                $form->field(
                    $authClientConfigModel,
                    "[-1]class_name"
                )->widget(
                    \kartik\widgets\TypeaheadBasic::className(),
                    [
                        'data' => AuthClientConfig::classNameAutoComplete(),
                        'pluginOptions' => ['highlight'=>true],
                    ]
                )
                ?>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" data-dismiss="modal">
                    <?= Icon::show('times') ?> <?= Yii::t('app', 'Cancel') ?>
                </button>
				<button type="button" class="btn btn-primary" id="add-auth-client">
                    <?= Icon::show('floppy-o') ?> <?= Yii::t('app', 'Add') ?>
                </button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?=
Html::hiddenInput('addAuthClientFlag', 0, ['id'=>'addAuthClientFlag']);
?>
<?=
Html::hiddenInput('removeAuthClientIndex', -1, ['id'=>'removeAuthClientIndex']);
?>

<?php

$js = <<<JS
"use strict";

$("#add-auth-client").click(function(){
    if ($("#config-form").submit() === true) {
        $("#add-auth-client-modal").modal('hide');
    }
    return false;
});
$("#config-form").submit(function(){
    if ($("#add-auth-client-modal").is(':visible')===true) {
        $("#addAuthClientFlag").val(1);
    }
    return true;
});
$(".remove-auth-client").click(function(){
    $("#removeAuthClientIndex").val($(this).data('index'));
    $("#config-form").submit();
    return false;
});
JS;

$this->registerJs($js);
