<?php

namespace app\backend\widgets;

use yii\base\Widget;
use app\backend\models\Notification as NotificationModel;

class Notification extends Widget
{
    public $notificationsCount = 10;
    public $viewFile = 'notification';

    private $user_id = null;

    /**
     *
     */
    public function init()
    {
        parent::init();

        if (\Yii::$app->user->can('administrate')) {
            $this->user_id = \Yii::$app->user->id;
        }
    }

    /**
     *
     */
    public function run()
    {
        parent::run();

        if (null === $this->user_id) {
            return '';
        }

        $count = NotificationModel::getCountByUserId($this->user_id);
        $notifications = NotificationModel::getAllByUserId($this->user_id, NotificationModel::STATUS_NOT_VIEWED);

        return $this->render($this->viewFile, [
            'count' => $count,
            'notifications' => $notifications
        ]);
    }
}

?>