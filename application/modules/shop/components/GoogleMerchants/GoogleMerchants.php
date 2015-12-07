<?php

namespace app\modules\shop\components\GoogleMerchants;

use app\modules\shop\models\Currency;
use app\modules\shop\models\GoogleFeed;
use app\modules\shop\models\Product;
use Yii;
use yii\base\Component;
use yii\helpers\Html;
use yii\helpers\Json;


/**
 * Class GoogleMerchants
 * This implementation uses a rss 2.0 template
 * @package app\modules\shop\components\GoogleMerchants
 */
class GoogleMerchants extends Component
{

    protected static $modelSetting;

    public $host = null;
    public $title = null;
    public $description = null;
    public $fileName = null;

    public $handlers = [
        'app\modules\shop\components\GoogleMerchants\DefaultHandler'
    ];
    public $brand_property = 'manufacturer';

    public $mainCurrency = null;
    protected $data = [];

    const MODIFICATION_DATA = 'modification_data';

    public function init()
    {
        self::$modelSetting = new GoogleFeed();
        self::$modelSetting->loadConfig();

        $this->handlers = Json::decode(self::$modelSetting->feed_handlers);

        foreach ($this->handlers as $handler) {
            if (is_subclass_of($handler, ModificationDataInterface::class)) {
                $this->on(self::MODIFICATION_DATA, [$handler, 'processData']);
            }
        }
        parent::init();


        if ($this->mainCurrency === null) {
            $this->mainCurrency = Currency::findOne(['iso_code' => self::$modelSetting->shop_main_currency]);
        }

        if ($this->host === null) {
            $this->host = self::$modelSetting->shop_host;
        }
        if ($this->title === null) {
            $this->title = self::$modelSetting->shop_name;
        }
        if ($this->description === null) {
            $this->description = self::$modelSetting->shop_description;
        }
        if ($this->fileName === null) {
            $this->fileName = self::$modelSetting->feed_file_name;
        }

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
                $this->data[] = $event->dataArray;
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
