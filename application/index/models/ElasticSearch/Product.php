<?php

namespace app\index\models\ElasticSearch;

use app;
use app\index\models\ElasticSearchModel;
use Yii;

class Product extends ElasticSearchModel
{

    /**
     * Returns document type to separate different document types in one collection
     * @return string
     */
    public static function type()
    {
        return 'Product';
    }
}