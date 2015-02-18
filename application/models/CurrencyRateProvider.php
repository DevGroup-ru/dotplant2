<?php

namespace app\models;

use Ivory\HttpAdapter\HttpAdapterInterface;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Model for storing name and params of Currency Rate Providers
 * Currency rate provider class should be an implementation of Swap\ProviderInterface
 *
 * @property integer $id
 * @property string $name
 * @property string $class_name
 * @property string $params
 */
class CurrencyRateProvider extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%currency_rate_provider}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['params'], 'string'],
            [['name', 'class_name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'class_name' => Yii::t('app', 'Class Name'),
            'params' => Yii::t('app', 'Params'),
        ];
    }

    /**
     * Returns Swap provider instance for currency rate gathering
     * @param HttpAdapterInterface $httpAdapter
     * @return \Swap\ProviderInterface
     */
    public function getImplementationInstance(HttpAdapterInterface $httpAdapter)
    {
        $reflection_class = new \ReflectionClass($this->class_name);
        $params = ['httpAdapter'=>$httpAdapter];
        if (!empty($this->params)){
            $params = ArrayHelper::merge($params, Json::decode($this->params));
        }

        return $reflection_class->newInstanceArgs($params);
    }
}
