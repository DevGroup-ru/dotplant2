<?php

namespace app\modules\review;

use app\components\BaseModule;

class ReviewModule extends BaseModule
{
    public $email;
    public $notification = [
        'Product' => 0,
        'Page' => 0,
    ];
    public $emailTemplate = [
        'Product' => '@app/modules/review/views/page-review-email-template',
        'Page' => '@app/modules/review/views/page-review-email-template',
    ];

    public function behaviors()
    {
        return [
            'configurableModule' => [
                'class' => 'app\modules\config\behaviors\ConfigurableModuleBehavior',
                'configurationView' => '@app/modules/review/views/configurable/_config',
                'configurableModel' => 'app\modules\review\models\ConfigConfigurableModel',
            ]
        ];
    }

    public function getEmailTemplate($objectName)
    {
        return isset($this->emailTemplate[$objectName]) && !empty($this->emailTemplate[$objectName])
            ? $this->emailTemplate[$objectName]
            : '@app/modules/review/views/page-review-email-template';
    }

    public function isEnableNotification($objectName)
    {
        return isset($this->notification[$objectName]) && (int) $this->notification[$objectName] === 1;
    }
}
