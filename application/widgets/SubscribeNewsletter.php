<?php

namespace app\widgets;

use app\models\SubscribeEmail;
use yii\base\Widget;

class SubscribeNewsletter extends Widget
{
    public $action = '/subscribe/add';
    public $submitButtonText = "Subscribe";

    /**
     * @inheritdoc
     */
    public function run()
    {
        $model = new SubscribeEmail();

        return $this->render(
            'subscribe-newsletter/addemail.php',
            [
                'model' => $model,
                'action' => $this->action,
                'submitButtonText' => $this->submitButtonText
            ]
        );
    }
}
