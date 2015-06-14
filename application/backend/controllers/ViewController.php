<?php

namespace app\backend\controllers;

use app\backend\traits\BackendRedirect;
use app\models\View;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ViewController extends Controller
{
    use BackendRedirect;

    protected function getTree($path = '', $level = 0)
    {
        if (is_null($this->view->theme) || !file_exists($this->view->theme->getBaseUrl())) {
            return [];
        }
        $result = [];
        $basePath = $this->view->theme->getBaseUrl();
        $dir = new \DirectoryIterator($basePath . $path);
        /** @var \DirectoryIterator $file */
        foreach ($dir as $file) {
            if ($file->isDot()) {
                continue;
            }
            $id = '#' . preg_replace('#[^\w\d]#', '_', $file->getFilename()) . "_lev{$level}";
            if ($file->isDir()) {
                $result[] = [
                    'id' => $id,
                    'children' => $this->getTree($path . DIRECTORY_SEPARATOR . $file->getBasename(), $level + 1),
                    'text' => $file->getBasename(),
                    'type' => 'dir',
                ];
            } elseif ($file->isFile() && 'php' === $file->getExtension()) {
                $result[] = [
                    'id' => $id,
                    'text' => $file->getBasename(),
                    'a_attr' => [
                        'data-file' => '@webroot/theme/views'.str_replace($basePath, '', $file->getRealPath()),
                        'data-toggle' => 'tooltip',
                        'title' => $file->getBasename()
                    ],
                    'type' => 'file',
                ];
            }
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['view manage'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'autocomplete' => [
                'class' => 'app\backend\actions\AutocompleteAction',
                'modelName' => 'app\models\View',
                'json_attributes' => ['name', 'id', 'category', 'internal_name',],
                'search_attributes' => ['name', 'category', 'internal_name'],
            ],
        ];
    }

    /*
     *
     */
    public function actionIndex()
    {
        $model = new View();
        return $this->render(
            'index',
            [
                'searchModel' => $model,
                'dataProvider' => $model->search(Yii::$app->request->get()),
            ]
        );
    }

    /*
     *
     */
    public function actionAdd($id = null)
    {
        $model = new View();
        if (null !== $id) {
            $id = intval($id);
            if (null !== View::findOne(['id' => $id])) {
                return $this->redirect(Url::toRoute(['edit', 'id' => $id]));
            }
            $model->id = $id;
        }

        if ($model->load(\Yii::$app->request->post())) {
            if ($model->save()) {
                return $this->redirectUser($model->id, true, 'edit');
            }
        }

        return $this->render(
            'edit',
            [
                'model' => $model
            ]
        );
    }

    /*
     *
     */
    public function actionEdit($id = null)
    {
        if ((null === $id) || (null === $model = View::findOne(['id' => $id]))) {
            return $this->redirect(Url::toRoute(['add', 'id' => $id]));
        }

        /** @var View $model */
        if ($model->load(\Yii::$app->request->post())) {
            if ($model->save()) {
                return $this->redirectUser($model->id);
            }
        }

        return $this->render(
            'edit',
            [
                'model' => $model
            ]
        );
    }

    public function actionDelete($id = null)
    {
        if ((null === $id) || (null === $model = View::findOne($id))) {
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
            $items = View::find()->where(['in', 'id', $items])->all();
            foreach ($items as $item) {
                $item->delete();
            }
        }

        return $this->redirect(['index']);
    }

    public function actionGetViews()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->getTree('', 0);
    }
}
