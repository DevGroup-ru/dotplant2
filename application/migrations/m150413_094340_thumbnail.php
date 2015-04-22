<?php

use app\backend\models\BackendMenu;
use app\backgroundtasks\models\Task;
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
                'default_watermark_id' => 'INT UNSIGNED NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%watermark}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'watermark_src' => 'VARCHAR(255) NOT NULL',
                'position' => 'enum(\'TOP LEFT\',\'TOP RIGHT\',\'BOTTOM LEFT\',\'BOTTOM RIGHT\',\'CENTER\') NOT NULL DEFAULT \'TOP LEFT\''
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%thumbnail_watermark}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'thumb_id' => 'INT UNSIGNED NOT NULL',
                'water_id' => 'INT UNSIGNED NOT NULL',
                'compiled_src' => 'VARCHAR(255) NOT NULL',
            ],
            $tableOptions
        );
        //$this->dropColumn(Image::tableName(), 'thumbnail_src');
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
                [$image_id, 'Use watermark', 'useWatermark', '0', 'image.useWatermark'],
                [
                    $image_id,
                    'Watermark directory',
                    'waterDir',
                    '/theme/resources/product-images/watermark',
                    'image.waterDir'
                ],
                [
                    $image_id,
                    'No image supplied',
                    'noImage',
                    'http://placehold.it/300&text=No+image+supplied',
                    'image.noImage'
                ],
                [
                    $image_id,
                    'List of recreated isd',
                    'IdsToRecreate',
                    '',
                    'image.IdsToRecreate'
                ],
            ]
        );
        $this->insert(
            BackendMenu::tableName(),
            [
                'parent_id' => 1,
                'name' => 'Images',
                'icon' => 'picture-o',
                'sort_order' => 10,
                'added_by_ext' => 'core',
                'rbac_check' => 'content manage',
                'translation_category' => 'app'
            ]
        );
        $image_menu_id = Yii::$app->db->lastInsertID;
        $this->batchInsert(
            BackendMenu::tableName(),
            ['parent_id', 'name', 'route', 'added_by_ext', 'rbac_check', 'translation_category'],
            [
                [$image_menu_id, 'Thumbnails sizes', 'backend/thumbnail-size/index', 'core', 'content manage', 'app'],
                [$image_menu_id, 'Create thumbnails', 'backend/thumbnail/index', 'core', 'content manage', 'app'],
                [$image_menu_id, 'Watermarks', 'backend/watermark/index', 'core', 'content manage', 'app'],
                [$image_menu_id, 'Broken images', 'backend/error-images/index', 'core', 'content manage', 'app'],
            ]
        );
        $this->createTable(
            '{{%error_images}}',
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'img_id' => 'INT UNSIGNED NOT NULL',
                'class_name' => 'VARCHAR(255) NOT NULL',
            ],
            $tableOptions
        );
        $this->insert(
            Task::tableName(),
            [
                'action' => 'images/check-broken',
                'type' => Task::TYPE_REPEAT,
                'initiator' => 1,
                'name' => 'Check broken images',
                'cron_expression' => '* */1 * * *',
            ]
        );
    }

    public function down()
    {
        $this->dropTable('{{%thumbnail}}');
        $this->dropTable('{{%thumbnail_size}}');
        $this->dropTable('{{%watermark}}');
        $this->dropTable('{{%thumbnail_watermark}}');
        $this->dropTable('{{%error_images}}');
        $this->addColumn(Image::tableName(), 'thumbnail_src', 'VARCHAR(255) NOT NULL');
        $this->delete(Config::tableName(), ['key' => 'IdsToRecreate']);
        $this->delete(Config::tableName(), ['key' => 'noImage']);
        $this->delete(Config::tableName(), ['key' => 'waterDir']);
        $this->delete(Config::tableName(), ['key' => 'useWatermark']);
        $this->delete(Config::tableName(), ['key' => 'thumbDir']);
        $this->delete(Config::tableName(), ['key' => 'defaultThumbSize']);
        $this->delete(Config::tableName(), ['key' => 'image']);
        $this->delete(BackendMenu::tableName(), ['name' => 'Broken images']);
        $this->delete(BackendMenu::tableName(), ['name' => 'Thumbnails sizes']);
        $this->delete(BackendMenu::tableName(), ['name' => 'Create thumbnails']);
        $this->delete(BackendMenu::tableName(), ['name' => 'Watermarks']);
        $this->delete(BackendMenu::tableName(), ['name' => 'Images']);
        $this->delete(Task::tableName(), ['action' => 'images/check-broken']);
    }
}
