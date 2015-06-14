<?php

namespace app\components;

use Yii;
use yii\base\InvalidConfigException;
use yii\widgets\FragmentCache;

class AdvancedFragmentCache extends FragmentCache
{
    /** @var null|ViewElementsGathener|string */
    public $viewElementsGathener = null;

    private $cachedData = null;

    private $startedGathering = false;

    public function init()
    {
        parent::init();
        if ($this->viewElementsGathener === null) {
            throw new InvalidConfigException('ViewElementsGathener should be set');
        }
        if (is_string($this->viewElementsGathener)) {
            $this->viewElementsGathener = Yii::$app->get($this->viewElementsGathener);
        }

    }

    public function startGathering()
    {
        $this->startedGathering = true;
        $this->viewElementsGathener->startGathering($this->getId(), $this->dependency);
    }

    public function run()
    {
        if ($this->startedGathering === true) {
            $this->viewElementsGathener->endGathering();
        }
        parent::run();

    }

    public function getCachedContent()
    {
        if ($this->cachedData === null) {
            $cachedData = $this->viewElementsGathener->getCachedData($this->getId());

            if ($cachedData === false) {
                $this->cachedData = false;
                return false;
            }


            $this->viewElementsGathener->repeatGatheredData($this->view, $cachedData);

            $this->cachedData = parent::getCachedContent();

        }
        return $this->cachedData;
    }
}