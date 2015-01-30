<?php

namespace app\backend\controllers;

use app\models\Config;
use app\models\Product;
use kartik\widgets\Select2;
use Yii;
use app\models\Category;
use yii\base\Controller;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\web\Response;

class YmlController extends Controller
{
    public function actionIndex()
    {
        header('content-type: application/xml');

        $document = $this->buidDocument();
        echo $document->saveXML();

        return;
    }

    public function actionSettings()
    {
        if (!empty($_POST['yml'])) {
            $yml = $_POST['yml'];


            $config = Config::find()->where(['key' => 'show_all_properties'])->one();
            if ($config) {
                if (isset($yml['show_all_properties'])) {
                    $config->value = "1";
                    unset($yml['show_all_properties']);
                } else {
                    $config->value = "0";
                }

                $config->update();
            }

            foreach ($yml as $key => $value) {
                $config = Config::find()->where(['key' => $key])->one();
                if ($config) {
                    $config->value = $value;
                    $config->update();
                }
            }
        }

        return $this->render('settings',
            [
                'main_currency' => Config::getValue("yml.main_currency"),
                'show_all_properties' => Config::getValue("yml.show_all_properties"),
                'default_offer_type' => Config::getValue("yml.default_offer_type")
            ]
        );
    }

    private function buidDocument()
    {
        $document = new \DOMDocument("1.0", "windows-1251");
        $ymlCatalog = $this->buildYmlCatalog($document);

        $document->appendChild($ymlCatalog);

        return $document;
    }

    /**
     * @param \DOMDocument $doc
     * @return mixed
     */
    private function buildYmlCatalog($doc)
    {
        $yml_catalog = $doc->createElement('yml_catalog');
        $yml_catalog->appendChild($this->buildShop($doc));;
        return $yml_catalog;
    }

    /**
     * @param \DOMDocument $doc
     * @return mixed
     */
    private function buildShop($doc)
    {
        $shop = $doc->createElement('shop');

        // <name>
        $nameConf = Config::getValue('shop.name');
        if (null === $nameConf) {
            throw new InvalidParamException(Yii::t('app', 'Не задано название магазина'));
        }
        $name = $doc->createElement('name', $nameConf);
        $shop->appendChild($name);

        // <company>
        $companyConf = Config::getValue('shop.company');
        if (null === $companyConf) {
            throw new InvalidParamException(Yii::t('app', 'Не задано название компании'));
        }
        $company = $doc->createElement('company', $companyConf);
        $shop->appendChild($company);

        // <url>
        $urlConf = Config::getValue('core.serverName');
        if (null === $nameConf) {
            throw new InvalidParamException(Yii::t('app', 'Не задан URL магазина'));
        }
        $url = $doc->createElement('url', $urlConf);
        $shop->appendChild($url);

        $shop->appendChild($this->buildCurrencies($doc));
        $shop->appendChild($this->buildCategories($doc));
        $shop->appendChild($this->buildOffers($doc));

        return $shop;
    }

    /**
     * @param \DOMDocument $doc
     * @return mixed
     */
    private function buildCurrencies($doc)
    {
        // <currencies>
        $currenciesRoot = $doc->createElement('currencies');

        $exchangeRate = [
            // 'id' => 'rate'
            "RUR" => "1",
            "USD" => "CB",
            "EUR" => "CB",
            "UAH" => "CB",
            "KZT" => "CB",
        ];

        foreach ($exchangeRate as $id => $rate) {
            $currency = $doc->createElement('currency');

            $currency->setAttribute('id', $id);
            $currency->setAttribute('rate', $rate);

            $currenciesRoot->appendChild($currency);
        }

        return $currenciesRoot;
    }

    /**
     * @param \DOMDocument $doc
     * @return \DOMElement
     */
    private function buildCategories($doc)
    {
        $categories = $doc->createElement('categories');

        $allCat = Category::find()->all();

        /** @var Category $cat */
        foreach ($allCat as $cat) {
            $catElement = $doc->createElement('category', $cat->name);
            $catElement->setAttribute('id', $cat->id);
            if ($cat->parent_id > 0) {
                $catElement->setAttribute('parentId', $cat->parent_id);
            }

            $categories->appendChild($catElement);
        }

        return $categories;
    }

    /**
     * @param \DOMDocument $doc
     * @return \DOMElement
     */
    private function buildOffers($doc)
    {
        $offers = $doc->createElement('offers');
        $products = Product::find()->all();
        /** @var Product $product */
        foreach ($products as $product) {
            $offer = $doc->createElement('offer');

            // общие для всех типов поля
            $url = $doc->createElement('url', '123'); // TODO запилить url
            $price = $doc->createElement('price', $product->price);
            $currencyId = $doc->createElement('currencyId', 'RUR');
            $categoryId = $doc->createElement('categoryId', $product->main_category_id);

            $offer->appendChild($url);
            $offer->appendChild($price);
            $offer->appendChild($currencyId);
            $offer->appendChild($categoryId);


            // не общие
            $fields = [
                'delivery',
                'local_delivery_cost',
                'typePrefix',
                'vendor',
                'vendorCode',
                'model',
                'description',
                'manufacturer_warranty',
                'country_of_origin',
                'author',
                'name',
                'publisher',
                'series',
                'year',
                'ISBN',
                'volume',
                'part',
                'language',
                'page_extent',
                'downloadable',
                'binding',
                'performed_by',
                'performance_type',
                'storage',
                'format',
                'recording_length',
                'title',
                'media',
                'starring',
                'director',
                'originalName',
                'country',
                'worldRegion',
                'region',
                'days',
                'dataTour',
                'hotel_stars',
                'room',
                'meal',
                'included',
                'transport',
                'place',
                'hall',
                'hall_part',
                'date',
                'is_premiere',
                'is_kids'
            ];

            foreach ($fields as $field) {
                if ($val = $product->getPropertyValuesByKey($field)) {
                    $$field = $doc->createElement($field, $val);
                    $offer->appendChild($$field);
                }
            }


            if ($offerType = $product->getPropertyValuesByKey('offerType')) {
                switch($offerType) {
                    case 'vendor.model':
                        if (get_class($delivery) === "DOMElement") { $offer->appendChild($delivery); }
                        if (get_class($local_delivery_cost) === "DOMElement") { $offer->appendChild($local_delivery_cost); }
                        if (get_class($typePrefix) === "DOMElement") { $offer->appendChild($typePrefix); }
                        if (get_class($vendor) === "DOMElement") { $offer->appendChild($vendor); }
                        if (get_class($vendorCode) === "DOMElement") { $offer->appendChild($vendorCode); }
                        if (get_class($model) === "DOMElement") { $offer->appendChild($model); }
                        if (get_class($description) === "DOMElement") { $offer->appendChild($description); }
                        if (get_class($manufacturer_warranty) === "DOMElement") { $offer->appendChild($manufacturer_warranty); }
                        if (get_class($country_of_origin) === "DOMElement") { $offer->appendChild($country_of_origin); }
                        // TODO в vendor.model можно добавлять дополнительные параметры <param name="Вес" unit="кг">2.73</param> ...
                        break;
                    case 'book':
                        if (get_class($delivery) === "DOMElement") { $offer->appendChild($delivery); }
                        if (get_class($local_delivery_cost) === "DOMElement") { $offer->appendChild($local_delivery_cost); }
                        if (get_class($description) === "DOMElement") { $offer->appendChild($description); }
                        if (get_class($author) === "DOMElement") { $offer->appendChild($author); }
                        if (get_class($name) === "DOMElement") { $offer->appendChild($name); }
                        if (get_class($publisher) === "DOMElement") { $offer->appendChild($publisher); }
                        if (get_class($series) === "DOMElement") { $offer->appendChild($series); }
                        if (get_class($year) === "DOMElement") { $offer->appendChild($year); }
                        if (get_class($ISBN) === "DOMElement") { $offer->appendChild($ISBN); }
                        if (get_class($volume) === "DOMElement") { $offer->appendChild($volume); }
                        if (get_class($part) === "DOMElement") { $offer->appendChild($part); }
                        if (get_class($language) === "DOMElement") { $offer->appendChild($language); }
                        if (get_class($page_extent) === "DOMElement") { $offer->appendChild($page_extent); }
                        if (get_class($downloadable) === "DOMElement") { $offer->appendChild($downloadable); }
                        break;
                    case 'audiobook':
                        if (get_class($description) === "DOMElement") { $offer->appendChild($description); }
                        if (get_class($author) === "DOMElement") { $offer->appendChild($author); }
                        if (get_class($name) === "DOMElement") { $offer->appendChild($name); }
                        if (get_class($publisher) === "DOMElement") { $offer->appendChild($publisher); }
                        if (get_class($year) === "DOMElement") { $offer->appendChild($year); }
                        if (get_class($ISBN) === "DOMElement") { $offer->appendChild($ISBN); }
                        if (get_class($language) === "DOMElement") { $offer->appendChild($language); }
                        if (get_class($binding) === "DOMElement") { $offer->appendChild($binding); }
                        if (get_class($downloadable) === "DOMElement") { $offer->appendChild($downloadable); }
                        if (get_class($performed_by) === "DOMElement") { $offer->appendChild($performed_by); }
                        if (get_class($performance_type) === "DOMElement") { $offer->appendChild($performance_type); }
                        if (get_class($storage) === "DOMElement") { $offer->appendChild($storage); }
                        if (get_class($format) === "DOMElement") { $offer->appendChild($format); }
                        if (get_class($recording_length) === "DOMElement") { $offer->appendChild($recording_length); }
                        break;
                    case 'artist.title':
                        if (get_class($delivery) === "DOMElement") { $offer->appendChild($delivery); }
                        if (get_class($description) === "DOMElement") { $offer->appendChild($description); }
                        if (get_class($year) === "DOMElement") { $offer->appendChild($year); }
                        if (get_class($title) === "DOMElement") { $offer->appendChild($title); }
                        if (get_class($media) === "DOMElement") { $offer->appendChild($media); }
                        if (get_class($starring) === "DOMElement") { $offer->appendChild($starring); }
                        if (get_class($director) === "DOMElement") { $offer->appendChild($director); }
                        if (get_class($originalName) === "DOMElement") { $offer->appendChild($originalName); }
                        if (get_class($country) === "DOMElement") { $offer->appendChild($country); }
                        break;
                    case 'tour':
                        if (get_class($delivery) === "DOMElement") { $offer->appendChild($delivery); }
                        if (get_class($local_delivery_cost) === "DOMElement") { $offer->appendChild($local_delivery_cost); }
                        if (get_class($description) === "DOMElement") { $offer->appendChild($description); }
                        if (get_class($name) === "DOMElement") { $offer->appendChild($name); }
                        if (get_class($country) === "DOMElement") { $offer->appendChild($country); }
                        if (get_class($worldRegion) === "DOMElement") { $offer->appendChild($worldRegion); }
                        if (get_class($region) === "DOMElement") { $offer->appendChild($region); }
                        if (get_class($days) === "DOMElement") { $offer->appendChild($days); }
                        if (get_class($dataTour) === "DOMElement") { $offer->appendChild($dataTour); }
                        if (get_class($hotel_stars) === "DOMElement") { $offer->appendChild($hotel_stars); }
                        if (get_class($room) === "DOMElement") { $offer->appendChild($room); }
                        if (get_class($meal) === "DOMElement") { $offer->appendChild($meal); }
                        if (get_class($included) === "DOMElement") { $offer->appendChild($included); }
                        if (get_class($transport) === "DOMElement") { $offer->appendChild($transport); }
                        break;
                    case 'event-ticket':
                        if (get_class($delivery) === "DOMElement") { $offer->appendChild($delivery); }
                        if (get_class($local_delivery_cost) === "DOMElement") { $offer->appendChild($local_delivery_cost); }
                        if (get_class($description) === "DOMElement") { $offer->appendChild($description); }
                        if (get_class($name) === "DOMElement") { $offer->appendChild($name); }
                        if (get_class($place) === "DOMElement") { $offer->appendChild($place); }
                        if (get_class($hall) === "DOMElement") { $offer->appendChild($hall); } // todo + <hall plan="url плана зала">Большой  зал<hall>
                        if (get_class($hall_part) === "DOMElement") { $offer->appendChild($hall_part); }
                        if (get_class($date) === "DOMElement") { $offer->appendChild($date); }
                        if (get_class($is_premiere) === "DOMElement") { $offer->appendChild($is_premiere); }
                        if (get_class($is_kids) === "DOMElement") { $offer->appendChild($is_kids); }
                        break;
                    default: // 'simplified'
                        if (get_class($delivery) === "DOMElement") { $offer->appendChild($delivery); }
                        if (get_class($local_delivery_cost) === "DOMElement") { $offer->appendChild($local_delivery_cost); }
                        if (get_class($vendor) === "DOMElement") { $offer->appendChild($vendor); }
                        if (get_class($vendorCode) === "DOMElement") { $offer->appendChild($vendorCode); }
                        if (get_class($description) === "DOMElement") { $offer->appendChild($description); }
                        if (get_class($country_of_origin) === "DOMElement") { $offer->appendChild($country_of_origin); }
                        if (get_class($name) === "DOMElement") { $offer->appendChild($name); }
                }
            }

            $offers->appendChild($offer);
        }

        return $offers;
    }
}
