<?php

use kartik\icons\Icon;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;

?>
<div class = "widget-reviews">
    <?php \yii\widgets\Pjax::begin() ?>
    <?= \yii\widgets\ListView::widget([
        'dataProvider' => $reviews,
        'itemView' => function ($model, $key, $index, $widget) {
            $itemView = Html::beginTag('div', ['class' => 'row review']);
            $itemView .= Html::beginTag('div', ['class' => 'col-md-4']);

            $itemView .= Html::beginTag('div', ['class' => 'review-date_submitted label label-default']);
            $itemView .= Icon::show('calendar');
            $itemView .= date("d.m.Y H:i:s", strtotime($model->date_submitted));
            $itemView .= '</div>';

            $itemView .= Html::beginTag('div', ['class' => 'review-author']);
            if ($model->user) {
                $itemView .= Icon::show('user') . Html::encode($model->user->getAwesomeUsername());
            } else {
                $itemView .= Icon::show('user') . Html::encode($model->author_name);
            }
            $itemView .= '</div>';

            $itemView .= '</div>';

            $itemView .= Html::beginTag('div', ['class' => 'col-md-8']);
            $itemView .= Html::encode($model->text);
            $itemView .= '</div>';

            $itemView .= '</div>';

            return $itemView;
        },
    ]) ?>
    <?php \yii\widgets\Pjax::end() ?>
    <?php $form = ActiveForm::begin(['id' => 'review-form']); ?>
    <div class="row">
        <div class="col-md-12">
            <h2>
                <?= Yii::t('shop', 'You review') ?>
                <?php if (Yii::$app->getUser()->isGuest) : ?>
                    <small>[<?= Html::a(Yii::t('app', 'Login'),
                            ['/default/login', 'returnUrl' => Yii::$app->request->absoluteUrl]) ?>]</small>
                <?php else : ?>
                    <small>[<?= Yii::$app->getUser()->getIdentity()->getAwesomeUserName() ?>]</small>
                <?php endif; ?>
            </h2>
        </div>
    </div>
    <?php if (Yii::$app->getUser()->isGuest) : ?>
        <div class = "row">
        <div class = "col-md-6">
            <?=
            $form->field($model, 'author_name')
            ?>
            <?=
            $form->field($model, 'author_phone')
            ?>
        </div>
        <div class = "col-md-6">
            <?=
            $form->field($model, 'author_email')
            ?>
        </div>
    </div>
    <?php else : ?>
        <div class = "row">
        <div class = "col-md-6">
            <?=
            $form->field($model, 'author_user_id', ['template' => '{input}'])->hiddenInput();
            ?>
            <?=
            $form->field($model, 'author_phone')
            ?>
        </div>
    </div>
    <?php endif; ?>
    <div class = "row">
        <div class = "col-md-12">
            <?=
            $form->field($model, 'text')->textarea()
            ?>
        </div>
    </div>
    <?php
    if ($useCaptcha) {
        echo $form->field($model, 'captcha')->widget(yii\captcha\Captcha::className(), [
            'template' => '<div class="row"><div class="col-lg-6">{image}</div><div class="col-lg-6">{input}</div></div>',
            'captchaAction' => '/default/captcha',
        ]);

    }
    ?>
    <div class = "row">
        <div class = "col-md-12">
            <div class = "form-group no-margin">
                <?=
                Html::submitButton(
                    Yii::t('shop', 'You review'),
                    ['class' => 'btn btn-success']
                ) ?>
            </div>
        </div>
    </div>


    <?php ActiveForm::end(); ?>
</div>