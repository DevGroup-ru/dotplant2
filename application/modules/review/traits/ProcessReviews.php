<?php

namespace app\modules\review\traits;

use app\models\Object;
use app\models\RatingItem;
use app\models\RatingValues;
use app\modules\review\models\Review;
use Yii;
use yii\helpers\Json;

trait ProcessReviews
{
    public function processReviews($object_id, $object_model_id)
    {
        $review_saved = false;

        if (isset($_POST['Review'])) {
            $review = new Review();
            $review->useCaptcha = isset($_POST['Review']['captcha']);
            $review->object_id = $object_id;
            $review->object_model_id = $object_model_id;
            $review->load($_POST);
            if (!\Yii::$app->getUser()->isGuest) {
                $review->author_name = \Yii::$app->user->identity->getDisplayName();
            }
            $review->status = Review::STATUS_NEW;
            $review->date_submitted = date("Y-m-d H:i:s");
            if ($review->save()) {
                Yii::$app->session->setFlash(
                    'info',
                    Yii::t('app', 'Your review will appear on the website immediately after moderation')
                );
                $review_saved = true;
                if (Yii::$app->getModule('review')->email !== null) {
                    $object = Object::findById($review->object_id);
                    if (Yii::$app->getModule('review')->isEnableNotification($object->name)) {
                        try {
                            Yii::$app->mail->compose(
                                Yii::$app->getModule('review')->getEmailTemplate($object->name),
                                [
                                    'review' => $review,
                                ]
                            )->setTo(explode(',', Yii::$app->getModule('review')->email))->setFrom(
                                Yii::$app->mail->transport->getUsername()
                            )->setSubject(Yii::t('app', 'Review #{reviewId}', ['reviewId' => $review->id]))->send();
                        } catch (\Exception $e) {
                            // do nothing
                        }
                    }
                }
            }
        }

        $_post = Yii::$app->request->post('ObjectRating');
        if (Yii::$app->request->isPost && (null !== $_post)) {
            $group = isset($_post['group']) ? trim($_post['group']) : null;
            $group = RatingItem::getGroupByName($group);
            $items = [];

            if (!empty($_post['values']) && !empty($group)) {
                $user_id = \Yii::$app->getUser()->isGuest ? 0 : \Yii::$app->user->identity->getId();
                $rating_id = md5(Json::encode(array_merge($_post['values'], [microtime(), $user_id])));
                $date = date('Y-m-d H:m:s');

                if ((0 == $group['require_review']) || ((0 != $group['require_review']) && $review_saved)) {
                    $items = RatingItem::getItemsByAttributes(['rating_group' => $group['rating_group']], true, true);
                }

                if (!empty($items)) {
                    foreach ($items as $key => $item) {
                        $model = new RatingValues();
                        $model->loadDefaultValues();
                        $model->object_id = $object_id;
                        $model->object_model_id = $object_model_id;
                        $model->rating_item_id = $item['id'];
                        $model->value = isset($_post['values'][$item['id']]) ? intval(
                            $_post['values'][$item['id']]
                        ) : 0;
                        $model->rating_id = $rating_id;
                        $model->date = $date;
                        $model->save();
                    }

                    if (isset($review)) {
                        $review->rating_id = $rating_id;
                        $review->save();
                    }
                }
            }
        }
    }
}
