<?php

namespace app\components;

use Yii;
use yii\base\InvalidConfigException;
use yii\widgets\FragmentCache;

class AdvancedFragmentCache extends FragmentCache
{
    /** @var null|ViewElementsGathener|string */
    public $viewElementsGathener = null;

    public function init()
    {
        parent::init();
        if ($this->viewElementsGathener === null) {
            throw new InvalidConfigException('ViewElementsGathener should be set');
        }
        if (is_string($this->viewElementsGathener)) {
            $this->viewElementsGathener = Yii::$app->get($this->viewElementsGathener);
        }
        $this->viewElementsGathener->startGathering($this->getId());
    }

    public function run()
    {
        parent::run();
        $this->viewElementsGathener->endGathering();
    }

    public function getCachedContent()
    {
        $cachedData = $this->viewElementsGathener->getCachedData($this->getId());

        if ($cachedData === false) {
            return false;
        }


        $this->viewElementsGathener->repeatGatheredData($this->view, $cachedData);

        $content = parent::getCachedContent();

        return $content;
    }
}