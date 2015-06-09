<?php

namespace app\modules\data\controllers;

use app\backend\assets\KoAsset;
use app\backgroundtasks\helpers\BackgroundTasks;
use app\modules\data\components\commerceml\XmlFileReader;
use app\modules\data\models\CommercemlGuid;
use app\models\PropertyGroup;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\UploadedFile;

class CommercemlController extends Controller
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['data manage'],
                    ],
                ],
            ],
        ];
    }

    public function init()
    {
        parent::init();
        KoAsset::register($this->getView());
    }

    public function actionIndex()
    {
        if (\Yii::$app->request->isPost) {
            $uploaded = UploadedFile::getInstancesByName('cmlFile');
            $files = [];
            $date = time();
            $dir = \Yii::getAlias(\Yii::$app->getModule('data')->importDirPath . '/');
            if (!empty($uploaded)) {
                foreach ($uploaded as $k => $file) {
                    if ($file->saveAs($dir . "cml{$date}_{$k}.xml")) {
                        $files[] = $dir . "cml{$date}_{$k}.xml";
                    }
                }

                if (!empty($files)) {
                    $params = [
                        'files' => $files
                    ];
                    BackgroundTasks::addTask([
                        'name' => 'CommerceML import',
                        'description' => 'CommerceML import',
                        'action' => 'data/commerceml/import',
                        'params' => Json::encode($params),
                        'init_event' => 'import',
                    ],
                    [
                        'create_notification' => true
                    ]);
                }
            }
        }

        return $this->render('index');
    }

    public function actionConfigure()
    {

        if (\Yii::$app->request->isPost) {
            foreach (\Yii::$app->request->post('guidSelect', []) as $key => $value) {
                $item = CommercemlGuid::findOne(['id' => $key, 'type' => 'PROPERTY']);
                if (!empty($item)) {
                    $item->model_id = $value;
                    $item->save();
                }
            }

            if (null !== $file = UploadedFile::getInstanceByName('cmlFile')) {
                $xmlReader = new XmlFileReader($file->tempName);
                foreach($xmlReader->getProperties() as $item) {
                    $model = CommercemlGuid::findOne(['guid' => $item[XmlFileReader::ELEMENT_ID]]);
                    if (empty($model)) {
                        $guid = new CommercemlGuid();
                        $guid->type = 'PROPERTY';
                        $guid->guid = $item[XmlFileReader::ELEMENT_ID];
                        $guid->name = $item[XmlFileReader::ELEMENT_NAIMENOVANIE];
                        $guid->model_id = 0;
                        $guid->save();
                    }
                }
            }

            return $this->redirect('', 301);
        }

        $properties = array_reduce(CommercemlGuid::find()->where(['type' => 'PROPERTY'])->asArray()->all(),
            function($result, $item)
            {
                $result[$item['guid']] = $item;
                return $result;
            }, []);

        $propertiesGroups = array_reduce(PropertyGroup::findAll(['object_id' => 3]),
            function($result, $item)
            {
                return array_reduce($item->properties,
                    function ($result, $item)
                    {
                        if ($item->is_eav || $item->has_static_values) {
                            $result[] = ['id' => $item->id, 'name' => $item->name];
                        }
                        return $result;
                    }, $result);
            }, [ ['id' => 0, 'name' => ''] ]);

        return $this->render('configure', [
            'props' => $properties,
            'propsGroups' => $propertiesGroups,
        ]);
    }
}
?>