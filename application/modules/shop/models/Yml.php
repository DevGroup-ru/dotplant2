<?php

namespace app\modules\shop\models;

use app\modules\config\components\ConfigurationSaveEvent;
use app\modules\config\helpers\ConfigurationUpdater;
use app\modules\config\models\Configurable;
use app\modules\shop\ShopModule;
use yii;
use yii\base\Model;

class Yml extends Model
{
    /** @property array $attrStorage */
    private $attrStorage = [];

    /**
     * @inheritdoc
     */
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
            [['shop_store', 'shop_pickup', 'shop_delivery', 'shop_adult', 'offer_param', 'use_gzip'], 'integer'],
            ['shop_url', 'url', 'defaultScheme' => 'http'],
            [['shop_name', 'shop_company'], 'string', 'length' => [1, 255]],
            ['offer_description', 'safe'],
        ];

        return $rules;
    }

    /**
     * @inheritdoc
     */
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
            'offer_param',
            'shop_adult',
            'currency_id',
            'offer_name',
            'offer_price',
            'offer_category',
            'offer_picture',
            'offer_description',
            'use_gzip'
        ];
    }

    /**
     * @inheritdoc
     */
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
            'offer_category' => Yii::t('app', 'Offer category'),
            'offer_param' => Yii::t('app', 'Import properties to YML'),
            'use_gzip' => Yii::t('app', 'Compress file with gzip. Will store file as "File name".gz'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return parent::scenarios();
    }

    /**
     * @inheritdoc
     */
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
            'offer_picture' => ['type' => 'relation', 'key' => 'getImage', 'value' => 'file'],
            'offer_description' => ['type' => 'field', 'key' => 'content'],
            'offer_param' => 0,
            'use_gzip' => 0,
        ];
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if (isset($this->attrStorage[$name])) {
            return $this->attrStorage[$name];
        }
        if (in_array($name, $this->attributes())) {
            return '';
        }
        return parent::__get($name);
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @return array
     */
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

    /**
     * @return bool
     */
    public function saveConfig()
    {
        /** @var ShopModule $module */
        if (null === $module = Yii::$app->getModule('shop')) {
            return false;
        }
        /** @var Configurable $configurable */
        if (null === $configurable = Configurable::findOne(['module' => $module->id])) {
            return false;
        }
        /** @var ConfigConfigurationModel $configurableModel */
        $configurableModel = $configurable->getConfigurableModel();
        $config = $this->attrStorage;

        yii\base\Event::on(
            $configurableModel::className(),
            $configurableModel->configurationSaveEvent(),
            function (ConfigurationSaveEvent $event) use ($config)
            {
                /** @var ConfigConfigurationModel $model */
                $model = $event->configurableModel;
                $model->ymlConfig = $config;
            }
        );

        $models = [$configurable];
        return ConfigurationUpdater::updateConfiguration($models, false);
    }

    /**
     * @return bool
     */
    public function loadConfig()
    {
        /** @var ShopModule $module */
        if (null === $module = Yii::$app->getModule('shop')) {
            return false;
        }
        if (empty($module->ymlConfig)) {
            return false;
        }
        $this->attrStorage = $module->ymlConfig;

        return static::validate();
    }
}
