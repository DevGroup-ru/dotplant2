<?php

use app\modules\image\models\ThumbnailSize;
use yii\db\Schema;
use yii\db\Migration;

class m150716_091156_qualityThumbnail extends Migration
{
    public function up()
    {

        $this->addColumn(
            ThumbnailSize::tableName(),
            'quality',
            'TINYINT(1) UNSIGNED NOT NULL DEFAULT 90'
        );

    }

    public function down()
    {
        $this->dropColumn(
            ThumbnailSize::tableName(),
            'quality'
        );
    }

}
