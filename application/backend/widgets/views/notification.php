<?php
/**
 * @var $count int
 * @var $notifications array
 */
?>

<span id="activity" class="activity-dropdown"> <i class="fa fa-user"></i> <b class="badge"> <?= $count ?> </b> </span>
<!-- AJAX-DROPDOWN : control this dropdown height, look and feel from the LESS variable file -->
<div class="ajax-dropdown">
    <!-- the ID links are fetched via AJAX to the ajax container "ajax-notifications" -->
    <div class="btn-group btn-group-justified" data-toggle="buttons">
        <!--label class="btn btn-default">
            <input type="radio" name="activity" id="/backend/dashboard/notifications">
            notify (<span class="notifications-count"><?= $count ?></span>)
        </label-->
    </div>
    <!-- notification content -->
    <div class="ajax-notifications custom-scroll">
        <!--div class="alert alert-transparent">
            <h4>Click a button to show messages here</h4>
        </div>
        <i class="fa fa-lock fa-4x fa-border"></i-->
        <ul class="notification-body">
        <?php
            $now = date('Y-m-d H:i:s');
            foreach ($notifications as $item):
        ?>
            <li data-id="<?= $item['id'] ?>">
                <span class="padding-10 <?= intval($item['viewed']) === 0 ? 'unread' :'' ?>">
                <?= \kartik\helpers\Html::tag('span', $item['label'], ['class' => 'label label-' . $item['type']]) ?>
                <span><?= $item['message'] ?></span>
                <div class="pull-right font-xs text-muted">
                    <em><?= Yii::$app->formatter->asRelativeTime($item['date']); ?></em>
                    <?php if (intval($item['viewed']) === 0): ?>
                        <a href="#" data-type="new-notification" data-id="<?= $item['id'] ?>"><i class="fa fa-eye"></i></a>
                    <?php endif; ?>
                </div>
                </span>
            </li>
        <?php endforeach; ?>
        </ul>
    </div>
    <!-- end notification content -->
    <!-- footer: refresh area -->
                    <span> Последнее сообщение от: <?= isset($item['date']) ? $item['date'] : '' ?>
                        <button type="button" data-loading-text="<i class='fa fa-refresh fa-spin'></i> Loading..." class="btn btn-xs btn-default pull-right">
                            <i class="fa fa-refresh"></i>
                        </button> </span>
    <!-- end footer -->
</div>
