<?php

namespace app\modules\shop\components\GoogleMerchants;

use app\modules\shop\models\Currency;
use app\modules\shop\models\Product;
use Yii;
use yii\base\Component;
use yii\helpers\Html;


/**
 * Class GoogleMerchants
 * This implementation uses a rss 2.0 template
 * @package app\modules\shop\components\GoogleMerchants
 */
class GoogleMerchants extends Component
{

    public $host = 'https://localhost';
    public $title = 'Site title';
    public $description = 'Site description';
    public $fileName = 'feed.xml';

    public $handlers = [
        'app\modules\shop\components\GoogleMerchants\DefaultHandler'
    ];
    public $brand_property = 'manufacturer';

    public $mainCurrency = null;
    protected $data = [];

    const MODIFICATION_DATA = 'modification_data';

    public function init()
    {
        if ($this->mainCurrency === null) {
            $this->mainCurrency = Currency::getMainCurrency();
        }
        foreach ($this->handlers as $handler) {
            if (is_subclass_of($handler, ModificationDataInterface::class)) {
                $this->on(self::MODIFICATION_DATA, [$handler, 'processData']);
            }
        }
        return parent::init();
    }


    public function saveFeedInFs()
    {
        file_put_contents(Yii::getAlias('@webroot/' . $this->fileName), $this->generateFeedByArray($this->getData()));
    }

    public function getData()
    {
        if ($this->data === []) {
            $event = new ModificationDataEvent();
            $query = Product::find()
                ->where(['active' => 1])
                ->limit(3);

            foreach ($query->each() as $product) {
                $event->model = $product;
                $this->trigger(self::MODIFICATION_DATA, $event);
                $this->data[] = $event->data;
            }
        }
        return $this->data;
    }

    public function generateFeedByArray($data)
    {
        $result = "<?xml version=\"1.0\"?><rss xmlns:g=\"http://base.google.com/ns/1.0\" version=\"2.0\">";
        $result .= $this->generateItem(
            'channel',
            $this->generateItem('title', $this->title) .
            $this->generateItem('link', $this->host) .
            $this->generateItem('description', $this->description) .
            $this->generateItems($data)
        );
        $result .= "</rss>";
        return $result;
    }

    protected function generateItems($data)
    {
        $result = "";
        foreach ($data as $item) {
            $result .= $this->generateItem('item', $item);
        }
        return $result;
    }

    protected function generateItem($tag, $data)
    {
        $content = "";
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $content .= $this->generateItem($key, $value);
            }
        } else {
            $content = $data;
        }
        return static::tag($tag, $content);
    }

    protected static function tag($name, $content = '', $options = [])
    {
        return "<$name" . Html::renderTagAttributes($options) . '>' . $content . "</$name>";
    }

}
