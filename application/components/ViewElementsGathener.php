<?php

namespace app\components;

use Yii;
use yii\base\Component;

class ViewElementsGathener extends Component
{
    public $elements = [];
    public $cacheStack = [null,];
    public $cacheStackDependencies = [null,];
    public $currentStackId = null;

    /** @var string|\yii\caching\Cache */
    public $cache = 'cache';

    public $cacheLifetime = 86400;

    public function init()
    {
        parent::init();
        if (is_string($this->cache)) {
            $this->cache = Yii::$app->get($this->cache);
        }
    }

    public function gatherToStack($cacheStackId, $elementType, $arguments)
    {
        if (isset($this->elements[$cacheStackId]) === false) {
            $this->elements[$cacheStackId] = [];
        }
        if (isset($this->elements[$cacheStackId][$elementType]) === false) {
            $this->elements[$cacheStackId][$elementType] = [];
        }
        $this->elements[$cacheStackId][$elementType][] = $arguments;
    }

    public function gather($elementType, $arguments)
    {
        if ($this->currentStackId===null) {
            return;
        }
        Yii::trace("Gather: ".$this->currentStackId." -- ".$elementType);
        $this->gatherToStack($this->currentStackId, $elementType, $arguments);
    }

    public function startGathering($cacheStackId, $dependency = null)
    {
        $this->currentStackId = $cacheStackId;
        $this->elements[$cacheStackId] = [];
        $this->cacheStack[] = $cacheStackId;
        $this->cacheStackDependencies[] = $dependency;
        Yii::trace('Start gathering:' . $cacheStackId);
    }

    public function endGathering()
    {
        array_pop($this->cacheStack);
        $elements = $this->elements[$this->currentStackId];
        Yii::trace('End gathering:' . $this->currentStackId);
        $dependencies = array_pop($this->cacheStackDependencies);

        $this->cache->set(
            $this->getCacheKey(),
            $elements,
            $this->cacheLifetime,
            $dependencies
        );
        
        unset($this->elements[$this->currentStackId]);

        $stack = $this->cacheStack;
        $this->currentStackId = end($stack);


        return $elements;
    }


    public function repeatGatheredData($view, $cachedData = [])
    {
        Yii::trace('Repeat gathered data!');

        foreach ($cachedData as $function => $cached) {
            foreach ($cached as $arguments) {
                call_user_func_array([$view, $function], $arguments);
            }
        }
    }

    public function getCachedData($cacheStackId)
    {
        Yii::trace('Get cached data: ' . $cacheStackId);
        $data = Yii::$app->cache->get('ViewElementsGathener:'.$cacheStackId);

        return $data;
    }

    private function getCacheKey()
    {
        return 'ViewElementsGathener:'.$this->currentStackId;
    }

}