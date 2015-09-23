<?php

namespace app\modules\core\models;

use app;
use app\modules\config\models\BaseConfigurationModel;
use Yii;

/**
 * Class ConfigConfigurationModel represents configuration model for retrieving user input
 * in backend configuration subsystem.
 *
 * @package app\modules\shop\models
 */
class ConfigConfigurationModel extends BaseConfigurationModel
{
    /**
     * @var string Path to composer home directory(ie. /home/user/.composer/)
     */
    public $composerHomeDirectory = './composer/';

    /**
     * @var string Internal encoding. It's used for mbstring functions.
     */
    public $internalEncoding;

    public $autoCompleteResultsCount = 5;

    public $fileUploadPath = '@webroot/upload/files/';
    public $removeUploadedFiles = true;
    public $overwriteUploadedFiles = false;

    public $spamCheckerApiKey;

    public $serverName = 'localhost';

    public $daysToStoreSubmissions;

    public $errorMonitorEnabled = false;
    public $emailNotifyEnabled = false;
    public $devmail = '';
    public $notifyOnlyHttpCodes = '';
    public $numberElementsToStore = 5;
    public $immediateNotice = false;
    public $immediateNoticeLimitPerUrl = 10;
    public $httpCodesForImmediateNotify = '404,500';

    public $emailConfig = [
        'transport' => 'Swift_MailTransport',
        'host' => 'localhost',
        'username' => 'login',
        'password' => 'password',
        'port' => '25',
        'encryption' => '',
        'mailFrom' => 'login',
        'sendMail' => '',
    ];

    public $wysiwyg_id = 1;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'composerHomeDirectory',
                    'internalEncoding',
                    'fileUploadPath',
                    'serverName',
                ],
                'required',
            ],
            [
                [
                    'autoCompleteResultsCount',
                ],
                'filter',
                'filter' => 'intval',
            ],
            [
                [
                    'autoCompleteResultsCount',
                    'daysToStoreSubmissions',
                    'wysiwyg_id',
                ],
                'integer',
            ],
            [
                [
                    'spamCheckerApiKey',
                ],
                'string',
            ],
            [
                [
                    'removeUploadedFiles',
                    'overwriteUploadedFiles',
                ],
                'boolean'
            ],
            [
                [
                    'removeUploadedFiles',
                    'overwriteUploadedFiles',
                ],
                'filter',
                'filter' => 'boolval',
            ],
            [
                ['fileUploadPath'],
                function ($attribute, $params)
                {
                    $_directory = \yii\helpers\FileHelper::normalizePath(\Yii::getAlias($this->$attribute));
                    if (!is_dir($_directory)) {
                        $this->addError($attribute, Yii::t('app', 'Directory {dir} not exists.', ['dir' => $_directory]));
                    } elseif (!is_writable($_directory)) {
                        $this->addError($attribute, Yii::t('app', 'Directory {dir} not writable.', ['dir' => $_directory]));
                    }
                }
            ],
            [['emailConfig'], 'each', 'rule' => ['string']],
            [
                ['emailConfig'],
                function ($attribute, $params)
                {
                    $value = $this->$attribute;
                    if (empty($value['transport'])) {
                        $this->addError($attribute, Yii::t('app', 'Mail transport cannot be empty.'));
                    } elseif (
                        'Swift_SmtpTransport' === $value['transport']
                        && (
                            empty($value['host']) || empty($value['username']) || empty($value['password'])
                            || empty($value['port']) || empty($value['mailFrom'])
                        )
                    ) {
                        $this->addError($attribute, Yii::t('app', 'Wrong SMTP parameters.'));
                    }
                }
            ],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            ''
        ];
    }

    /**
     * @inheritdoc
     */
    public function defaultValues()
    {
        /** @var app\modules\shop\ShopModule $module */
        $module = $this->getModuleInstance();

        $attributes = array_keys($this->getAttributes(null, ['composerHomeDirectory']));
        foreach ($attributes as $attribute) {
            $this->{$attribute} = $module->{$attribute};
        }
    }

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for web only.
     *
     * @return array
     */
    public function webApplicationAttributes()
    {
        $attributes = $this->getAttributes(null, ['composerHomeDirectory', 'emailConfig']);
        return [
            'modules' => [
                'core' => $attributes,
            ],

        ];
    }

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for console only.
     *
     * @return array
     */
    public function consoleApplicationAttributes()
    {
        return [];
    }

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for web and console.
     *
     * @return array
     */
    public function commonApplicationAttributes()
    {
        return [
            'components' => [
                'updateHelper' => [
                    'composerHomeDirectory' => $this->composerHomeDirectory,
                ]
            ],
            'modules' => [
                'core' => [
                    'emailConfig' => $this->emailConfig,
                ],
            ],
        ];
    }

    /**
     * Returns array of key=>values for configuration.
     *
     * @return mixed
     */
    public function keyValueAttributes()
    {
        return [];
    }

    /**
     * Returns array of aliases that should be set in common config
     * @return array
     */
    public function aliases()
    {
        return [
            '@core' => dirname(__FILE__) . '/../',
        ];
    }
}
