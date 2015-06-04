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

    protected function getTree($path = '')
    {
        if (is_null($this->view->theme) || !file_exists($this->view->theme->getBaseUrl())) {
            return [];
        }
        $result = [];
        $basePath = $this->view->theme->getBaseUrl();
        $dir = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($basePath . $path),
            \RecursiveIteratorIterator::CATCH_GET_CHILD
        );
        /** @var \SplFileInfo $file */
        foreach ($dir as $file) {
            if ($file->getBasename() == '.' || $file->getBasename() == '..') {
                continue;
            }
            $id = '#' . preg_replace('#[^\w\d]#', '_', str_replace($basePath, '', $file->getFilename()));
            if ($file->isDir()) {
                $result[] = [
                    'id' => $id,
                    'children' => $this->getTree($path . DIRECTORY_SEPARATOR . $file->getBasename()),
                    'text' => $file->getBasename(),
                    'type' => 'dir',
                ];
            } elseif ($file->isFile() && ('.php' === substr($file->getBasename(), -4))) {
                $result[] = [
                    'id' => $id,
                    'text' => $file->getBasename(),
                    'a_attr' => [
                        'data-file' => str_replace($basePath, '', $file->getPath()),
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
        return $this->render(
            'index',
            [
                'searchModel' => $_model = new View(),
                'dataProvider' => $_model->search(Yii::$app->request->get()),
            ]
        );
    }

    /*
     *
     */
    public function actionAdd()
    {
        $model = new View();

        $post = \Yii::$app->request->post();
        if ($model->load($post) && $model->validate()) {
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

    /*
     *
     */
    public function actionEdit($id = null)
    {
        if ((null === $id) || (null === $model = View::findOne(['id' => $id]))) {
            $model = new View();
        }

        $post = \Yii::$app->request->post();

        if ($model->load($post) && $model->validate()) {
            $model->save();
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
        return $this->getTree();
    }
}
