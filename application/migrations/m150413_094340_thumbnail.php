<?php

use app\models\Image;
use app\models\Thumbnail;
use app\models\ThumbnailSize;
use yii\db\Migration;
use yii\helpers\VarDumper;

class m150413_094340_thumbnail extends Migration
{
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
//        $this->createTable(
//            '{{%thumbnail}}',
//            [
//                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
//                'img_id' => 'INT UNSIGNED NOT NULL',
//                'thumb_src' => 'VARCHAR(255) NOT NULL',
//                'size_id' => 'INT UNSIGNED NOT NULL',
//            ],
//            $tableOptions
//        );
//        $this->createTable(
//            '{{%thumbnail_size}}',
//            [
//                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
//                'width' => 'INT UNSIGNED NOT NULL',
//                'height' => 'INT UNSIGNED NOT NULL',
//            ],
//            $tableOptions
//        );
//        $this->createTable(
//            '{{%watermark}}',
//            [
//                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
//                'watermark_src' => 'VARCHAR(255) NOT NULL',
//            ],
//            $tableOptions
//        );
//        $this->createTable(
//            '{{%thumbnail_watermark}}',
//            [
//                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
//                'thumb_id' => 'INT UNSIGNED NOT NULL',
//                'water_id' => 'INT UNSIGNED NOT NULL',
//                'src' => 'VARCHAR(255) NOT NULL',
//                //@todo add type mb enum?
//            ],
//            $tableOptions
//        );
        //$this->dropColumn(Image::tableName(), 'thumbnail_src');
        $defaultSize = new ThumbnailSize;
        $defaultSize->setAttributes(['width' => 80, 'height' => 80]);
        VarDumper::dump($defaultSize->errors);
        $defaultSize->save();
        $images = Image::find()->all();
        foreach ($images as $image) {
            Thumbnail::createThumbnail($image, $defaultSize);
        }
    }

    public function down()
    {
        $this->dropTable('{{%thumbnail}}');
        $this->dropTable('{{%thumbnail_size}}');
        $this->dropTable('{{%watermark}}');
        $this->dropTable('{{%thumbnail_watermark}}');
        //$this->addColumn(Image::tableName(),'thumbnail_src','VARCHAR(255) NOT NULL');
        //@todo create new thumb to down
    }
}
