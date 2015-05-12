<?php

use yii\db\Schema;
use yii\db\Migration;
use \app\modules\seo\models\Config;
use \app\modules\seo\models\Robots;

class m150512_135841_db_robots_txt extends Migration
{

    public $robotsText = "User-agent: *\nDisallow: /cabinet";

    public function up()
    {
        $model = Config::findOne(Robots::KEY_ROBOTS);
        if ($model === null) {
            $model = new Robots();
        }

        if ($model->value !== $this->robotsText || $model->value === "") {
            $model->value = $this->robotsText;
            $model->save();
        }
    }

    public function down()
    {
        $model = Config::findOne(Robots::KEY_ROBOTS);

        if ($model && $model->value === $this->robotsText) {
            $model->delete();
        }
    }

}
