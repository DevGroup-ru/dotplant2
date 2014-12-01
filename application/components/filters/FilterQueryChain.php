<?php

namespace app\components\filters;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;

class FilterQueryChain extends Component
{
    public $filters = [];

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $objects = [];
        foreach ($this->filters as $filterConfig) {
            $filterObject = Yii::createObject($filterConfig);
            if ($filterObject instanceof FilterQueryInterface === false) {
                throw new InvalidConfigException(
                    "Filter class should implement FilterQueryInterface"
                );
            }
            $objects[] = $filterObject;
        }
        $this->filters = $objects;
    }

    /**
     * Passes $query through all defined filters
     * @param ActiveQuery $query
     * @return ActiveQuery
     */
    public function filter(ActiveQuery $query, &$cacheKeyAppend)
    {
        foreach ($this->filters as $filter) {
            $query = $filter->filter($query, $cacheKeyAppend);
        }
        return $query;
    }
}
