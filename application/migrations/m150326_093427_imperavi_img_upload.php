<?php

use app\models\Config;
use yii\db\Migration;

class m150326_093427_imperavi_img_upload extends Migration
{
    public function up()
    {
        $core = Config::findOne(['name' => 'Core']);
        $imperavi = new Config;
        $imperavi->setAttributes(
            [
                'parent_id' => $core->id,
                'name' => 'Imperavi',
                'key' => 'imperavi',
                'value' => '',
                'preload' => 1,
                'path' => $core->path . '.imperavi',
            ]
        );
        $imperavi->save();
        $uploadDir = new Config;
        $uploadDir->setAttributes(
            [
                'parent_id' => $imperavi->id,
                'name' => 'Img upload dir',
                'key' => 'uploadDir',
                'value' => '@webroot/upload',
                'preload' => 1,
                'path' => $imperavi->path . '.uploadDir',
            ]
        );
        $uploadDir->save();
    }

    public function down()
    {
        $imperavi = Config::findOne(['key' => 'imperavi']);
        $uploadDir = Config::findOne(['key' => 'uploadDir']);
        $imperavi->delete();
        $uploadDir->delete();
    }
}
