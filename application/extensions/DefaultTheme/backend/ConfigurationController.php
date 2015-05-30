<?php

namespace app\extensions\DefaultTheme\backend;

use app\backend\components\BackendController;
use app\backend\traits\BackendRedirect;
use app\extensions\DefaultTheme\models\ThemeActiveWidgets;
use app\extensions\DefaultTheme\models\ThemeParts;
use app\extensions\DefaultTheme\models\ThemeVariation;
use app\extensions\DefaultTheme\models\ThemeWidgets;
use app\traits\LoadModel;
use Yii;

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
        $models = ThemeActiveWidgets::find()
            ->where(['variation_id' => $variation->id])
            ->orderBy(['part_id' => SORT_ASC, 'sort_order' => SORT_ASC])
            ->all();

        // Warning! Element of $allParts is not a model! It's an array(db row)
        $allParts = ThemeParts::getAllParts();

        $availableWidgets = ThemeWidgets::find()
            ->joinWith('applying')
            ->where(['theme_widget_applying.part_id'=>$variation->id])
            ->all();


        return $this->render(
            'active-widgets',
            [
                'variation' => $variation,
                'models' => $models,
                'availableWidgets' => $availableWidgets,
                'allVariations' => $allParts,
            ]
        );
    }
}