<?php

namespace app\backend\widgets\flushcache;

use yii\base\Widget;
use yii\helpers\Url;

class FlushCacheButton extends Widget
{
    public $url = '';
    public $htmlOptions = [];

    public $onSuccess = "''";
    public $onError = "''";

    public $label = "Flush cache";

    public function init()
    {
        parent::init();
        if (!$this->url) {
            $this->url = Url::to(['flush-cache']);
        }
        if (!isset($this->htmlOptions['class'])) {
            $this->htmlOptions['class'] = 'btn btn-warning';
        }
        $this->htmlOptions['id'] = 'flush_cache';
    }

    public function run()
    {
        $view = $this->getView();
        FlushCacheButtonAsset::register($view);

        $view->registerJs(
            "jQuery('#{$this->htmlOptions['id']}').flushCache('{$this->url}', {$this->onSuccess}, {$this->onError});"
        );

        return $this->render('flushCacheButton', ['htmlOptions' => $this->htmlOptions, 'label'=>$this->label]);
    }
}
