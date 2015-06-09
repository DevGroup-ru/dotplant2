<?php

namespace app\modules\seo\controllers;


use app\backend\components\BackendController;
use app\modules\seo\models\OpenGraphObject;
use Yii;
use yii\data\ActiveDataProvider;

class OpenGraphController extends BackendController
{
    public function actionIndex()
    {

        $provider = new ActiveDataProvider([
            'query' => OpenGraphObject::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', ['provider' => $provider]);
    }

    public function actionEdit($id)
    {

        $model = OpenGraphObject::findOne($id);
        /** @var OpenGraphObject $model */


        if (Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->post());
            $model->relation_data = json_encode(Yii::$app->request->post('data'));
            if ($model->save()) {
                $this->refresh();
            }
        }


        $openGraphFields = [
            [
                'key' => 'title',
                'label' => Yii::t('app', 'Title'),
                'required' => true
            ],
            [
                'key' => 'image',
                'label' => Yii::t('app', 'Image'),
                'required' => true
            ],
            [
                'key' => 'description',
                'label' => Yii::t('app', 'Description'),
                'required' => true
            ],
        ];

        $relationLinks = [
            [
                'class' => \app\modules\image\models\Image::className(),
                'relationName' => 'getImages'
            ],
        ];

        return $this->render('form', [
            'openGraphFields' => $openGraphFields,
            'relationLinks' => $relationLinks,
            'model' => $model
        ]);
    }

}