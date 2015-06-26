<?php

namespace app\extensions\DefaultTheme\backend;

use app\backend\components\BackendController;
use app\backend\traits\BackendRedirect;
use app\extensions\DefaultTheme\models\BaseWidgetConfigurationModel;
use app\extensions\DefaultTheme\models\ThemeActiveWidgets;
use app\extensions\DefaultTheme\models\ThemeParts;
use app\extensions\DefaultTheme\models\ThemeVariation;
use app\extensions\DefaultTheme\models\ThemeWidgets;
use app\extensions\DefaultTheme\models\WidgetConfigurationModel;
use app\traits\LoadModel;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\base\DynamicModel;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ConfigurationController extends BackendController
{
    use LoadModel;
    use BackendRedirect;

    /**
     * Lists theme parts, variations and widgets
     * @return string
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionIndex()
    {
        $partsSearchModel = new ThemeParts();
        $partsDataProvider = $partsSearchModel->search($_GET);

        $variationsSearchModel = new ThemeVariation();
        $variationsDataProvider = $variationsSearchModel->search($_GET);

        $widgetsSearchModel = new ThemeWidgets();
        $widgetsDataProvider = $widgetsSearchModel->search($_GET);

        return $this->render(
            'index',
            [
                'partsSearchModel' => $partsSearchModel,
                'partsDataProvider' => $partsDataProvider,
                'variationsSearchModel' => $variationsSearchModel,
                'variationsDataProvider' => $variationsDataProvider,
                'widgetsSearchModel' => $widgetsSearchModel,
                'widgetsDataProvider' => $widgetsDataProvider,
            ]
        );
    }

    public function actionEditPart($id='')
    {
        $model = $this->loadModel(ThemeParts::className(), $id, true);
        if ($model->isNewRecord === true) {
            $model->loadDefaultValues();
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save()) {
                return $this->redirectUser($model->id, true, 'index', 'edit-part');
            }
        }

        return $this->render(
            'edit-part',
            [
                'model' => $model,
            ]
        );
    }

    public function actionDeletePart($id)
    {
        /** @var ThemeParts $model */
        $model = ThemeParts::findOne($id);
        if (is_null($model)) {
            throw new NotFoundHttpException;
        }
        $model->delete();
        return $this->redirect(['index']);
    }

    public function actionEditVariation($id='')
    {
        $model = $this->loadModel(ThemeVariation::className(), $id, true);
        if ($model->isNewRecord === true) {
            $model->loadDefaultValues();
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save()) {
                return $this->redirectUser($model->id, true, 'index', 'edit-variation');
            }
        }

        return $this->render(
            'edit-variation',
            [
                'model' => $model,
            ]
        );
    }

    public function actionDeleteVariation($id)
    {
        /** @var ThemeVariation $model */
        $model = ThemeVariation::findById($id);
        if (is_null($model)) {
            throw new NotFoundHttpException;
        }
        $model->delete();
        return $this->redirect(['index']);
    }

    public function actionEditWidget($id='')
    {
        $model = new ThemeWidgets;
        if (!empty($id)) {
            $model = ThemeWidgets::find()
                ->where(['theme_widgets.id'=>$id])
                ->joinWith('applying.part')
                ->one();
            if ($model === null) {
                throw new NotFoundHttpException;
            }
        }
        if ($model->isNewRecord === true) {
            $model->loadDefaultValues();
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            if ($model->save()) {


                $newPart = Yii::$app->request->post('new-part', null);
                if ($newPart !== null) {
                    /** @var ThemeParts $themePart */
                    $themePart = ThemeParts::findOne($newPart);
                    if ($themePart!==null) {
                        $model->link('applicableParts', $themePart);
                    }
                }

                return $this->redirectUser($model->id, true, 'index', 'edit-widget');
            }
        }

        return $this->render(
            'edit-widget',
            [
                'model' => $model,
            ]
        );
    }

    public function actionDeleteWidget($id)
    {
        /** @var ThemeWidgets $model */
        $model = ThemeWidgets::findOne($id);
        if (is_null($model)) {
            throw new NotFoundHttpException;
        }
        $model->delete();
        return $this->redirect(['index']);
    }

    public function actionRemoveApplying($id, $part_id)
    {
        $model = $this->loadModel(ThemeWidgets::className(), $id);
        /** @var ThemeParts $themePart */
        $themePart = ThemeParts::findOne($part_id);
        if ($themePart === null) {
            throw new NotFoundHttpException;
        }
        $model->unlink('applicableParts', $themePart);
        return $this->redirect(['edit-widget', 'id'=>$model->id]);
    }

    public function actionConfigureJson($id, $returnUrl = '')
    {
        /** @var ThemeActiveWidgets $model */
        $model = $this->loadModel(ThemeActiveWidgets::className(), $id);
        /** @var ThemeWidgets $widget */
        $widget = ThemeWidgets::findById($model->widget_id);

        /** @var WidgetConfigurationModel|null|BaseWidgetConfigurationModel $configurationModel */
        $configurationModel = null;
        if (!empty($widget->configuration_model) && !empty($widget->configuration_view)) {
            $className = $widget->configuration_model;

            $configurationModel = new $className;
            $configurationModel->setAttributes(Json::decode($model->configuration_json));
        } else {
            $configurationModel = new BaseWidgetConfigurationModel();
            $configurationModel->loadState(Json::decode($widget->configuration_json));
        }


        $isAjax = Yii::$app->request->isAjax;
        $isValid = true;
        if ($model->load(Yii::$app->request->post())) {
            $isValid = $model->validate();
        }

        if ($isValid && $configurationModel !== null) {

            $configurationModel->load(Yii::$app->request->post());

            if ($configurationModel->validate()) {
                if ($configurationModel instanceof BaseWidgetConfigurationModel) {
                    $json = Json::decode($configurationModel->configurationJson);
                    $json['header'] = $configurationModel->header;
                    $json['displayHeader'] = $configurationModel->displayHeader;
                    $model->configuration_json = Json::encode($json);
                } else {
                    $model->configuration_json = Json::encode($configurationModel->getAttributes());
                }
            } else {
                $isValid = false;
            }
        }
        if (Yii::$app->request->isPost && $isValid && $model->save()) {
            return $this->redirectUser($model->id, true, 'index', 'configure-json');
        }

        $data = [
            'model' => $model,
            'isAjax' => $isAjax,
            'widget' => $widget,
            'configurationModel' => $configurationModel,
        ];

        if ($isAjax === true) {
            return $this->renderAjax('configure-json', $data);
        } else {
            return $this->render('configure-json', $data);
        }


    }

    public function actionActiveWidgets($id)
    {
        $variation = $this->loadModel(ThemeVariation::className(), $id);

        if (isset($_POST['addWidget'], $_POST['partId'])) {
            $part = ThemeParts::findById($_POST['partId']);
            if ($part === null) {
                throw new NotFoundHttpException;
            }
            $widget = ThemeWidgets::findById($_POST['addWidget']);
            if ($widget === null) {
                throw new NotFoundHttpException;
            }

            $binding = new ThemeActiveWidgets();
            $binding->part_id = $part->id;
            $binding->variation_id = $variation->id;
            $binding->widget_id = $widget->id;
            $binding->save();
        }

        $models = ThemeActiveWidgets::find()
            ->where(['variation_id' => $variation->id])
            ->orderBy(['part_id' => SORT_ASC, 'sort_order' => SORT_ASC])
            ->all();

        // Warning! Element of $allParts is not a model! It's an array of db rows
        $allParts = ThemeParts::getAllParts();

        $availableWidgets = ThemeWidgets::find()
            ->joinWith('applying')
            ->all();


        return $this->render(
            'active-widgets',
            [
                'variation' => $variation,
                'models' => $models,
                'availableWidgets' => $availableWidgets,
                'allParts' => $allParts,
            ]
        );
    }

    public function actionDeleteActiveWidget($id)
    {
        /** @var ThemeActiveWidgets $widget */
        $widget = $this->loadModel(ThemeActiveWidgets::className(), $id);

        $widget->delete();
        return $this->redirect(['active-widgets', 'id'=>$widget->variation_id]);
    }

    public function actionSaveSorted()
    {
        if (Yii::$app->request->isPost === false || !isset($_POST['ids'])) {
            throw new BadRequestHttpException;
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        $ids = (array) $_POST['ids'];

        $result = ThemeActiveWidgets::sortModels($ids);

        $this->invalidateTags(ThemeActiveWidgets::className(), $ids);


        return $result;
    }

    private function invalidateTags($className, $ids)
    {
        $tags = [
            ActiveRecordHelper::getCommonTag($className),
        ];
        foreach ($ids as $id) {
            $tags[] = ActiveRecordHelper::getObjectTag($className, $id);
        }
        \yii\caching\TagDependency::invalidate(
            Yii::$app->cache,
            $tags
        );
    }
}