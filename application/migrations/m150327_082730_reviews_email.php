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
            ]
        );
        $email->save();
        $page = new Config;
        $page->setAttributes(
            [
                'parent_id' => $reviews->id,
                'name' => 'Page reviews',
                'key' => 'pageReviews',
                'value' => '',
                'preload' => 1,
            ]
        );
        $page->save();
        $pageEmailView = new Config;
        $pageEmailView->setAttributes(
            [
                'parent_id' => $page->id,
                'name' => 'Page review e-mail template',
                'key' => 'pageReviewEmailTemplate',
                'value' => '@app/reviews/views/page-review-email-template',
                'preload' => 1,
            ]
        );
        $pageEmailView->save();
        $pageSend = new Config;
        $pageSend->setAttributes(
            [
                'parent_id' => $page->id,
                'name' => 'Send page review email',
                'key' => 'pageReviewSend',
                'value' => '1',
                'preload' => 1,
            ]
        );
        $pageSend->save();
        $product = new Config;
        $product->setAttributes(
            [
                'parent_id' => $reviews->id,
                'name' => 'Product reviews',
                'key' => 'productReviews',
                'value' => '',
                'preload' => 1,
            ]
        );
        $product->save();
        $productEmailView = new Config;
        $productEmailView->setAttributes(
            [
                'parent_id' => $product->id,
                'name' => 'Product review e-mail template',
                'key' => 'productReviewEmailTemplate',
                'value' => '@app/reviews/views/product-review-email-template',
                'preload' => 1,
            ]
        );
        $productEmailView->save();
        $productSend = new Config;
        $productSend->setAttributes(
            [
                'parent_id' => $product->id,
                'name' => 'Send product review email',
                'key' => 'productReviewSend',
                'value' => '1',
                'preload' => 1,
            ]
        );
        $productSend->save();
    }

    public function down()
    {
        $this->delete(Config::tableName(), ['key' => 'productReviewSend']);
        $this->delete(Config::tableName(), ['key' => 'productReviewEmailTemplate']);
        $this->delete(Config::tableName(), ['key' => 'productReviews']);
        $this->delete(Config::tableName(), ['key' => 'pageReviewSend']);
        $this->delete(Config::tableName(), ['key' => 'pageReviewEmailTemplate']);
        $this->delete(Config::tableName(), ['key' => 'pageReviews']);
        $this->delete(Config::tableName(), ['key' => 'reviewEmail']);
        $this->delete(Config::tableName(), ['key' => 'reviews']);
    }
}
