<?php

use yii\db\Migration;
use app\backend\models\BackendMenu;

class m150508_084640_review_move extends Migration
{
    public function up()
    {
        $this->update(BackendMenu::tableName(), ['route' => 'review/backend/products'],['name' => 'Product reviews']);
        $this->update(BackendMenu::tableName(), ['route' => 'review/backend/pages'],['name' => 'Page reviews']);
    }

    public function down()
    {
        $this->update(BackendMenu::tableName(), ['route' => 'backend/review/products'],['name' => 'Product reviews']);
        $this->update(BackendMenu::tableName(), ['route' => 'backend/review/pages'],['name' => 'Page reviews']);
    }

}
