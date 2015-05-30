<?php

namespace app\extensions\DefaultTheme\backend;

use app\backend\components\BackendController;
use app\backend\traits\BackendRedirect;
use app\extensions\DefaultTheme\models\ThemeActiveWidgets;
use app\extensions\DefaultTheme\models\ThemeParts;
use app\extensions\DefaultTheme\models\ThemeVariation;
use app\extensions\DefaultTheme\models\ThemeWidgets;
use app\traits\LoadModel;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ConfigurationController extends BackendController
{
    use LoadModel;
    use BackendRedirect;

    public function actionIndex()
    {
        $partsSearchModel = new ThemeParts();
        $partsDataProvider = $partsSearchModel->search($_GET);

        $variationsSearchModel = new ThemeVariation();
        $variationsDataProvider = $variationsSearchModel->search($_GET);

        return $this->render(
            'index',
            [
                'partsSearchModel' => $partsSearchModel,
                'partsDataProvider' => $partsDataProvider,
                'variationsSearchModel' => $variationsSearchModel,
                'variationsDataProvider' => $variationsDataProvider,
            ]
        );
    }

    public function actionEditPart($id)
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

    public function actionEditVariation($id)
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