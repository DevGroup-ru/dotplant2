<?php
/**
 * @var $model \app\models\Form
 * @var $reviews \app\modules\review\models\Review[]
 * @var $review \app\modules\review\models\Review
 * @var $useCaptcha boolean
 * @var $groups \app\models\PropertyGroup[]
 * @var $view \yii\web\View
 * @var $objectModel \yii\db\ActiveRecord
 * @var $ratingGroupName string
 */

/** @var \yii\data\ArrayDataProvider $reviews */

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use app\models\Property;

$allowRate = !empty($ratingGroupName);

?>
<div class="row">
    <div class="col-md-12">
        <?php if ($allowRate): ?>
            <?= \app\modules\review\widgets\rating\RatingShowWidget::widget(['objectModel' => $objectModel]) ?>
        <?php endif; ?>
    </div>
</div>
<div class="row">

    <div class="col-md-6 col-lg-8 col-sm-12">
        <div class = "widget-reviews">
            <?php if ($reviews->totalCount === 0):?>
                <?php if($allowRate === false): ?>
                    <?= Yii::t('app', 'There\'s no reviews yet. Be first to leave a review!') ?>
                <?php endif; ?>
            <?php else: ?>
            <?php \yii\widgets\Pjax::begin() ?>
            <?=
            \yii\widgets\ListView::widget([
                'dataProvider' => $reviews,
                'itemView' => 'item',
                'viewParams' => ['allowRate' => $allowRate, 'groups' => $groups],
            ])
            ?>
            <?php \yii\widgets\Pjax::end() ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 col-sm-12">
        <?php
        $form = ActiveForm::begin(
            [
                'action'=>[
                    '/review/process/process',
                    'objectId' => $objectModel->object->id,
                    'objectModelId' => $objectModel->id,
                    'id' => $model->id,
                    'returnUrl' => Yii::$app->request->url,
                ],
                'id' => 'review-form',
                'options' => ['data-type' => 'form-widget']
            ]
        );
        ?>
        <h2>
            <?= Yii::t('app', 'Leave review') ?>
            <?php if (Yii::$app->getUser()->isGuest): ?>
                <small>[<?=
                    Html::a(
                        Yii::t('app', 'Login'),
                        ['/user/user/login', 'returnUrl' => Yii::$app->request->absoluteUrl],
                        [
                            'rel' => 'nofollow',
                        ]
                    )
                    ?>]</small>
            <?php else: ?>
                <small>[<?= Yii::$app->getUser()->getIdentity()->getDisplayName() ?>]</small>
            <?php endif; ?>
        </h2>
        <?php if ($allowRate): ?>
            <?=
            \app\modules\review\widgets\rating\RatingWidget::widget(
                [
                    'groupName' => $ratingGroupName,
                ]
            )
            ?>
        <?php endif; ?>
        <?php if (Yii::$app->getUser()->isGuest): ?>
            <?= $form->field($review, 'author_email') ?>
        <?php endif; ?>
        <?= $form->field($review, 'review_text')->textarea() ?>
        <?php foreach ($groups as $group): ?>
            <?php $properties = Property::getForGroupId($group->id); ?>
            <?php foreach ($properties as $property): ?>
                <?= $property->handler($form, $model->abstractModel, [], 'frontend_edit_view'); ?>
            <?php endforeach; ?>
        <?php endforeach; ?>

        <?php
        if ($useCaptcha) {
            echo $form->field($review, 'captcha')->widget(yii\captcha\Captcha::className(), [
                'template' => '<div class="row"><div class="col-lg-4">{image}</div><div class="col-lg-8">{input}</div></div>',
                'captchaAction' => '/default/captcha',
            ]);
        }
        ?>
        <div class="form-group no-margin">
            <?=
            Html::submitButton(
                Yii::t('app', 'Your review'),
                ['class' => 'btn btn-success']
            )
            ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="modal-form-info-review-form">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Отзыв отправлен</h4>
      </div>
      <div class="modal-body">
        <p>\Yii::t('Your review will appear on the website immediately after moderation')</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
