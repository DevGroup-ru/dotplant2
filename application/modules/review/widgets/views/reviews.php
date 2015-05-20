<?php

/**
 * @var $reviews \app\modules\review\models\Review[]
 * @var $useCaptcha boolean
 * @param $groups \app\models\PropertyGroup[];
 */

use kartik\icons\Icon;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use app\models\Property;

?>
<div class = "widget-reviews">
    <!--<?php //\yii\widgets\Pjax::begin() ?>
<?//= \yii\widgets\ListView::widget([
    //        'dataProvider' => $reviews,
    //        'itemView' => function ($model, $key, $index, $widget) {
    //            $itemView = Html::beginTag('div', ['class' => 'row review']);
    //            $itemView .= Html::beginTag('div', ['class' => 'col-md-4']);
    //
    //            $itemView .= Html::beginTag('div', ['class' => 'review-date_submitted label label-default']);
    //            $itemView .= Icon::show('calendar');
    //            $itemView .= date("d.m.Y H:i:s", strtotime($model->date_submitted));
    //            $itemView .= '</div>';
    //
    //            $itemView .= Html::beginTag('div', ['class' => 'review-author']);
    //            if ($model->user) {
    //                $itemView .= Icon::show('user') . Html::encode($model->user->getDisplayName());
    //            } else {
    //                $itemView .= Icon::show('user') . Html::encode($model->author_name);
    //            }
    //            $itemView .= '</div>';
    //
    //            $itemView .= '</div>';
    //
    //            $itemView .= Html::beginTag('div', ['class' => 'col-md-8']);
    //            $itemView .= Html::encode($model->text);
    //            $itemView .= '</div>';
    //
    //            $itemView .= '</div>';
    //
    //            return $itemView;
    //        },
    //    ]) ?>
<?php //\yii\widgets\Pjax::end() ?>-->
    <?php $form = ActiveForm::begin(
        [
            'action'=>[
                'review/process/process',
                'object_model_id' => $object_model_id,
                'id' => $model->id,
                'returnUrl'=>Yii::$app->request->url
            ],
            'id' => 'review-form'
        ]
    ); ?>
    <div class="row">
        <div class="col-md-12">
            <h2>
                <?= Yii::t('app', 'You review') ?>
                <?php if (Yii::$app->getUser()->isGuest) : ?>
                    <small>[<?= Html::a(Yii::t('app', 'Login'),
                            ['/default/login', 'returnUrl' => Yii::$app->request->absoluteUrl]) ?>]</small>
                <?php else : ?>
                    <small>[<?= Yii::$app->getUser()->getIdentity()->getDisplayName() ?>]</small>
                <?php endif; ?>
            </h2>
        </div>
    </div>
    <?php if (Yii::$app->getUser()->isGuest) : ?>
        <div class = "row">
            <div class = "col-md-6">
                <?=
                $form->field($review, 'author_email')
                ?>
            </div>
        </div>
    <?php else : ?>
        <?//=
        //$form->field($review, 'author_email', ['value' => Yii::$app->getUser()->getIdentity()->email])->hiddenInput()->label(false)
        ?>
    <?php endif; ?>
    <div class = "col-md-6">
        <?=
        $form->field($review, 'review_text')
        ?>
    </div>
    <?php foreach ($groups as $group): ?>
        <?php $properties = Property::getForGroupId($group->id); ?>
        <?php foreach ($properties as $property): ?>
            <?= $property->handler($form, $model->abstractModel, [], 'frontend_edit_view'); ?>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <!--<?php/*
    if ($useCaptcha) {
        echo $form->field($model, 'captcha')->widget(yii\captcha\Captcha::className(), [
            'template' => '<div class="row"><div class="col-lg-6">{image}</div><div class="col-lg-6">{input}</div></div>',
            'captchaAction' => '/default/captcha',
        ]);

    }
    */?>-->
    <div class = "row">
        <div class = "col-md-12">
            <div class = "form-group no-margin">
                <?=
                Html::submitButton(
                    Yii::t('app', 'You review'),
                    ['class' => 'btn btn-success']
                ) ?>
            </div>
        </div>
    </div>


    <?php ActiveForm::end(); ?>
</div>
