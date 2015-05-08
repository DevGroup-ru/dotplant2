<?php

namespace app\modules\review;

use app\components\BaseModule;
/**
 * Base configuration module for DotPlant2 CMS
 * @package app\modules\review
 */
class ReviewModule extends BaseModule
{
    /**
     * @var string email's to send notification
     */
    public $email;
    /***
     * @var array contain bool values. If 1 when send notification Object to email
     */
    public $notification = [
        'Product' => 0,
        'Page' => 0,
    ];

    /**
     * @var array email template
     */
    public $emailTemplate = [
        'Product' => '@app/modules/review/views/page-review-email-template',
        'Page' => '@app/modules/review/views/page-review-email-template',
    ];

    /**
     * @return array the behavior configurations.
     */
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

    /**
     * @param string $objectName
     * @return string renderFile
     */
    public function getEmailTemplate($objectName)
    {
        return isset($this->emailTemplate[$objectName]) && !empty($this->emailTemplate[$objectName])
            ? $this->emailTemplate[$objectName]
            : '@app/modules/review/views/page-review-email-template';
    }

    /**
     * @param $objectName
     * @return bool Need send notification ?
     */
    public function isEnableNotification($objectName)
    {
        return isset($this->notification[$objectName]) && (int) $this->notification[$objectName] === 1;
    }
}
