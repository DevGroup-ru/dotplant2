<?php

use app\backend\models\BackendMenu;
use app\backgroundtasks\models\Task;
use app\modules\config\models\Configurable;
use app\modules\image\models\Image;
use app\modules\image\models\Thumbnail;
use app\modules\image\models\ThumbnailSize;
use app\modules\image\models\ThumbnailWatermark;
use app\modules\image\models\Watermark;
use Imagine\Image\ManipulatorInterface;
use app\modules\image\models\ErrorImage;
use yii\db\Migration;
use yii\db\Query;

class m150413_094340_thumbnail extends Migration
{
    public function up()
    {

        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        $this->createTable(
            Thumbnail::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'img_id' => 'INT UNSIGNED NOT NULL',
                'thumb_path' => 'VARCHAR(255) NOT NULL',
                'size_id' => 'INT UNSIGNED NOT NULL',
            ],
            $tableOptions
        );
        $this->createTable(
            ThumbnailSize::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'width' => 'INT UNSIGNED NOT NULL',
                'height' => 'INT UNSIGNED NOT NULL',
                'default_watermark_id' => 'INT UNSIGNED NULL',
                'resize_mode' => 'ENUM(\'' . ManipulatorInterface::THUMBNAIL_INSET . '\',\'' . ManipulatorInterface::THUMBNAIL_OUTBOUND . '\') DEFAULT \'' . ManipulatorInterface::THUMBNAIL_INSET . '\'',
            ],
            $tableOptions
        );
        $this->createTable(
            Watermark::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'watermark_path' => 'VARCHAR(255) NOT NULL',
                'position' => 'enum(\'TOP LEFT\',\'TOP RIGHT\',\'BOTTOM LEFT\',\'BOTTOM RIGHT\',\'CENTER\') NOT NULL DEFAULT \'TOP LEFT\''
            ],
            $tableOptions
        );
        $this->createTable(
            ThumbnailWatermark::tableName(),
            [
                'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                'thumb_id' => 'INT UNSIGNED NOT NULL',
                'water_id' => 'INT UNSIGNED NOT NULL',
                'compiled_src' => 'VARCHAR(255) NOT NULL',
            ],
            $tableOptions
        );

        $defaultSize = new ThumbnailSize;
        $defaultSize->setAttributes(['width' => 80, 'height' => 80]);
        $defaultSize->save();
        $query = new Query;
        $query->select('*')->from('image');
        $images = $query->all();
        foreach ($images as $image) {
            try {
                if (file_exists(Yii::getAlias("@webroot{$image['image_src']}")) === true) {
                    $stream = fopen(Yii::getAlias("@webroot{$image['image_src']}"), 'r+');
                    Yii::$app->getModule('image')->fsComponent->putStream($image['filename'], $stream);
                } else {
                    $this->delete(Image::tableName(), ['id' => $image['id']]);
                }
            } catch (\Exception $e) {
                echo sprintf('[%s] %s || %s'. PHP_EOL, $e->getMessage(), $image['image_src'], $image['filename']);
            }
        }

        $this->dropColumn(Image::tableName(), 'thumbnail_src');
        $this->dropColumn(Image::tableName(), 'image_src');

        $this->insert(
            BackendMenu::tableName(),
            [
                'parent_id' => 1,
                'name' => 'Images',
                'icon' => 'picture-o',
                'sort_order' => 10,
                'route' => '',
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
                [
                    $image_menu_id,
                    'Thumbnails sizes',
                    'image/backend-thumbnail-size/index',
                    'core',
                    'content manage',
                    'app'
                ],
                [$image_menu_id, 'Create thumbnails', 'image/backend-thumbnail/index', 'core', 'content manage', 'app'],
                [$image_menu_id, 'Watermarks', 'image/backend-watermark/index', 'core', 'content manage', 'app'],
                [$image_menu_id, 'Broken images', 'image/backend-error-images/index', 'core', 'content manage', 'app'],
            ]
        );
        $this->createTable(
            ErrorImage::tableName(),
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
        $this->insert(
            Configurable::tableName(),
            [
                'module' => 'image',
                'sort_order' => 8,
                'section_name' => 'Images',
            ]
        );
    }

    public function down()
    {
        $this->delete(Configurable::tableName(), ['module' => 'image']);
        $this->dropTable(Thumbnail::tableName());
        $this->dropTable(ThumbnailSize::tableName());
        $this->dropTable(Watermark::tableName());
        $this->dropTable(ThumbnailWatermark::tableName());
        $this->addColumn(Image::tableName(), 'thumbnail_src', 'VARCHAR(255)');
        $this->addColumn(Image::tableName(), 'image_src', 'VARCHAR(255)');
        $this->dropTable(ErrorImage::tableName());
        $this->delete(BackendMenu::tableName(), ['name' => 'Broken images']);
        $this->delete(BackendMenu::tableName(), ['name' => 'Thumbnails sizes']);
        $this->delete(BackendMenu::tableName(), ['name' => 'Create thumbnails']);
        $this->delete(BackendMenu::tableName(), ['name' => 'Watermarks']);
        $this->delete(BackendMenu::tableName(), ['name' => 'Images']);
        $this->delete(Task::tableName(), ['action' => 'images/check-broken']);
    }
}
