<?php

namespace app\modules\review\controllers;

use app\models\Object;
use app\modules\page\models\Page;
use app\modules\review\models\Review;
use app\modules\shop\models\Category;
use app\modules\shop\models\Product;
use yii\db\ActiveRecord;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use Yii;
use yii\web\NotFoundHttpException;
use app\components\SearchModel;
use app\models\Submission;
use yii\web\Response;

class BackendReviewController extends \app\backend\components\BackendController
{

    const BACKEND_REVIEW_EDIT = 'backend-review-edit';
    const BACKEND_REVIEW_EDIT_SAVE = 'backend-review-edit-save';
    const BACKEND_REVIEW_EDIT_FORM = 'backend-review-edit-form';
    const BACKEND_REVIEW_AFTER_SAVE = 'backend-review-after-save';


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
            'additionalConditions' => [
                ['parent_id' => 0],
            ]
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
        $model = Review::find()
            ->with(['submission'])
            ->where(['id' => $id])
            ->one();
        if (null === $model) {
            throw new NotFoundHttpException;
        }

        if (true === Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(Url::toRoute(['view', 'id' => $model->id]));
            }
        }

        return $this->render('edit', [
            'review' => $model
        ]);
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
        $submission->spam = $spam;
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

    /**
     * @param $id
     * @param null $returnUrl
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDelete($id, $returnUrl = null)
    {
        $model = $this->loadModel($id);
        $parent_id = $model->parent_id;
        if ($model->delete()) {
            Yii::$app->session->setFlash('info', Yii::t('app', 'Object removed'));
        }

        $returnUrl = !empty($returnUrl)
            ? $returnUrl
            : (0 === intval($parent_id) ? Url::toRoute(['index']) : Url::toRoute(['view', 'id' => $parent_id]));
        return $this->redirect($returnUrl);
    }

    /**
     * @param array $returnUrl
     * @return Response
     * @throws \Exception
     */
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

    public function actionCreate($parent_id = 0)
    {
        $parent_id = intval($parent_id);
        $model = new Review();
        $model->loadDefaultValues();

        if (0 === $parent_id) {
            $model->parent_id = $parent_id;
            $model->submission_id = 0;
            $model->object_model_id = 0;
            $model->object_id = 0;
        } elseif (null !== $parent = Review::findOne(['id' => $parent_id])) {
            /** @var Review $parent */
            $model->parent_id = $parent_id;
            $model->object_id = $parent->object_id;
            $model->object_model_id = $parent->object_model_id;
            $model->root_id = $parent->root_id;
            $model->submission_id = $parent->submission_id;
        }

        if (true === Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(Url::toRoute(['view', 'id' => $model->id]));
            } else {
                // @todo add alert and may be something else here
            }
        }

        return $this->render('edit', [
            'review' => $model
        ]);
    }

    /**
     * @return array
     */
    public function actionAjaxSearch()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $search = \Yii::$app->request->get('search', []);
        $object = !empty($search['object']) ?  intval($search['object']) : 0;
        $term = !empty($search['term']) ?  $search['term'] : '';

        $result = [
            'more' => false,
            'results' => []
        ];

        if (null === $object = Object::findById($object)) {
            return $result;
        }

        /** @var ActiveRecord $class */
        $class = $object->object_class;
        $list = Object::find()->select("object_class")->column();
        if (!in_array($class, $list)) {
            return $result;
        }

        $query = $class::find()
            ->select('id, name, "#" `url`')
            ->andWhere(['like', 'name', $term])
            ->asArray(true);
        $result['results'] = array_values($query->all());
        array_walk($result['results'],
            function (&$val) use ($class)
            {
                if (null !== $model = $class::findOne(['id' => $val['id']])) {
                    if (Product::className() === $model->className()) {
                        $val['url'] = Url::toRoute([
                            '@product',
                            'model' => $model,
                            'category_group_id' => $model->category->category_group_id,
                        ]);
                    } elseif (Category::className() === $model->className()) {
                        $val['url'] = Url::toRoute([
                            '@category',
                            'last_category_id' => $model->id,
                            'category_group_id' => $model->category_group_id,
                        ]);
                    } else if (Page::className() === $model->className()) {
                        $val['url'] = Url::toRoute([
                            '@article',
                            'id' => $model->id,
                        ]);
                    }
                }
            });

        return $result;
    }

    /**
     * @return array
     */
    public function actionAjaxGetTree($root_id = null, $current_id = 0)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $q = Review::find()
            ->select('*')
            ->where(['root_id' => $root_id])
            ->orderBy(['parent_id' => SORT_ASC])
            ->asArray(true);

        $result = array_reduce($q->all(),
            function ($res, $item) use ($current_id)
            {
                $res[] = [
                    'id' => $item['id'],
                    'parent' => 0 === intval($item['parent_id']) ? '#' : $item['parent_id'],
                    'text' => $item['id'],
                    'type' => intval($item['id']) === intval($current_id) ? 'current' : 'leaf',
                    'a_attr' => [
                        'data-id' => $item['id'],
                    ]
                ];
                return $res;
            }, []);

        return $result;
    }
}
