<?php

namespace app\backend\controllers;

use Yii;
use yii\caching\TagDependency;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\models\View;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ViewController extends Controller
{
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
            $model->save();

            $this->redirect(Url::toRoute(['edit', 'id' => $model->id]));
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

    /*
     *
     */
    public function actionGetViews()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (is_dir($_dir = $this->view->theme->getBaseUrl())) {
            $cacheKey = 'ViewDirectoryTree';
            if (false === $items = Yii::$app->cache->get($cacheKey)) {
                $rdir = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($this->view->theme->getBaseUrl()),
                    \RecursiveIteratorIterator::SELF_FIRST
                );

                $items = [];
                /** @var \SplFileInfo $file */
                foreach ($rdir as $file) {
                    if (($file->getBasename() === '.') || ($file->getBasename() === '..')) {
                        continue;
                    }

                    $_parent = '#';
                    if ($_dir !== $file->getPath()) {
                        $_parent = '#' . array_pop(explode('/', str_replace($_dir, '', $file->getPath())));
                    }

                    if ($file->isDir()) {
                        $_name = $file->getBasename();
                        $items[] = [
                            'id' => '#' . $_name,
                            'parent' => $_parent,
                            'text' => $_name,
                            'type' => 'dir',
                        ];
                    } elseif ($file->isFile() && ('.php' === substr($file->getBasename(), -4))) {
                        $_name = $file->getBasename('.php');
                        $_attr = trim(str_replace($_dir, '', $file->getPath()), '/');

                        $_title = $_name;
                        if (false !== $_cnt = file_get_contents($file->getRealPath())) {
                            if (preg_match('#\<\\?(php)?\s*/\*(.+)\*/#siU', $_cnt, $_match)) {
                                foreach (explode(PHP_EOL, $_match[2]) as $_line) {
                                    if (preg_match('#@theme-name\s*(.+)$#iU', $_line, $_match)) {
                                        $_title = trim($_match[1]);
                                    }
                                }
                            }
                        }

                        $items[] = [
                            'id' => $_parent . $_name,
                            'parent' => $_parent,
                            'text' => $_name,
                            'a_attr' => [
                                'data-file' => '/' . $_attr . '/' . $_name,
                                'data-toggle' => 'tooltip',
                                'title' => $_title
                            ],
                            'type' => 'file',
                        ];
                    }
                }

                Yii::$app->cache->set(
                    $cacheKey,
                    $items,
                    86400,
                    new TagDependency(
                        [
                            'tags' => [\app\behaviors\TagDependency::getCommonTag(View::className())]
                        ]
                    )
                );
            }

            return $items;
        }

        return '';
    }
}
