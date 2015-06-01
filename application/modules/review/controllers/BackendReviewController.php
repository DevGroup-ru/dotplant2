<?php

namespace app\modules\review\controllers;

use app\modules\review\models\Review;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use Yii;
use yii\web\NotFoundHttpException;
use app\components\SearchModel;
use app\models\Submission;

class BackendReviewController extends \app\backend\components\BackendController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['review manage'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModelConfig = [
            'defaultOrder' => ['id' => SORT_DESC],
            'model' => Review::className(),
            'relations' => [
                'submission.form' => ['name'],
            ],
        ];
        $searchModel = new SearchModel($searchModelConfig);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionView($id)
    {
        $review = Review::find()
            ->with(['submission'])
            ->where(['id' => $id])
            ->one();
        if (null === $review) {
            throw new NotFoundHttpException;
        }
        return $this->render(
            'submission-view',
            [
                'review' => $review
            ]
        );
    }

    public function actionUpdateStatus($id = null)
    {
        if (null === $id) {
            $id = \Yii::$app->request->post('editableKey');
            $index = \Yii::$app->request->post('editableIndex');
            if (null === $id || null === $index) {
                throw new BadRequestHttpException;
            } else {
                $review = $this->loadModel($id);
                $reviews = \Yii::$app->request->post('Review', []);
                $review->status = $reviews[$index]['status'];
                return $review->update();
            }
        } else {
            $reviews = $reviews = \Yii::$app->request->post('Review');
            $status = $reviews['status'];
            $review = $this->loadModel($id);
            $review->status = $status;
            if ($review->update()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Review successfully updated'));
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Error occurred while updating review'));
            }
            return $this->redirect(
                Url::toRoute(
                    [
                        'view',
                        'id' => $review->id
                    ]
                )
            );
        }
    }

    public function actionMarkSpam($id, $spam = 1)
    {
        if ($spam === 1) {
            $message = Yii::t('app', 'Entry successfully marked as spam');
        } else {
            $message = Yii::t('app', 'Entry successfully marked as not spam');
        }
        /** @var Submission $submission */
        $submission = Submission::findOne($id);
        if (is_null($submission)) {
            throw new NotFoundHttpException;
        }
        $submission->spam = Yii::$app->formatter->asBoolean($spam);
        if ($spam == 1) {
            /** @var Review $review */
            $review = Review::findOne(['submission_id' => $id]);
            if (!is_null($review)) {
                $review->status = Review::STATUS_NOT_APPROVED;
                $review->save(true, ['status']);
            }
        }
        if ($submission->save(true, ['spam'])) {
            Yii::$app->session->setFlash('success', $message);
        }
        return $this->redirect(
            Url::toRoute(
                [
                    'view',
                    'id' => $id
                ]
            )
        );
    }

    public function actionDelete($id, $returnUrl)
    {
        $model = $this->loadModel($id);
        if ($model->delete()) {
            Yii::$app->session->setFlash('info', Yii::t('app', 'Object removed'));
        }
        return $this->redirect($returnUrl);
    }

    public function actionRemoveAll($returnUrl = ['index'])
    {
        $items = Yii::$app->request->post('items', []);
        if (!empty($items)) {
            $items = Review::findAll(['id' => $items]);
            foreach ($items as $item) {
                $item->delete();
            }
            Yii::$app->session->setFlash('info', Yii::t('app', 'Objects removed'));
        }
        return $this->redirect($returnUrl);
    }

    /**
     * Load review model by id
     * @param $id
     * @return Review
     * @throws NotFoundHttpException
     */
    protected function loadModel($id)
    {
        $model = Review::findOne($id);
        if (is_null($model)) {
            throw new NotFoundHttpException;
        }
        return $model;
    }
}
