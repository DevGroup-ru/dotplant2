<?php

/**
 * @var $model \app\models\Form
 * @var $groups \app\models\PropertyGroup[]
 * @var $this \yii\web\View
 * @var $id string
 */

use app\models\Property;
use kartik\widgets\ActiveForm;
use yii\captcha\Captcha;

?>
<?php
    $form = ActiveForm::begin([
        'id' => $id,
        'action' => ['/default/submit-form', 'id' => $this->context->formId],
        'options' => $options,
    ]);
?>
    <?php if (!$this->context->modal): ?>
        <h3><?= $model->name; ?></h3>
    <?php endif; ?>
    <?php foreach ($groups as $group): ?>
        <?php if ($group->hidden_group_title == 0): ?>
            <h4><?= $group->name; ?></h4>
        <?php endif; ?>
        <?php $properties = Property::getForGroupId($group->id); ?>
        <?php foreach ($properties as $property): ?>
            <?php
                if($property->captcha) {
                    echo Captcha::widget(
                        [
                            'model'         => $model->abstractModel,
                            'attribute'     => $property->key,
                            'captchaAction' => 
                            [
                                '/default/captcha'
                            ]
                        ]
                    );
                } else {
                    echo $property->handler($form, $model->abstractModel, [], 'frontend_edit_view');
                } 
            ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
    <?= \kartik\helpers\Html::submitButton(Yii::t('app', 'Send'), ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end(); ?>
