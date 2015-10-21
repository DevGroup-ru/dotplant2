<?php

namespace app\backend\controllers;

use app\models\Form;
use app\models\Object;
use app\models\ObjectPropertyGroup;
use app\models\PropertyGroup;
use app\models\Submission;
use app\properties\DynamicSearchModel;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\helpers\Url;

class FormController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['form manage'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new Form();
        $dataProvider = $searchModel->search($_GET);

        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionEdit($id = null)
    {
        $model = new Form();
        if ($id > 0) {
            $model = Form::findOne($id);
        }

        /** @var \app\models\Object $object */
        $object = Object::getForClass(Form::className());

        $propIds = (new Query())->select('property_group_id')->from([ObjectPropertyGroup::tableName()])->where(
            [
                'and',
                'object_id = :object',
                'object_model_id = :id'
            ],
            [
                ':object' => $object->id,
                ':id' => $id
            ]
        )->column();

        $post = \Yii::$app->request->post();
        $properties = isset($post['Form']['properties']) ? $post['Form']['properties'] : [];

        if ($model->load($post) && $model->validate()) {
            if ($model->save()) {
                $id = $model->id;
                $remove = [];
                $add = [];

                foreach ($propIds as $value) {
                    $key = array_search($value, $properties);
                    if ($key === false) {
                        $remove[] = $value;
                    } else {
                        unset($properties[$key]);
                    }
                }
                foreach ($properties as $value) {
                    $add[] = [
                        $value,
                        $object->id,
                        $id
                    ];
                }

                Yii::$app->db->createCommand()->delete(
                    ObjectPropertyGroup::tableName(),
                    [
                        'and',
                        'object_id = :object',
                        'object_model_id = :id',
                        ['in', 'property_group_id', $remove]
                    ],
                    [
                        ':object' => $object->id,
                        ':id' => $id,
                    ]
                )->execute();

                if (!empty($add)) {
                    Yii::$app->db->createCommand()->batchInsert(
                        ObjectPropertyGroup::tableName(),
                        ['property_group_id', 'object_id', 'object_model_id'],
                        $add
                    )->execute();
                }

                \Yii::$app->session->setFlash('info', Yii::t('app', 'Object saved'));
                $returnUrl = Yii::$app->request->get('returnUrl', ['/backend/form/index', 'id' => $model->id]);
                switch (Yii::$app->request->post('action', 'save')) {
                    case 'next':
                        return $this->redirect(
                            [
                                '/backend/form/edit',
                                'returnUrl' => $returnUrl,
                            ]
                        );
                    case 'back':
                        return $this->redirect($returnUrl);
                    default:
                        return $this->redirect(
                            Url::toRoute(
                                [
                                    '/backend/form/edit',
                                    'id' => $model->id,
                                    'returnUrl' => $returnUrl,
                                ]
                            )
                        );
                }
            } else {
                \Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot update data'));
            }
        }

        $items = ArrayHelper::map(
            PropertyGroup::find()->where(
                'object_id = :object',
                [
                    ':object' => $object->id,
                ]
            )->all(),
            'id',
            'name'
        );

        return $this->render(
            'edit',
            [
                'model' => $model,
                'items' => $items,
                'selected' => $propIds,
            ]
        );
    }

    public function actionView($id, $show_deleted = 0)
    {
        $form = Form::findById($id);
        $propertyGroups = $form->getPropertyGroups();
        $submission = new Submission();

        $dynamicModel = new DynamicSearchModel($submission, $propertyGroups);
        $data = $dynamicModel->search(Yii::$app->request->get());
        $data->query->andWhere('form_id = :form_id', [':form_id' => $form->id]);
        $data->query->andWhere(['is_deleted' => $show_deleted]);
        $data->query->andFilterWhere(['sending_status' => $dynamicModel->sending_status]);
        $data->query->andFilterWhere(['like', 'ip', $dynamicModel->ip]);
        $data->query->andFilterWhere(['like', 'user_agent', $dynamicModel->user_agent]);


        return $this->render(
            'view',
            [
                'searchModel' => $dynamicModel,
                'dataProvider' => $data,
                'form' => $form,
            ]
        );
    }
    
    public function actionViewSubmission($id)
    {
        $submission = Submission::findOne($id);
        if ($submission === null) {
            throw new NotFoundHttpException('Submission not found');
        }

        return $this->render(
            'view-submission',
            [
                'submission' => $submission,
            ]
        );
    }

    public function actionDeleteSubmission($id)
    {
        $submission = Submission::findOne($id);
        $form_id = $submission->form_id;
        if ($submission === null) {
            throw new NotFoundHttpException('Submission not found');
        }
        $res = false;
        if ($submission->is_deleted == 1) {
            if ($submission->delete()) {
                $res = true;
            }
        } else {
            $submission->setAttribute('is_deleted', 1);
            if ($submission->save()) {
                $res = true;
            }
        }
        if ($res === false) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Object not removed'));
        } else {
            Yii::$app->session->setFlash('info', Yii::t('app', 'Object removed'));
        }
        return $this->redirect(Url::toRoute(['view', 'id' => $form_id]));
    }

    public function actionRestoreSubmission($id)
    {
        $submission = Submission::findOne($id);
        if ($submission === null) {
            throw new NotFoundHttpException('Submission not found');
        }
        $submission->setAttribute('is_deleted', 0);
        $submission->save();
        Yii::$app->session->setFlash('success', Yii::t('app', 'Object successfully restored'));

        return $this->redirect(Url::toRoute(['view', 'id' => $submission->form_id]));
    }

    public function actionDownload($key, $submissionId)
    {
        $submission = Submission::findOne($submissionId);
        if ($submission === null) {
            throw new NotFoundHttpException('Submission not found');
        }
        $prop = $submission->getPropertyValuesByKey($key);
        return \Yii::$app->response->sendFile(
            Yii::getAlias(Yii::$app->getModule('core')->visitorsFileUploadPath) .
            DIRECTORY_SEPARATOR .
            $prop->values[0]['value']
        );
    }

    public function actionDelete($id = null)
    {
        if ((null === $id) || (null === $model = Form::findOne($id))) {
            throw new NotFoundHttpException;
        }

        if (!$model->delete()) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Object not removed'));
        } else {
            Yii::$app->session->setFlash('info', Yii::t('app', 'Object removed'));
        }

        return $this->redirect(Url::toRoute('index'));
    }

    public function actionRemoveAll()
    {
        $items = Yii::$app->request->post('items', []);
        if (!empty($items)) {
            $items = Form::find()->where(['in', 'id', $items])->all();
            foreach ($items as $item) {
                $item->delete();
            }
        }

        return $this->redirect(['index']);
    }
}
