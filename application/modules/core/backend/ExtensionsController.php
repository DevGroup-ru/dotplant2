<?php

namespace app\modules\core\backend;

use app;
use app\backend\components\BackendController;
use app\modules\core\models\Extensions;
use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;


class ExtensionsController extends BackendController
{
    public function actionIndex()
    {
        $searchModel = new Extensions();
        $dataProvider = $searchModel->search($_GET);

        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionExplore($q='', $p=1)
    {
        $client = new app\modules\core\components\PackagistClient();
        $packages = $client->search($q, ['type'=>'yii2-extension'], $p);
    }

    public function actionUpdateComposer()
    {
        /** @var app\modules\core\helpers\UpdateHelper $updateHelper */
        $updateHelper = Yii::$app->updateHelper;
        $process = $updateHelper->updateComposer();
        $process->mustRun();
        echo "<PRE>" . $process->getOutput() . $process->getErrorOutput() . "</PRE>";
    }

    public function actionInstallExtension($name)
    {
        /** @var app\modules\core\helpers\UpdateHelper $updateHelper */
        $updateHelper = Yii::$app->updateHelper;
        /** @var Extensions $extension */
        $extension = Extensions::find()
            ->where(['name' => $name])
            ->one();
        if ($extension === null) {
            throw new NotFoundHttpException;
        }

        $moduleClassName = $extension->namespace_prefix . 'Module';
        if (class_exists($moduleClassName) === true) {
            $moduleClassName::installModule(false, false);
        } else {
            throw new ServerErrorHttpException("Extension module class not found.");
        }
    }
}