<?php

namespace app\modules\shop\components\GoogleMerchants;

use app\modules\shop\helpers\CurrencyHelper;
use app\modules\shop\models\Currency;
use app\modules\shop\models\Product;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\VarDumper;

/**
 * Class GoogleMerchants
 * This implementation uses a rss 2.0 template
 * @package app\modules\shop\components\GoogleMerchants
 */
class GoogleMerchants
{
    protected $host = 'https://english-brands.ru';
    protected $title = 'Site title';
    protected $description = 'Site description';
    protected $mainCurrency;

    protected function getHeader()
    {
        return <<<HEADER
<?xml version="1.0"?>
<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">
	<channel>
		<title>{$this->title}</title>
		<link>{$this->host}</link>
		<description>{$this->description}</description>

HEADER;
    }

    protected function getFooter()
    {
        return <<<FOOTER
	</channel>
</rss>
FOOTER;

    }

    /**
     * @param Product $product
     * @return string
     */
    protected function getItem($product)
    {
        $url = htmlspecialchars(Url::toRoute(['@product', 'model' => $product]));
        $image = htmlspecialchars($product->image->getOriginalUrl());
        if ($product->unlimited_count === 1) {
            $inStock = 'in stock';
        } else {
            $inStock = 'out of stock';
            foreach ($product->getWarehousesState() as $warehouse) {
                if ($warehouse->in_warehouse > 0) {
                    $inStock = 'in stock';
                    break;
                }
            }
        }
        $price = number_format(
            CurrencyHelper::convertCurrencies($product->price, $product->currency, $this->mainCurrency),
            2,
            '.',
            ''
        ) . ' ' . $this->mainCurrency->iso_code;
        return <<<ITEM
		<item>
			<title>{$product['name']}</title>
			<description>{$product['content']}</description>
			<link>{$this->host}{$url}</link>
			<g:id>{$product['id']}</g:id>
			<g:image_link>{$image}</g:image_link>
			<g:condition>new</g:condition>
			<g:availability>{$inStock}</g:availability>
			<g:price>{$price}</g:price>
			<g:item_group_id>{$product->parent_id}</g:item_group_id>
		</item>

ITEM;

    }

    public function generate()
    {
        $this->mainCurrency = Currency::getMainCurrency();
        $s = $this->getHeader();
        foreach (Product::find()->where(['active' => 1])->limit(2)->all() as $product) {
            $s .= $this->getItem($product);
        }
        $s .= $this->getFooter();
        file_put_contents('/home/fps/feed.xml', $s);
    }
}
