<?php

namespace app\backend\controllers;

use app\models\Config;
use Yii;
use yii\web\Controller;

class YmlController extends Controller
{
    public function actionSettings()
    {
        if (!empty($_POST['yml'])) {
            $yml = $_POST['yml'];


            $config = Config::find()->where(['key' => 'show_all_properties'])->one();
            if ($config) {
                if (isset($yml['show_all_properties'])) {
                    $config->value = "1";
                    unset($yml['show_all_properties']);
                } else {
                    $config->value = "0";
                }

                $config->update();
            }

            foreach ($yml as $key => $value) {
                $config = Config::find()->where(['key' => $key])->one();
                if ($config) {
                    $config->value = $value;
                    $config->update();
                }
            }

            if (isset($_POST['data'])) {

                $ymlDataFields = [];

                foreach ($_POST['data'] as $key => $one) {
                    if(isset($one['type']) && $one['type'] ) {
                        $ymlDataFields[$key] = $one;
                    }
                }
                $config =  Config::find()->where(['key'=>'fields_params'])->one();
                $config->value = json_encode($ymlDataFields);
                $config->save();
            }



        }

        return $this->render('settings',
            [
                'main_currency' => Config::getValue("yml.main_currency"),
                'show_all_properties' => Config::getValue("yml.show_all_properties"),
                'default_offer_type' => Config::getValue("yml.default_offer_type"),
                'local_delivery_cost' => Config::getValue("yml.local_delivery_cost"),
                'fields_params' => Config::getValue('yml.fields_params')
            ]
        );
    }
}
