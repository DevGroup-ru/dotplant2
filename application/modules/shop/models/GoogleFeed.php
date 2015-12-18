<?php

namespace app\modules\shop\models;


use app\modules\config\components\ConfigurationSaveEvent;
use app\modules\config\helpers\ConfigurationUpdater;
use app\modules\config\models\Configurable;
use app\modules\shop\ShopModule;
use yii;
use yii\base\Model;

class GoogleFeed extends Model
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
                    'shop_host',
                    'shop_name',
                    'shop_description',
                    'shop_main_currency',
                    'shop_delivery_price',
                    'item_delivery_cost',
                    'feed_file_name',
                    'feed_handlers'
                ],
                'required'
            ],
            [['item_delivery_cost'], 'integer'],
            [['shop_name',], 'string', 'length' => [1, 255]],
            [$this->getOfferElements(), 'safe']
        ];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return array_keys($this->attrStorage);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'shop_host' => Yii::t('app', 'Shop host'),
            'shop_title' => Yii::t('app', 'Shop title'),
            'shop_description' => Yii::t('app', 'Shop description'),
            'shop_main_currency' => Yii::t('app', 'Main currency'),
            'shop_delivery_price' => Yii::t('app', 'Shop delivery price'),
            'item_price' => Yii::t('app', 'Price'),
            'item_condition' => Yii::t('app', 'Condition'),
            'item_title' => Yii::t('app', 'Title'),
            'item_description' => Yii::t('app', 'Description'),
            'feed_handlers' => Yii::t('app', 'Handlers'),
            'feed_file_name' => Yii::t('app', 'File Name'),
            'item_gtin' => Yii::t('app', 'Google gtin'),
            'item_brand' => Yii::t('app', 'Brand'),
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
            'shop_host' => 'http://localhost.dev',
            'shop_name' => 'shop name',
            'shop_description' => 'shop description',
            'shop_delivery_price' => 'GB:Standard:100 GBP',
            'shop_main_currency' => 0,
            'item_price' => ['type' => 'field', 'key' => 'price'],
            'item_condition' => 'new',
            'item_title' => ['type' => 'field', 'key' => 'name'],
            'item_description' => ['type' => 'field', 'key' => 'announce'],
            'item_gtin' => ['type' => 'field', 'key' => 'sku'],
            'item_google_product_category' => ['type' => 'field'],
            'item_mpn' => ['type' => 'field'],
            'item_brand' => ['type' => 'field', 'key' => 'name'],
            'item_delivery_cost' => 0,
            'feed_handlers' => yii\helpers\Json::encode([
                'app\modules\shop\components\GoogleMerchants\DefaultHandler'
            ]),
            'feed_file_name' => 'feed.xml',
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
            'item_price',
            'item_title',
            'item_description',
            'item_gtin',
            'item_mpn',
            'item_google_product_category',
            'item_brand'
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
            function (ConfigurationSaveEvent $event) use ($config) {
                /** @var ConfigConfigurationModel $model */
                $model = $event->configurableModel;
                $model->googleFeedConfig = $config;
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
        if (empty($module->googleFeedConfig)) {
            return false;
        }
        $this->attrStorage = $module->googleFeedConfig;

        return static::validate();
    }
}