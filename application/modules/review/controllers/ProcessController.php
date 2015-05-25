<?php

namespace app\modules\review\controllers;

use app\components\Controller;
use app\modules\review\models\Review;
use Yii;
use app\actions\SubmitFormAction;
use yii\helpers\VarDumper;
use yii\web\HttpException;
use app\models\RatingItem;
use app\models\RatingValues;
use yii\helpers\Json;

class ProcessController extends Controller
{
    public function actions()
    {
        return [
                'submission' => [
                  'class' => SubmitFormAction::className(),
                ],
            ];
    }

    public function actionProcess($id, $object_model_id, $object_id, $returnUrl = '/')
    {
        if (false === Yii::$app->request->isPost) {
            throw new HttpException(403);
        }
        /** @var $review \app\modules\review\models\Review */
        $post = Yii::$app->request->post();
        $review = new Review(['scenario' => 'check']);
        $review->load($post);
        if (Yii::$app->user->isGuest === false) {
            $review->author_email = Yii::$app->user->identity->email;
        }
        $review->object_id = $object_id;
        $review->object_model_id = $object_model_id;
        if ($review->validate()) {
            $submission_id = Yii::$app->runAction('review/process/submission', ['id' => $id]);
            if ($submission_id == "0") {
                Yii::$app->session->setFlash(
                    'info',
                    Yii::t('error', 'Error occurred while saving review. Sorry. Try again later')
                );
                $this->redirect($returnUrl);
            }
            $review->submission_id = $submission_id;
            $review->status= $review::STATUS_NEW;
            if ($review->save()) {
                $ratingData = isset($post['ObjectRating']) ? $post['ObjectRating'] : null;
                if (null !== $ratingData) {
                    $group = isset($ratingData['group']) ? trim($ratingData['group']) : null;
                    $group = RatingItem::getGroupByName($group);
                    $items = [];

                    if (!empty($ratingData['values']) && !empty($group)) {
                        $user_id = \Yii::$app->getUser()->isGuest ? 0 : \Yii::$app->user->identity->getId();
                        $rating_id = md5(Json::encode(array_merge($ratingData['values'], [microtime(), $user_id])));
                        $date = date('Y-m-d H:m:s');

                        if ((0 == $group['require_review']) || ((0 != $group['require_review']))) {
                            $items = RatingItem::getItemsByAttributes(['rating_group' => $group['rating_group']], true, true);
                        }

                        if (!empty($items)) {
                            foreach ($items as $key => $item) {
                                $model = new RatingValues();
                                $model->loadDefaultValues();
                                $model->object_id = $object_id;
                                $model->object_model_id = $object_model_id;
                                $model->rating_item_id = $item['id'];
                                $model->value = isset($ratingData['values'][$item['id']]) ? intval(
                                    $ratingData['values'][$item['id']]
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
                Yii::$app->session->setFlash(
                    'info',
                    Yii::t('app', 'Your review will appear on the website immediately after moderation')
                );
                $this->redirect($returnUrl);
            }
        }
    }
}