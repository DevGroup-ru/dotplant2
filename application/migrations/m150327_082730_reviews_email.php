<?php

use app\models\Config;
use yii\db\Migration;

class m150327_082730_reviews_email extends Migration
{
    public function up()
    {
        $core = Config::findOne(['name' => 'Core']);
        $reviews = new Config;
        $reviews->setAttributes(
            [
                'parent_id' => $core->id,
                'name' => 'Reviews',
                'key' => 'reviews',
                'value' => '',
                'preload' => 1,
                'path' => $core->path . '.reviews',
            ]
        );
        $reviews->save();
        $adminEmail = Config::findOne(['key' => 'adminEmail']);
        $email = new Config;
        $email->setAttributes(
            [
                'parent_id' => $reviews->id,
                'name' => 'E-mail',
                'key' => 'reviewEmail',
                'value' => $adminEmail->value,
                'preload' => 1,
                'path' => $reviews->path . '.reviewEmail',
            ]
        );
        $email->save();
        $emailView = new Config;
        $emailView->setAttributes(
            [
                'parent_id' => $reviews->id,
                'name' => 'Review e-mail template',
                'key' => 'reviewEmailTemplate',
                'value' => '@app/reviews/views/review-email-template',
                'preload' => 1,
                'path' => $reviews->path . 'reviewEmailTemplate',
            ]
        );
        $emailView->save();
    }

    public function down()
    {
        $this->delete(Config::tableName(), ['key' => 'reviewEmailTemplate']);
        $this->delete(Config::tableName(), ['key' => 'reviewEmail']);
        $this->delete(Config::tableName(), ['key' => 'reviews']);
    }
}
