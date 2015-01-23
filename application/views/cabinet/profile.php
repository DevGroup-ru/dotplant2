<?php

/**
 * @var $model app\models\User
 * @var $propertyGroups \app\models\PropertyGroup[]
 * @var $services array
 * @var $this yii\web\View
 */

use kartik\widgets\ActiveForm;
use yii\authclient\widgets\AuthChoice;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Your profile');
$this->params['breadcrumbs'] = [
    [
        'label' => Yii::t('app', 'Personal cabinet'),
        'url' => Yii::$app->request->baseUrl.'/cabinet'
    ],
    $this->title,
];

?>
<h1><?= $this->title ?></h1>

<div class="row">
    <div class="span4 well">
        <?php
            $form = ActiveForm::begin([
                'id' => 'profile-form',
                'type' => ActiveForm::TYPE_VERTICAL,
            ]);
        ?>
            <?= $form->field($model, 'first_name') ?>
            <?= $form->field($model, 'last_name') ?>
            <?= $form->field($model, 'email') ?>
            <?php foreach ($propertyGroups as $group): ?>
                <?php if ($group['group']->hidden_group_title == 0): ?>
                    <h4><?= $group['group']->name; ?></h4>
                <?php endif; ?>
                <?php
                    /** @var \app\models\Property[] $properties */
                    $properties = $group['properties'];
                ?>
                <?php foreach ($properties as $property): ?>
                    <?= $property->handler($form, $model->abstractModel, [], 'frontend_edit_view'); ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary']) ?>
            </div>
        <?php ActiveForm::end(); ?>
    </div>
    <div class="span4 well">
        <?php
            $authChoice = AuthChoice::begin([
                'baseAuthUrl' => ['default/auth']
            ]);
        ?>
            <?php if (count($authChoice->clients) > count($services)): ?>
                <h3><?= Yii::t('app', 'Attach service') ?></h3>
                <ul class="auth-clients clear">
                    <?php foreach ($authChoice->clients as $client): ?>
                        <?php if (!in_array($client->className(), $services)): ?>
                            <li class="auth-client"><?= $authChoice->clientLink($client) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php AuthChoice::end(); ?>
        <?php if (!empty($services)): ?>
            <h3><?= Yii::t('app', 'Detach service') ?></h3>
            <?php
                $authChoice = AuthChoice::begin([
                    'baseAuthUrl' => ['default/auth']
                ]);
            ?>
                <ul class="auth-clients clear">
                    <?php foreach ($authChoice->clients as $client): ?>
                        <?php if (in_array($client->className(), $services)): ?>
                            <li class="auth-client"><?= $authChoice->clientLink($client) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            <?php AuthChoice::end(); ?>
        <?php endif; ?>
    </div>
</div>