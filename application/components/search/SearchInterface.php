<?php

namespace app\components\search;


interface SearchInterface
{
    public static function editQuery(SearchEvent $event);
}