<?php

namespace app\modules\config\controllers;

use app;
use app\modules\config\models\Configurable;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use yii\caching\TagDependency;
use yii\filters\AccessControl;

/**
 * Backend controller for modifying application and it's modules configuration.
 *
 * @package app\modules\config\controllers
 */
class BackendController extends app\backend\components\BackendController
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

    public function actionIndex()
    {
        $models = Configurable::getDb()->cache(
            function ($db) {
                return Configurable::find()->all($db);
            },
            86400,
            new TagDependency([
                'tags' => ActiveRecordHelper::getCommonTag(Configurable::className()),
            ])
        );

        return $this->render(
            'index',
            [
                'models' => $models,
            ]
        );
    }
}