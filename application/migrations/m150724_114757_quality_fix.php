<?php

use app\modules\image\models\ThumbnailSize;
use yii\db\Migration;

class m150724_114757_quality_fix extends Migration
{
    public function up()
    {
	$this->alterColumn(
            ThumbnailSize::tableName(),
            'quality',
            'TINYINT(1) UNSIGNED DEFAULT 90'
        );
    }

    public function down()
    {
        $this->alterColumn(
            ThumbnailSize::tableName(),
            'quality',
            'TINYINT(1) UNSIGNED NOT NULL DEFAULT 90'
        );
    }
}
