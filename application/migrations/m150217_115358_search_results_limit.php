<?php

use app\models\Config;
use yii\db\Migration;

class m150217_115358_search_results_limit extends Migration
{
    public function up()
    {
        Config::deleteAll(['path' => ['page.searchResultsLimit', 'product.searchResultsLimit']]);
        $parent = Config::findOne(['path' => 'page']);
        if (!is_null($parent)) {
            $config = new Config;
            $config->attributes = [
                'parent_id' => $parent->id,
                'name' => 'Search results limit',
                'key' => 'searchResultsLimit',
                'value' => '10',
            ];
            $config->save();
        }
        $parent = Config::findOne(['path' => 'shop']);
        if (!is_null($parent)) {
            $config = new Config;
            $config->attributes = [
                'parent_id' => $parent->id,
                'name' => 'Search results limit',
                'key' => 'searchResultsLimit',
                'value' => '9',

            ];
            $config->save();
        }
    }

    public function down()
    {
        Config::deleteAll(['path' => ['page.searchResultsLimit', 'product.searchResultsLimit']]);
    }
}
