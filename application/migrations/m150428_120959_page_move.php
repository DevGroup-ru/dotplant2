<?php

use yii\db\Schema;
use yii\db\Migration;
use app\backend\models\BackendMenu;
use app\modules\config\models\Configurable;

class m150428_120959_page_move extends Migration
{
    public function up()
    {
        $this->update('{{%object}}', ['object_class' => 'app\modules\page\models\Page'],['name' => 'Page']);
        $this->update(BackendMenu::tableName(), ['route' => 'page/backend/index'],['name' => 'Pages']);
        $this->insert(
            Configurable::tableName(),
            [
                'module' => 'page',
                'sort_order' => 7,
                'section_name' => 'Pages',
                'display_in_config' => 1,
            ]);
    }

    public function down()
    {
        $this->update('{{%object}}', ['object_class' => 'app\models\Page'],['name' => 'Page']);
        $this->update(BackendMenu::tableName(), ['route' => 'backend/page/index'],['name' => 'Pages']);
        $this->delete(Configurable::tableName(), ['module' => 'page']);
    }

}
