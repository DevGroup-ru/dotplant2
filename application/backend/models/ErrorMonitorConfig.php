<?php

namespace app\backend\models;

use app\models\Config;
use Yii;
use yii\base\Model;

/**
 * Class ErrorMonitorConfig
 * @package app\backend\models
 * @property boolean $errorMonitorEnabled;
 * @property boolean $emailNotifyEnabled;
 * @property string $devmail;
 * @property string $notifyOnlyHttpCodes;
 * @property integer $numberElementsToStore;
 * @property boolean $immediateNotice;
 * @property integer $immediateNoticeLimitPerUrl;
 * @property string $httpCodesForImmediateNotify;
 *
 */
class ErrorMonitorConfig extends Model
{
    public $errorMonitorEnabled;
    public $emailNotifyEnabled;
    public $devmail;
    public $notifyOnlyHttpCodes;
    public $numberElementsToStore;
    public $immediateNotice;
    public $immediateNoticeLimitPerUrl;
    public $httpCodesForImmediateNotify;

    public function __construct()
    {
        $config = new Config();

        $this->errorMonitorEnabled = $config->getValue('errorMonitor');
        $this->emailNotifyEnabled = $config->getValue('errorMonitor.emailNotifyEnabled');
        $this->devmail = $config->getValue('errorMonitor.devmail');
        $this->notifyOnlyHttpCodes = $config->getValue('errorMonitor.notifyOnlyHttpCodes');
        $this->numberElementsToStore = $config->getValue('errorMonitor.numberElementsToStore');
        $this->immediateNotice = $config->getValue('errorMonitor.immediateNotice');
        $this->immediateNoticeLimitPerUrl = $config->getValue('errorMonitor.immediateNoticeLimitPerUrl');
        $this->httpCodesForImmediateNotify = $config->getValue('errorMonitor.httpCodesForImmediateNotify');
    }

    public function rules()
    {
        return [
            [['errorMonitorEnabled','emailNotifyEnabled', 'immediateNotice'], 'integer', 'min' => 0, 'max' => 1],
            [['devmail'], 'string'],
            [['notifyOnlyHttpCodes', 'httpCodesForImmediateNotify'], 'string'],
            [['numberElementsToStore', 'immediateNoticeLimitPerUrl'], 'integer']
        ];
    }

    public function attributeLabels()
    {
        return [
            'errorMonitorEnabled' => Yii::t('app', 'Error Monitor is enabled'),
            'emailNotifyEnabled' => Yii::t('app', 'Notification enabled'),
            'devmail' => Yii::t('app', 'Developers e-mail'),
            'notifyOnlyHttpCodes' => Yii::t('app', 'Notification for codes'),
            'numberElementsToStore' => Yii::t('app', 'Number elements to store'),
            'immediateNotice' => Yii::t('app', 'Immediate notify'),
            'immediateNoticeLimitPerUrl' => Yii::t('app', 'immediate notify limit (per URL)'),
            'httpCodesForImmediateNotify' => Yii::t('app', 'HTTP codes for immediate notify')
        ];
    }

    public function saveConfig()
    {
        $config = new Config();

        $conf = $config->findOne(['path' => 'errorMonitor']);
        $conf->value = $this->errorMonitorEnabled;
        $conf->save();

        $conf = $config->findOne(['path' => 'errorMonitor.emailNotifyEnabled']);
        $conf->value = $this->emailNotifyEnabled;
        $conf->save();

        $conf = $config->findOne(['path' => 'errorMonitor.devmail']);
        $conf->value = $this->devmail;
        $conf->save();

        $conf = $config->findOne(['path' => 'errorMonitor.notifyOnlyHttpCodes']);
        $conf->value = $this->notifyOnlyHttpCodes;
        $conf->save();

        $conf = $config->findOne(['path' => 'errorMonitor.numberElementsToStore']);
        $conf->value = $this->numberElementsToStore;
        $conf->save();

        $conf = $config->findOne(['path' => 'errorMonitor.immediateNotice']);
        $conf->value = $this->immediateNotice;
        $conf->save();

        $conf = $config->findOne(['path' => 'errorMonitor.immediateNoticeLimitPerUrl']);
        $conf->value = $this->immediateNoticeLimitPerUrl;
        $conf->save();

        $conf = $config->findOne(['path' => 'errorMonitor.httpCodesForImmediateNotify']);
        $conf->value = $this->httpCodesForImmediateNotify;
        $conf->save();
    }
}
