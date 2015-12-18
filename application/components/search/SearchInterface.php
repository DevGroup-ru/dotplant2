<?php

namespace app\components\search;


interface SearchInterface
{
    /**
     * set $event params
     *
     * @param SearchEvent $event
     * @return null
     */
    public static function editQuery(SearchEvent $event);
}