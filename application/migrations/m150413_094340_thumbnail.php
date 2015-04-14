<?php

use app\models\Image;
use app\models\Thumbnail;
use app\models\ThumbnailSize;
use app\models\Config;
use yii\db\Migration;

class m150413_094340_thumbnail extends Migration
{
    public function up()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        $this->createTable(
            '{{%thumbnail}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'img_id' => 'INT UNSIGNED NOT NULL',
                'thumb_src' => 'VARCHAR(255) NOT NULL',
                'size_id' => 'INT UNSIGNED NOT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%thumbnail_size}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'width' => 'INT UNSIGNED NOT NULL',
                'height' => 'INT UNSIGNED NOT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%watermark}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'watermark_src' => 'VARCHAR(255) NOT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%thumbnail_watermark}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'thumb_id' => 'INT UNSIGNED NOT NULL',
                'water_id' => 'INT UNSIGNED NOT NULL',
                'src' => 'VARCHAR(255) NOT NULL',
                //@todo add type mb enum?
            ],
            $tableOptions
        );
        $this->dropColumn(Image::tableName(), 'thumbnail_src');
        $defaultSize = new ThumbnailSize;
        $defaultSize->setAttributes(['width' => 80, 'height' => 80]);
        $defaultSize->save();
        $images = Image::find()->all();
        foreach ($images as $image) {
            Thumbnail::getImageThumbnailBySize($image, $defaultSize);
        }
        $this->insert(Config::tableName(), ['parent_id' => 0, 'name' => 'Image', 'key' => 'image', 'path' => 'image']);
        $image_id = Yii::$app->db->lastInsertID;
        $this->batchInsert(
            Config::tableName(),
            ['parent_id', 'name', 'key', 'value', 'path'],
            [
                [$image_id, 'Default thumbnail size', 'defaultThumbSize', '80x80', 'image.defaultThumbSize'],
                [
                    $image_id,
                    'Thumbnails directory',
                    'thumbDir',
                    '/theme/resources/product-images/thumbnail',
                    'image.thumbDir'
                ],
            ]
        );
        return false;
    }

    public function down()
    {
        $this->dropTable('{{%thumbnail}}');
        $this->dropTable('{{%thumbnail_size}}');
        $this->dropTable('{{%watermark}}');
        $this->dropTable('{{%thumbnail_watermark}}');
        $this->addColumn(Image::tableName(), 'thumbnail_src', 'VARCHAR(255) NOT NULL');
        //@todo create new thumb to down ImageDropzone::saveThumbnail()
    }
}
