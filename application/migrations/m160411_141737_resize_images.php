<?php

use app\modules\image\models\ThumbnailSize;
use Imagine\Image\ManipulatorInterface;
use yii\db\Migration;

class m160411_141737_resize_images extends Migration
{
    public function up()
    {
        $mods = implode("','", [ManipulatorInterface::THUMBNAIL_INSET, ManipulatorInterface::THUMBNAIL_OUTBOUND, ThumbnailSize::RESIZE]);
        $this->alterColumn(ThumbnailSize::tableName(), 'resize_mode', "ENUM ('$mods') DEFAULT '" . ManipulatorInterface::THUMBNAIL_INSET . "'");
    }

    public function down()
    {
        $mods = implode("','", [ManipulatorInterface::THUMBNAIL_INSET, ManipulatorInterface::THUMBNAIL_OUTBOUND]);
        $this->alterColumn(ThumbnailSize::tableName(), 'resize_mode', "ENUM ('$mods') DEFAULT '" . ManipulatorInterface::THUMBNAIL_INSET . "'");
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
