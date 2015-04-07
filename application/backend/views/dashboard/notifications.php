<?php

/**
 * @var integer $id
 * @var \app\backend\models\Notification[] $notifications
 * @var boolean $showMoreLink
 * @var \yii\web\View $this
 */

use yii\helpers\Html;

?>
<ul class="notification-body">
    <?php
        $now = date('Y-m-d h:i:s');
        foreach ($notifications as $notification):
    ?>
            <li data-id="<?= $notification->id ?>">
                <span class="padding-10 <?= $notification->viewed == 0 ? 'unread' :'' ?>">
                    <?= Html::tag('span', $notification->label, ['class' => 'label label-' . $notification->type]) ?>
                    <span><?= $notification->message ?></span>
                    <div class="pull-right font-xs text-muted">
                        <em><?= Yii::$app->formatter->asRelativeTime($notification->date); ?></em>
                        <?php if ($notification->viewed == 0): ?>
                            <a href="#" data-type="new-notification" data-id="<?= $notification->id ?>"><i class="fa fa-eye"></i></a>
                        <?php endif; ?>
                    </div>
                </span>
            </li>
        <?php endforeach; ?>
        <?php if ($showMoreLink): ?>
            <li class="show-more">
                <a href="#" data-id="<?= $id ?>"><?= Yii::t('app', 'Show more') ?></a>
            </li>
        <?php endif; ?>
</ul>