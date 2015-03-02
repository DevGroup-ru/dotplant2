<?php

namespace app\reviews\traits;

use app\reviews\models\Review;
use Yii;

trait ProcessReviews
{

    public function processReviews($object_id, $object_model_id)
    {
        if (isset($_POST['Review'])) {
            $review = new Review();
            $review->useCaptcha = isset($_POST['Review']['captcha']);
            $review->object_id = $object_id;
            $review->object_model_id = $object_model_id;
            $review->load($_POST);
            if (!\Yii::$app->getUser()->isGuest) {
                $review->author_name = \Yii::$app->user->identity->awesomeUsername;
            }
            $review->status = Review::STATUS_NEW;
            $review->date_submitted = date("Y-m-d H:i:s");
            if ($review->save()) {
                Yii::$app->session->setFlash(
                    'info',
                    Yii::t('shop', 'Your review will appear on the website immediately after moderation')
                );
            }
        }
    }
}
