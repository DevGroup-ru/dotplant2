<?php

namespace app\backend\controllers;

use app\backend\traits\BackendRedirect;
use app\models\Slide;
use app\slider\BaseSliderEditModel;
use Imagine\Image\ManipulatorInterface;
use kartik\icons\Icon;
use Yii;
use app\models\Slider;
use app\components\SearchModel;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * SliderController implements the CRUD actions for Slider model.
 */
class SliderController extends Controller
{
    use BackendRedirect;
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
                        'roles' => ['content manage'],
                    ],
                ],
            ],
        ];
    }


    /**
     * Lists all Slider models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SearchModel(
            [
                'model' => Slider::className(),
                'partialMatchAttributes' => ['name'],
                'scenario' => 'default',
            ]
        );
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render(
            'index',
            [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]
        );
    }

    /**
     * Updates an existing Slider model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param null|string $id
     * @return mixed
     */
    public function actionUpdate($id = null)
    {
        $abstractModel = new BaseSliderEditModel();

        if (is_null($id)) {
            $model = new Slider;
            $model->loadDefaultValues();
        } else {
            $model = $this->findModel($id);
            if ($model->handler() !== null) {
                $abstractModel = Yii::createObject(['class'=>$model->handler()->edit_model]);
                if (!empty($model->params)) {
                    $abstractModel->unserialize($model->params);
                }
            }
        }

        $post = Yii::$app->request->post();

        if ($model->load($post) && $model->validate()) {
            if ($model->handler() !== null) {
                $abstractModel = Yii::createObject(['class'=>$model->handler()->edit_model]);
                if (!empty($model->params)) {
                    $abstractModel->unserialize($model->params);
                }
            }
            $abstractModel->load($post);
            if ($abstractModel->validate()) {
                $model->params = $abstractModel->serialize();
                if ($model->save()) {
                    return $this->redirectUser($model->id, true, 'index', 'update');

                } else {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot save data'));
                }
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot save data'));
            }
        }

        $searchModel = new Slide();
        $searchModel->slider_id = $model->id;
        $dataProvider = $searchModel->search($_GET);

        return $this->render(
            'update',
            [
                'model' => $model,
                'abstractModel' => $abstractModel,
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    /**
     * @param integer $slider_id
     */
    public function actionNewSlide($slider_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $slider = $this->findModel($slider_id);
        $model = new Slide();
        $model->slider_id = $slider->id;
        $model->active = 0;
        $model->sort_order = count($slider->getSlides());
        $model->save();
        return $this->redirect(['update', 'id' => $slider_id]);
    }

    /**
     * Update slide attributes
     * @return array
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionUpdateSlide()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $post = Yii::$app->request->post();

        if (!isset($post['editableIndex'], $post['editableKey'])) {
            throw new BadRequestHttpException;
        }
        $id = $post['editableKey'];
        /** @var Slide $model */
        $model = Slide::findOne($id);
        if ($model === null) {
            throw new BadRequestHttpException;
        }
        $index = $post['editableIndex'];
        if (!is_array($post['Slide'][$index])) {
            throw new BadRequestHttpException;
        }
        if (count($post['Slide'][$index])===0) {
            throw new BadRequestHttpException;
        }

        $modifiedAttribute = array_keys($post['Slide'][$index])[0];

        $model->setAttributes($post['Slide'][$index]);


        if (!$model->save()) {
            return [
                'message' => Yii::t('app', 'Cannot save object'),
            ];
        }

        if ($modifiedAttribute === 'active') {
            $model->active ? Icon::show('check txt-color-green') : Icon::show('times txt-color-red');
        }

        return [
            'output' => $model->getAttribute($modifiedAttribute),
        ];
    }

    public function actionDeleteSlide($id)
    {
        $slide = Slide::findOne($id);
        $slider_id = $slide->slider_id;
        $slide->delete();
        return $this->redirect(['update', 'id'=>$slider_id]);
    }

    public function actionUploadSlide()
    {
        if (!isset($_POST['slide_id'], $_POST['attribute'], $_POST['slider_id'])) {
            throw new BadRequestHttpException();
        }

        $model = Slider::findById($_POST['slider_id']);
        $slide = Slide::findOne($_POST['slide_id']);
        if ($model === null || $slide === null) {
            throw new NotFoundHttpException;
        }

        $file = UploadedFile::getInstanceByName('file');
        if ($file === null) {
            throw new HttpException(500, "Upload file error");
        }
        if ($file->hasError) {
            throw new HttpException(500, 'Upload error');
        }

        $fileName = $file->name;
        $uploadDir = Yii::getAlias(Yii::$app->getModule('core')->fileUploadPath);
        $fn = $uploadDir . $fileName;
        if (file_exists($fn)) {
            $fileName = $file->baseName . '-' . uniqid() . '.' . $file->extension;
            $fn = $uploadDir . $fileName;
        }


        $file->saveAs($fn);

        $image = \yii\imagine\Image::thumbnail($uploadDir . $fileName,
            $model->image_width,
            $model->image_height,
            ManipulatorInterface::THUMBNAIL_INSET
        );
        $image->save($uploadDir . 'small-' . $fileName, ['quality'=>95]);

        $slide->setAttribute($_POST['attribute'], str_replace(Yii::getAlias('@webroot'), '', $uploadDir) . 'small-' . $fileName);
        $slide->save();

        return $this->redirect(['update', 'id' => $model->id]);
    }

    /**
     * Deletes an existing Slider model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Slider model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Slider the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Slider::findById($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}