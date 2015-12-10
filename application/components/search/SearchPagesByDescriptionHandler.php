<?php

namespace app\components\search;


use app\modules\page\models\Page;

class SearchPagesByDescriptionHandler implements SearchInterface
{
    public static function editQuery(SearchEvent $event)
    {
        $event->activeQuery->select('`id`')
            ->from(Page::tableName())
            ->orWhere('`title` LIKE :q')
            ->orWhere('`h1` LIKE :q')
            ->orWhere('`content` LIKE :q')
            ->addParams([':q' => '%' . $event->q . '%'])
            ->andWhere('published=1')
            ->andWhere('searchable=1');
    }
}