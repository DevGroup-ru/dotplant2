<?php

namespace app\backend\models;

use app\models\Config;
use Yii;
use yii\base\Model;

/**
 * Class NewsletterConfig
 * @package app\backend\models
 *
 * @property integer $isActive
 * @property string $notifyType
 */
class NewsletterConfig extends Model
{
    public static $NOTIFY_TYPE_IMMEDIATE = 0;
    public static $NOTIFY_TYPE_SHEDULE = 1;

    public $isActive;
    public $notifyType;

    public function __construct()
    {
        $config = new Config();

        $this->isActive = $config->getValue('newsletter.newsletterEnabled');
        if (null === $this->isActive) {
            $this->isActive = 0;
        }
        $this->notifyType = $config->getValue('newsletter.newsletterNotifyType');
        if (null === $this->notifyType) {
            $this->notifyType = self::$NOTIFY_TYPE_IMMEDIATE;
        }
    }

    public function rules()
    {
        return [
            [['isActive'], 'integer', 'min' => 0, 'max' => 1],
            [['notifyType'], 'string']
        ];
    }

    public function attributeLabels()
    {
        return [
            'isActive' => Yii::t('app', 'Is active'),
            'notifyType' => Yii::t('app', 'Notify type')
        ];
    }

    public function saveConfig()
    {
        $config = new Config();

        $conf = $config->findOne(['path' => 'newsletter.newsletterEnabled']);
        $conf->value = $this->isActive;
        $conf->save();

        $conf = $config->findOne(['path' => 'newsletter.newsletterNotifyType']);
        $conf->value = $this->notifyType;
        $conf->save();
    }

    public static function getNotifyTypeList()
    {
        return [
            self::$NOTIFY_TYPE_IMMEDIATE => 'Immediate',
            self::$NOTIFY_TYPE_SHEDULE => 'Shedule'
        ];
    }
}
