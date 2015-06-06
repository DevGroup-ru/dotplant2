<?php

namespace app\modules\config\backend;

use app;
use app\modules\config\models\Configurable;
use app\modules\config\helpers\ConfigurationUpdater;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;
use yii\filters\AccessControl;


/**
 * Backend controller for modifying application and it's modules configuration.
 *
 * @package app\modules\config\controllers
 */
class ConfigController extends app\backend\components\BackendController
{
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
                        'roles' => ['setting manage'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists configurables by tabs and saves configuration
     * @return string
     * @throws \Exception
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionIndex()
    {
        /** @var Configurable[] $models */
        $models = Configurable::getDb()->cache(
            function ($db) {
                return Configurable::find()
                    ->orderBy([
                        'sort_order' => SORT_ASC,
                    ])
                    ->all($db);
            },
            86400,
            new TagDependency([
                'tags' => ActiveRecordHelper::getCommonTag(Configurable::className()),
            ])
        );

        foreach ($models as $model) {
            $configurableModel = $model->getConfigurableModel();
            $configurableModel->loadState();
        }

        if (Yii::$app->request->isPost === true) {
            if (ConfigurationUpdater::updateConfiguration($models, true)) {
                return $this->refresh();
            }
        }

        return $this->render(
            'index',
            [
                'models' => $models,
            ]
        );
    }
}