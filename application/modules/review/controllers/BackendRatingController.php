<?php

namespace app\modules\review\controllers;

use app\backend\components\BackendController;
use app\modules\review\models\RatingItem;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

class BackendRatingController extends BackendController
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

    /**
     *
     */
    public function actionIndex()
    {
        $data_provider = new ActiveDataProvider([
            'query' => RatingItem::getGroupsAll(false),
            'pagination' => [
                'pageSize' => 25,
            ]
        ]);

        echo $this->render(
            'index',
            [
                'data_provider' => $data_provider
            ]
        );
    }

    /**
     * @param null $group
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionGroupEdit($group = null)
    {
        if (null === $group) {
            throw new NotFoundHttpException();
        }

        $group = RatingItem::getGroupByName(urldecode($group));
        if (empty($group)) {
            throw new NotFoundHttpException();
        }

        if (\Yii::$app->request->isPost) {
            $_group = urldecode(\Yii::$app->request->post('group-name', ''));
            $_require_review = intval(\Yii::$app->request->post('group-require-review', 0));
            $items = RatingItem::getItemsByAttributes(['rating_group' => $group['rating_group']]);
            $_allow_guest = intval(\Yii::$app->request->post('group-allow-guest', 0));

            foreach ($items as $item) {
                $item->rating_group = $_group;
                $item->require_review = $_require_review;
                $item->allow_guest = $_allow_guest;
                $item->save();
            }

            return $this->redirect(Url::to(['group-edit', 'group' => urlencode($_group)]));
        }

        $items = RatingItem::getItemsByAttributes(['rating_group' => $group['rating_group']], false);

        $data_provider = new ActiveDataProvider([
            'query' => $items,
            'pagination' => [
                'pageSize' => 25,
            ]
        ]);

        echo $this->render(
            'group-edit',
            [
                'group' => $group,
                'data_provider' => $data_provider
            ]
        );
    }

    /**
     * @return \yii\web\Response
     */
    public function actionGroupCreate()
    {
        if (\Yii::$app->request->isPost) {
            $group = urldecode(\Yii::$app->request->post('group-name', ''));
            $_require_review = intval(\Yii::$app->request->post('group-require-review', 0));
            $_allow_guest = intval(\Yii::$app->request->post('group-allow-guest', 0));
            if (!empty($group)) {
                if (null === RatingItem::getOneItemByAttributes(['rating_group' => $group])) {
                    $item = new RatingItem();
                    $item->setAttributes([
                        'name' => $group,
                        'rating_group' => $group,
                        'require_review' => $_require_review,
                        'allow_guest' => $_allow_guest,
                    ]);
                    $item->save();

                    return $this->redirect(Url::to(['group-edit', 'group' => urlencode($group)]));
                }
            }
        }

        echo $this->render(
            'group-create'
        );
    }

    /**
     * @param null $group
     * @param null $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionItemEdit($group = null, $id = null)
    {
        if ((null === $group) && (null === $id)) {
            throw new NotFoundHttpException();
        }

        if (null !== $id) {
            $model = RatingItem::getOneItemByAttributes(['id' => $id]);
        }

        if (\Yii::$app->request->isPost) {
            if (!isset($model)) {
                $model = new RatingItem();
            }

            $model->loadDefaultValues();

            if ($model->load(\Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(Url::to(['group-edit', 'group' => urlencode($model->rating_group)]));
            }
        }

        if (!isset($model)) {
            if (null === $group) {
                return $this->redirect(Url::to(['index']));
            } else {
                $model = new RatingItem();
                $model->rating_group = urldecode($group);
            }
        }

        echo $this->render(
            'item-edit',
            [
                'model' => $model
            ]
        );
    }

    /**
     * @param null $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionItemDelete($id = null)
    {
        $item = RatingItem::getOneItemByAttributes(['id' => $id]);
        if (empty($item)) {
            throw new NotFoundHttpException();
        }

        $group = $item->rating_group;

        $item->delete();

        return $this->redirect(Url::to(['group-edit', 'group' => urlencode($group)]));
    }
}
