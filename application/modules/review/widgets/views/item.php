<?php

/**
 * @var bool $allowRate
 * @var \app\models\PropertyGroup[] $groups
 * @var int $index
 * @var string $key
 * @var \app\modules\review\models\Review $model
 * @var \yii\web\View $view
 */

use app\models\Property;
use kartik\helpers\Html;
use \kartik\icons\Icon;

if ($model->submission->processed_by_user_id === null) {
    $userName = Yii::t('app', 'Guest');
} else {
    /** @var \app\modules\user\models\User $user */
    $user = \app\modules\user\models\User::findIdentity($model->submission->processed_by_user_id);
    $userName = Html::encode($user->getDisplayName());
}
//$model->submission->getPropertyGroups(true);

?>
<div class="row review">
    <div class="col-md-4">
        <div class="review-date_submitted label label-default">
            <?= Icon::show('calendar') . date("d.m.Y H:i:s", strtotime($model->submission->date_received)) ?>
        </div>
        <div class="review-author">
            <?= Icon::show('user') . $userName ?>
        </div>
    </div>
    <div class="col-md-8"><?= Html::encode($model->review_text) ?>
    <?php foreach ($groups as $group): ?>
        <?php $properties = Property::getForGroupId($group->id); ?>
        <?php foreach ($properties as $property): ?>
            <?php if ($propertyValues = $model->submission->getPropertyValuesByPropertyId($property->id)): ?>
                    <?=
                    $property->handler(
                        'form',
                        $model->submission->abstractModel,
                        $propertyValues,
                        'frontend_render_view'
                    );
                    ?>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
    </div>
</div>
