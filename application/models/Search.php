<?php

namespace app\models;

use app\components\search\SearchEvent;
use app\components\search\SearchInterface;
use app\modules\core\CoreModule;
use Yii;
use yii\base\Model;
use yii\db\Query;

class Search extends Model
{

    const QUERY_SEARCH_PRODUCTS_BY_DESCRIPTION = 'query_search_products_by_description';
    const QUERY_SEARCH_PRODUCTS_BY_PROPERTY = 'query_search_products_by_property';
    const QUERY_SEARCH_PAGES_BY_DESCRIPTION = 'query_search_pages_by_description';

    public $q = '';

    public function attributeLabels()
    {
        return [
            'q' => \Yii::t('app', 'Do Search') . '...'
        ];
    }

    public function rules()
    {
        return [
            ['q', 'string', 'min' => 3, 'skipOnEmpty' => false],
        ];
    }

    public function searchByKey($key)
    {

        $result = [];
        /**
         * @var $module CoreModule;
         */
        $module = Yii::$app->getModule('core');

        if (!empty($module->searchHandlers[$key])) {
            foreach ($module->searchHandlers[$key] as $class) {
                if (is_subclass_of($class, SearchInterface::class)) {
                    $this->on($key, [$class, 'editQuery']);
                }
            }
            $event = new SearchEvent();
            $event->q = $this->q;
            $event->activeQuery = (new Query());
            $this->trigger($key, $event);
            $result = $event->getAll();
        }

        return $result;

    }


    public function searchProductsByProperty()
    {
        return $this->searchByKey(self::QUERY_SEARCH_PRODUCTS_BY_PROPERTY);
    }

    public function searchProductsByDescription()
    {
        return $this->searchByKey(self::QUERY_SEARCH_PRODUCTS_BY_DESCRIPTION);
    }

    public function searchPagesByDescription()
    {
        return $this->searchByKey(self::QUERY_SEARCH_PAGES_BY_DESCRIPTION);
    }
}
