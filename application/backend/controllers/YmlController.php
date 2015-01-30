<?php

namespace app\backend\controllers;

use app\models\Config;
use app\models\Product;
use Yii;
use app\models\Category;
use yii\base\Controller;
use yii\base\InvalidParamException;
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
     */
    private function buildOffers($doc)
    {
        $offers = $doc->createElement('offers');

        $products = Product::find()->all();
        /** @var Product $product */
        foreach ($products as $product) {
            $offer = $doc->createElement('offer');

            $url = $doc->createElement('url', '123');
            $price = $doc->createElement('price', $product->price);
            $currencyId = $doc->createElement('currencyId', 'RUR');
            $categoryId = $doc->createElement('categoryId', $product->main_category_id);

            $offer->appendChild($url);
            $offer->appendChild($price);
            $offer->appendChild($currencyId);
            $offer->appendChild($categoryId);

            if ($val = $product->getPropertyValuesByKey('delivery')) {
                $delivery = $doc->createElement('delivery', $val);
                $offer->appendChild($delivery);
            }

            if ($val = $product->getPropertyValuesByKey('local_delivery_cost')) {
                $local_delivery_cost = $doc->createElement('local_delivery_cost', $val);
                $offer->appendChild($local_delivery_cost);
            }

            $typePrefix = $doc->createElement('typePrefix', $product->name);
            $offer->appendChild($typePrefix);

            if ($val = $product->getPropertyValuesByKey('vendor')) {
                $vendor = $doc->createElement('vendor', $val);
                $offer->appendChild($vendor);
            }

            if ($val = $product->getPropertyValuesByKey('vendorCode')) {
                $vendorCode = $doc->createElement('vendorCode', $val);
                $offer->appendChild($vendorCode);
            }

            if ($val = $product->getPropertyValuesByKey('')) {
                $model = $doc->createElement('model', $val);
                $offer->appendChild($model);
            }

            if ($val = $product->getPropertyValuesByKey('description')) {
                $description = $doc->createElement('description', $val);
                $offer->appendChild($description);
            }

            if ($val = $product->getPropertyValuesByKey('manufacturer_warranty')) {
                $manufacturer_warranty = $doc->createElement('manufacturer_warranty', $val);
                $offer->appendChild($manufacturer_warranty);
            }

            if ($val = $product->getPropertyValuesByKey('country_of_origin')) {
                $country_of_origin = $doc->createElement('country_of_origin', $val);
                $offer->appendChild($country_of_origin);
            }

//            $att = $product->getAbstractModel()->attributes();
//            var_dump($att);
            $offers->appendChild($offer);
        }

        return $offers;
    }
}
