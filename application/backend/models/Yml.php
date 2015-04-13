<?php

namespace app\backend\models;

use app\models\Config;
use yii;
use yii\base\Model;

class Yml extends Model
{
    private $attrStorage = [];

    public function rules()
    {
        $rules = [
            [
                [
                    'shop_name',
                    'shop_company',
                    'shop_url',
                    'currency_id',
                    'offer_name',
                    'offer_price',
                    'offer_category',
                    'offer_picture',
                    'general_yml_filename',
                ],
                'required'
            ],
            [['shop_store', 'shop_pickup', 'shop_delivery', 'shop_adult', ], 'integer'],
            ['shop_url', 'url', 'defaultScheme' => 'http'],
            [['shop_name', 'shop_company'], 'string', 'length' => [1, 255]],
        ];

        return $rules;
    }

    public function attributes()
    {
        return [
            'general_yml_type',
            'general_yml_filename',
            'shop_name',
            'shop_company',
            'shop_url',
            'shop_local_delivery_cost',
            'shop_store',
            'shop_pickup',
            'shop_delivery',
            'shop_adult',
            'currency_id',
            'offer_name',
            'offer_price',
            'offer_category',
            'offer_picture',
            'offer_description',
        ];
    }

    public function attributeLabels()
    {
        return [
            'general_yml_type' => Yii::t('app', 'YML type by default'),
            'general_yml_filename' => Yii::t('app', 'YML filename'),
            'shop_name' => Yii::t('app', 'Shop name'),
            'shop_company' => Yii::t('app', 'Company'),
            'shop_url' => Yii::t('app', 'Shop URL'),
            'shop_local_delivery_cost' => Yii::t('app', 'Local delivery cost'),
            'shop_store' => Yii::t('app', 'Offer store'),
            'shop_pickup' => Yii::t('app', 'Offer pickup'),
            'shop_delivery' => Yii::t('app', 'Offer delivery'),
            'shop_adult' => Yii::t('app', 'Offer adult'),
            'currency_id' => Yii::t('app', 'Main currency'),
            'offer_name' => Yii::t('app', 'Offer name'),
            'offer_price' => Yii::t('app', 'Offer price'),
            'offer_picture' => Yii::t('app', 'Offer picture'),
            'offer_description' => Yii::t('app', 'Offer description'),
            'offer_param' => Yii::t('app', 'To show all properties of a product in YML'),
        ];
    }

    public function scenarios()
    {
        return parent::scenarios();
    }

    public function init()
    {
        parent::init();

        $this->attrStorage = [
            'general_yml_type' => 'simplified',
            'general_yml_filename' => 'file.yml',
            'shop_name' => Yii::t('app', 'Shop name'),
            'shop_company' => Yii::t('app', 'Company'),
            'shop_url' => '',
            'shop_local_delivery_cost' => 0,
            'shop_store' => 0,
            'shop_pickup' => 0,
            'shop_delivery' => 0,
            'shop_adult' => 0,
            'currency_id' => 'RUR',
            'offer_name' => ['type' => 'field', 'key' => 'name'],
            'offer_price' => ['type' => 'field', 'key' => 'price'],
            'offer_category' => ['type' => 'field', 'key' => 'main_category_id'],
            'offer_picture' => ['type' => 'relation', 'key' => 'getImage', 'value' => 'image_src'],
            'offer_description' => ['type' => 'field', 'key' => 'content'],
            'offer_param' => 0,
        ];
    }

    public function __get($name)
    {
        if (isset($this->attrStorage[$name])) {
            return $this->attrStorage[$name];
        }

        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        if (is_array($value)) {
            $value = array_map('trim', $value);
        } elseif (is_string($value)) {
            $value = trim($value);
        }

        if (isset($this->attrStorage[$name])) {
            $this->attrStorage[$name] = $value;
            return true;
        }

        return parent::__set($name, $value);
    }

    public function getOfferElements()
    {
        return [
            'offer_name',
            'offer_price',
            'offer_category',
            'offer_picture',
            'offer_description',
        ];
    }

    public function saveConfig()
    {
        $value = yii\helpers\Json::encode($this->attrStorage);
        return Config::updateValue('shop.yml.config', $value);
    }

    public function loadConfig()
    {
        $value = Config::getValue('shop.yml.config');

        if (empty($value)) {
            return false;
        }

        try {
            $value = yii\helpers\Json::decode($value);
            $this->attrStorage = $value;
        } catch (yii\base\InvalidParamException $e) {
        }

        return static::validate();
    }
}
?>