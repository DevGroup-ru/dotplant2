<?php

namespace app\models;

use app\components\search\SearchEvent;
use app\components\search\SearchInterface;
use app\modules\page\models\Page;
use Yii;
use yii\base\Model;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class Search extends Model
{

    const QUERY_SEARCH_BY_DESCRIPTION = 'query_search_by_description';
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

    public function searchProductsByProperty()
    {
        $result = (new Query())
            ->select('`id`')
            ->from(PropertyStaticValues::tableName())
            ->where('`name` LIKE :q')
            ->addParams([':q' => '%' . $this->q . '%'])
            ->all();
        $result = (new Query())
            ->select('`object_model_id`')
            ->distinct(true)
            ->from(ObjectStaticValues::tableName())
            ->where('`object_id` = :objectId')
            ->addParams([':objectId' => 1])
            ->andWhere(['in', '`property_static_value_id`', ArrayHelper::getColumn($result, 'id')])
            ->all();
        return ArrayHelper::getColumn($result, 'object_model_id');
    }

    public function searchProductsByDescription()
    {

        $module = Yii::$app->getModule('core');

        foreach ($module->handlersSearchProductByDescription as $class) {
            if (is_subclass_of($class, SearchInterface::class)) {
                $this->on(self::QUERY_SEARCH_BY_DESCRIPTION, [$class, 'editQuery']);
            }
        }

        $event = new SearchEvent();
        $event->q = $this->q;
        $event->activeQuery = (new Query());
        $this->trigger(self::QUERY_SEARCH_BY_DESCRIPTION, $event);

        return ArrayHelper::getColumn($event->activeQuery->all(), 'id');
    }

    public function searchPagesByDescription()
    {
        $result = (new Query())
            ->select('`id`')
            ->from(Page::tableName())
            ->orWhere('`title` LIKE :q')
            ->orWhere('`h1` LIKE :q')
            ->orWhere('`content` LIKE :q')
            ->addParams([':q' => '%' . $this->q . '%'])
            ->andWhere('published=1')
            ->andWhere('searchable=1')
            ->all();
        return ArrayHelper::getColumn($result, 'id');
    }
}
