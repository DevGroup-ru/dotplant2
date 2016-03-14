<?php
namespace app\modules\shop\components\yml;

use app\models\ObjectStaticValues;
use app\models\Property;
use app\models\PropertyStaticValues;
use app\modules\shop\events\yml\YmlOffersEvent;
use app\modules\shop\helpers\CurrencyHelper;
use app\modules\shop\models\Category;
use app\modules\shop\models\Currency;
use app\modules\shop\models\Product;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\base\Component;
use app\modules\shop\models\Yml as YmlModel;
use yii\caching\TagDependency;
use yii\db\Query;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;

class Yml extends Component
{
    const PARAM_TYPE_FIELD = 'field';
    const PARAM_TYPE_RELATION = 'relation';
    const USE_GZIP = 1;
    const USE_OFFER_PARAM = 1;
    const USE_ADULT = 1;
    const USE_STORE = 1;
    const USE_PICKUP = 1;
    const USE_DELIVERY = 1;

    const EVENT_PROCESS_OFFER = 'ymlProcessOffer';

    /**
     * @property \app\modules\shop\models\Yml $model
     * @property string $viewFile
     * @property Currency $currency
     */
    protected $model = null;
    protected $viewFile = null;
    protected $currency = null;

    static public $_noImg = '';
    static public $ymlEavProperties = [];
    static public $ymlStaticProperties = [];

    /**
     * @inheritdoc
     */
    public function __construct(YmlModel $model, $config = [])
    {
        $this->model = $model;
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (false === $this->model instanceof YmlModel) {
            return false;
        }

        if (null === $this->viewFile) {
            $this->viewFile = Yii::$app->getModule('shop')->getViewPath() . '/backend-yml/generate/yml.php';
        }

        /** @var YmlModel $config */
        $config = $this->model;
        Yii::$app->urlManager->setHostInfo($config->shop_url);
        $this->currency = CurrencyHelper::findCurrencyByIso($config->currency_id);

        if (static::USE_OFFER_PARAM == $config->offer_param) {
            $this->prepareProperties();
        }

        static::$_noImg = Yii::$app->getModule('image')->noImageSrc;
    }

    /**
     * @return bool
     */
    public function generate()
    {
        if (false === $this->model instanceof YmlModel) {
            return false;
        }

        /** @var \app\modules\shop\models\Yml $yml */
        $config = $this->model;
        /** @var View $view */
        $view = Yii::$app->getView();

        $outputParams = [];
        $outputParams['shop'] = $this->generateSectionShop($config);
        $outputParams['offers'] = [];

        $eventOffer = new YmlOffersEvent();
        /** @var Product $model */
        foreach (Product::find()->where(['active' => 1])->batch() as $offers) {
            $eventOffer
                ->clearHandled()
                ->setOffers(array_reduce($offers, function ($r, $i) use ($config) {
                    if (null !== $o = $this->generateSingleOffer($config, $i)) {
                        $r[] = $o;
                    }
                    return $r;
                }, []));
            $this->trigger(static::EVENT_PROCESS_OFFER, $eventOffer);

            $outputParams['offers'] = array_merge($outputParams['offers'], array_column($eventOffer->getOffers(), 'result'));
        }

        $output = $view->renderFile($this->viewFile, $outputParams);

        $fileName = Yii::getAlias('@webroot') . '/' . $config->general_yml_filename;
        $result = static::USE_GZIP === $config->use_gzip
            ? file_put_contents($fileName . '.gz', gzencode($output), 5)
            : file_put_contents($fileName, $output);

        return false !== $result;
    }

    /**
     * @param YmlModel $config
     * @param string $name
     * @param Product $model
     * @param string $default
     * @return mixed
     */
    public static function getOfferValue(YmlModel $config, $name, Product $model, $default = '')
    {
        $param = $config->$name;

        if (static::PARAM_TYPE_FIELD === $param['type']) {
            $field = $param['key'];
            $result = $model->$field;
        } elseif (static::PARAM_TYPE_RELATION === $param['type']) {
            $rel = call_user_func([$model, $param['key']]);
            $attr = $param['value'];
            $rel = $rel->one();
            if (false === empty($rel)) {
                $result = $rel->$attr;
            }
        }

        return false === empty($result) ? $result : $default;
    }

    /**
     *
     */
    private function prepareProperties()
    {
        $props = Property::getDb()->cache(function ($db) {
            return Property::find()->select([
                'id',
                'name',
                'property_handler_id',
                'key',
                'property_group_id',
                'has_static_values',
                'is_eav',
                'handler_additional_params'
            ])->all();
        }, 86400, new TagDependency(['tags' => [ActiveRecordHelper::getCommonTag(Property::className()),]]));
        foreach ($props as $one) {
            $additionalParams = Json::decode($one['handler_additional_params']);
            if (false === empty($additionalParams['use_in_file'])) {
                if (1 == $one['is_eav'] && false === isset(self::$ymlEavProperties[$one['id']])) {
                    self::$ymlEavProperties[$one['id']] = [
                        'name' => $one['name'],
                        'unit' => empty($additionalParams['unit']) ? '' : $additionalParams['unit'],
                        'key' => $one['key'],
                        'group_id' => $one['property_group_id'],
                        'handler_id' => $one['property_handler_id'],
                    ];
                } elseif (1 == $one['has_static_values'] && false === isset(self::$ymlStaticProperties[$one['id']])) {
                    self::$ymlStaticProperties[$one['id']] = [
                        'name' => $one['name'],
                        'unit' => empty($additionalParams['unit']) ? '' : $additionalParams['unit'],
                    ];
                }

            }
        }
    }

    /**
     * @param Product $model
     * @return array
     */
    public static function getOfferParams(Product $model)
    {
        $params = [];
        $eav = Yii::$app->getDb()->cache(function ($db) use ($model) {
            /**
             * @var \app\models\Object $object
             */
            $object = $model->object;
            return (new Query())
                ->from($object->eav_table_name)
                ->select(Property::tableName() . '.id, ' . $object->eav_table_name . '.value')
                ->innerJoin(
                    Property::tableName(),
                    Property::tableName() . '.property_group_id = ' . $object->eav_table_name . '.property_group_id'
                    . ' AND ' . Property::tableName() . '.key = ' . $object->eav_table_name . '.key'
                )
                ->where([
                    'object_model_id' => $model->id,
                    $object->eav_table_name . '.key' => array_column(static::$ymlEavProperties, 'key'),
                    $object->eav_table_name . '.property_group_id' => array_column(static::$ymlEavProperties, 'group_id'),
                    Property::tableName() . '.id' => array_keys(static::$ymlEavProperties),
                ])
                ->andWhere(['<>','value', ''])
                ->all();
        });
        foreach ($eav as $prop) {
            if (false === isset($prop['id'])) {
                continue ;
            }

            $val = htmlspecialchars($prop['value']);
            switch (static::$ymlEavProperties[$prop['id']]['handler_id']) {
                case 3 :
                    $val = $val == 1 ? Yii::t('yii', 'Yes') : Yii::t('yii', 'No');
                    break;
            }
            $_key = static::$ymlEavProperties[$prop['id']]['name'];
            $params[htmlspecialchars(trim($_key))] = [
                'unit' => false === empty(static::$ymlEavProperties[$prop['id']]['unit'])
                    ? htmlspecialchars(trim(static::$ymlEavProperties[$prop['id']]['unit']))
                    : null,
                'value' => $val,
            ];
        }

        $psv = Yii::$app->getDb()->cache(function ($db) use ($model) {
            return  (new Query())
                ->from(PropertyStaticValues::tableName())
                ->innerJoin(
                    ObjectStaticValues::tableName(),
                    ObjectStaticValues::tableName() . '.property_static_value_id = ' . PropertyStaticValues::tableName() . '.id'
                )
                ->where([
                    'object_model_id' => $model->id,
                    'object_id' => $model->object->id,
                    'property_id' => array_keys(static::$ymlStaticProperties)
                ])
                ->andWhere(['<>','value', ''])
                ->all();
        });
        foreach ($psv as $prop) {
            if (false === isset($prop['property_id'])) {
                continue ;
            }

            $_key = static::$ymlStaticProperties[$prop['property_id']]['name'];
            $params[htmlspecialchars(trim($_key))] = [
                'unit' => false === empty(static::$ymlStaticProperties[$prop['property_id']]['unit'])
                    ? htmlspecialchars(trim(static::$ymlStaticProperties[$prop['property_id']]['unit']))
                    : null,
                'value' => htmlspecialchars(trim($prop['value'])),
            ];
        }

        return $params;
    }

    /**
     * @param YmlModel $config
     * @return array
     */
    private function generateSectionShop(YmlModel $config)
    {
        return [
            'name' => $config->shop_name,
            'company' => $config->shop_company,
            'url' => $config->shop_url,
            'currency' => $this->currency->iso_code,
            'categories' => Category::find()->where(['active' => 1])->asArray(),
            'store' => static::USE_STORE == $config->shop_store ? 'true' : 'false',
            'pickup' => static::USE_PICKUP == $config->shop_pickup ? 'true' : 'false',
            'delivery' => static::USE_DELIVERY == $config->shop_delivery ? 'true' : 'false',
            'local_delivery_cost' => $config->shop_local_delivery_cost,
            'adult' => static::USE_ADULT == $config->shop_adult ? 'true' : 'false',
        ];
    }

    /**
     * @param YmlModel $config
     * @param Product $model
     * @return null|array
     */
    private function generateSingleOffer(YmlModel $config, Product $model)
    {
        $result = $this->offerSimplified($config, $model);
        if (null === $result) {
            return null;
        }

        return [
            'model' => $model,
            'result' => $result,
        ];
    }

    /**
     * @param YmlModel $config
     * @param Product $model
     * @return OfferTag|null
     */
    private function offerSimplified(YmlModel $config, Product $model)
    {
        $offer = new OfferTag('offer', [], ['id' => $model->id, 'available' => 'true',]);

        $price = static::getOfferValue($config, 'offer_price', $model, 0);
        $price = CurrencyHelper::convertCurrencies($price, $model->currency, $this->currency);
        if ($price <= 0 || $price >= 1000000000) {
            return null;
        }

        $values = [];

        $name = static::getOfferValue($config, 'offer_name', $model, null);
        if (true === empty($name)) {
            return null;
        }
        if (mb_strlen($name) > 120) {
            $name = mb_substr($name, 0, 120);
            $name = mb_substr($name, 0, mb_strrpos($name, ' '));
        }
        $values[] = new OfferTag('name', htmlspecialchars(trim(strip_tags($name))));
        $values[] = new OfferTag('price', $price);
        $values[] = new OfferTag('currencyId', $this->currency->iso_code);

        /** @var Category $category */
        if (null === $category = $model->category) {
            return null;
        }
        $values[] = new OfferTag('categoryId', $category->id);
        $values[] = new OfferTag('url', Url::toRoute([
            '@product',
            'model' => $model,
            'category_group_id' => $category->category_group_id,
        ], true));

        $picture = static::getOfferValue($config, 'offer_picture', $model, static::$_noImg);
        if (static::$_noImg !== $picture) {
            $picture = htmlspecialchars(trim($picture, '/'));
            $picture = implode('/', array_map('rawurlencode', explode('/', $picture)));
            $values[] = new OfferTag('picture', trim($config->shop_url, '/') . '/' . $picture);
        }

        $description = static::getOfferValue($config, 'offer_description', $model, null);

        if (false === empty($description)) {
            $description = preg_replace("/([\r\n\t])/", ' ', $description);
            $description = preg_replace("/[ ]{2,}/", '', $description);
            $description = htmlspecialchars(trim(strip_tags($description)));

            if (mb_strlen($description) > 175) {
                $description = mb_substr($description, 0, 175);
                $description = mb_substr($description, 0, mb_strrpos($description, ' '));
            }

            $values[] = new OfferTag('description', $description);
        }

        if (static::USE_OFFER_PARAM == $config->offer_param) {
            foreach (static::getOfferParams($model) as $k => $v) {
                $values[] = new OfferTag('param', $v['value'], ['name' => $k, 'unit' => $v['unit']]);
            }
        }

        return $offer->setValue($values);
    }
}
