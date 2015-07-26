<?php

namespace app\modules\review\controllers;

use app\actions\SubmitFormAction;
use app\behaviors\spamchecker\SpamCheckerBehavior;
use app\components\Controller;
use app\models\SpamChecker;
use app\models\Submission;
use app\modules\review\models\RatingItem;
use app\modules\review\models\RatingValues;
use app\modules\review\models\Review;
use Yii;
use yii\helpers\Json;
use yii\web\HttpException;

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

    /**
     * @param $id
     * @param $objectModelId
     * @param $objectId
     * @param string $returnUrl
     * @return \yii\web\Response
     * @throws HttpException
     */
    public function actionProcess($id, $objectModelId, $objectId, $returnUrl = '/')
    {
        if (false === Yii::$app->request->isPost) {
            throw new HttpException(403);
        }
        /** @var $review \app\modules\review\models\Review|SpamCheckerBehavior */
        $post = Yii::$app->request->post();
        $review = new Review(['scenario' => 'check']);
        $review->load($post);
        if (!Yii::$app->user->isGuest) {
            $review->author_email = Yii::$app->user->identity->email;
        }
        $review->object_id = $objectId;
        $review->object_model_id = $objectModelId;
        if ($review->validate()) {
            $submission_id = Yii::$app->runAction('review/process/submission', ['id' => $id]);
            if ($submission_id == "0") {
                Yii::$app->session->setFlash(
                    'info',
                    Yii::t('app', 'Error occurred while saving review. Sorry. Try again later')
                );
                return $this->redirect($returnUrl);
            }
            $review->submission_id = $submission_id;
            $review->status = Review::STATUS_NEW;
            if ($this->module->enableSpamChecking) {
                $activeSpamChecker = SpamChecker::getActive();
                if (!is_null($activeSpamChecker) && !empty($activeSpamChecker->api_key)) {
                    $review->attachBehavior(
                        'spamChecker',
                        [
                            'class' => SpamCheckerBehavior::className(),
                            'data' => [
                                $activeSpamChecker->name => [
                                    'class' => $activeSpamChecker->behavior,
                                    'value' => [
                                        'key' => $activeSpamChecker->api_key,
                                        SpamChecker::FIELD_TYPE_CONTENT => $review->review_text
                                    ],
                                ],
                            ],
                        ]
                    );
                    if ($review->isSpam()) {
                        $review->status = Review::STATUS_NOT_APPROVED;
                        Submission::updateAll(['spam' => 1], ['id' => $submission_id]);
                    }
                }
            }
            if ($review->save()) {
                $ratingData = isset($post['ObjectRating']) ? $post['ObjectRating'] : null;
                if (null !== $ratingData) {
                    $group = isset($ratingData['group']) ? trim($ratingData['group']) : null;
                    $group = RatingItem::getGroupByName($group);
                    $items = [];
                    if (!empty($ratingData['values']) && !empty($group)) {
                        $user_id = \Yii::$app->getUser()->isGuest ? 0 : \Yii::$app->user->identity->getId();
                        $ratingId = md5(Json::encode(array_merge($ratingData['values'], [microtime(), $user_id])));
                        $date = date('Y-m-d H:m:s');
                        if ((0 == $group['require_review']) || ((0 != $group['require_review']))) {
                            $items = RatingItem::getItemsByAttributes(
                                ['rating_group' => $group['rating_group']],
                                true,
                                true
                            );
                        }
                        if (!empty($items)) {
                            foreach ($items as $key => $item) {
                                $model = new RatingValues();
                                $model->loadDefaultValues();
                                $model->object_id = $objectId;
                                $model->object_model_id = $objectModelId;
                                $model->rating_item_id = $item['id'];
                                $model->value = isset($ratingData['values'][$item['id']])
                                    ? intval($ratingData['values'][$item['id']])
                                    : 0;
                                $model->rating_id = $ratingId;
                                $model->date = $date;
                                $model->save();
                            }
                            if (isset($review)) {
                                $review->rating_id = $ratingId;
                                $review->save(true, ['rating_id']);
                            }
                        }
                    }
                }
                Yii::$app->session->setFlash(
                    'info',
                    Yii::t('app', 'Your review will appear on the website immediately after moderation')
                );
                return $this->redirect($returnUrl);
            }
        }
        return $this->redirect($returnUrl);
    }
}
