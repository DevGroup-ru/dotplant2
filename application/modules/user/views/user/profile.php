<?php

/**
 * @var $model \app\modules\user\models\User
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
        'url' => ['/user/user/profile']
    ],
    $this->title,
];

$propertyGroups = $model->getPropertyGroups();

?>
<?php
$form = ActiveForm::begin([
    'id' => 'profile-form',
    'type' => ActiveForm::TYPE_VERTICAL,
]);
?>
<div class="row">
    <div class="col-md-6 col-sm-12">
        <h1 class="h2"><?= $this->title ?></h1>
        <?= $form->field($model, 'first_name') ?>
        <?= $form->field($model, 'last_name') ?>
        <?= $form->field($model, 'email') ?>
        <div class="form-group">
            <?=
            Html::a(
                Yii::t('app', 'Change password'),
                ['/user/user/change-password']
            )
            ?>
        </div>
        <div class="form-group">
            <?=
            Html::a(
                Yii::t('app', 'Your orders'),
                ['/shop/orders/list']
            )
            ?>
        </div>

    </div>
    <div class="col-md-6 col-sm-12">
        <?php foreach ($propertyGroups as $groupId => $properties): ?>

            <?php

            if (count($properties) > 0) {
                $group = \app\models\PropertyGroup::findById($groupId);
                if (intval($group->hidden_group_title) == 0) {
                    echo Html::tag('h2', $group->name);
                }

                $properties = app\models\Property::getForGroupId($groupId);

                foreach ($properties as $prop) {
                    $property_values = $model->getPropertyValuesByPropertyId($prop->id);


                    echo $prop->handler($form, $model->getAbstractModel(), $property_values, 'frontend_edit_view');


                }
            }
            ?>


        <?php endforeach; ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 col-sm-12">
        <?php
        $authChoice = AuthChoice::begin([
            'baseAuthUrl' => ['/user/user/auth']
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
                'baseAuthUrl' => ['/user/user/auth']
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

<?php ActiveForm::end(); ?>