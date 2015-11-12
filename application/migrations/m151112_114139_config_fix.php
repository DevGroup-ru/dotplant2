<?php

use yii\db\Migration;

class m151112_114139_config_fix extends Migration
{
    public function up()
    {
        $fileName = Yii::getAlias('@app/config/web-configurables.php');
        if (true === is_file($fileName)) {
            $array = include_once($fileName);
            if (true === is_array($array)) {
                $array = array_replace_recursive(
                    $array, [
                        'modules' => [
                            'seo' => [
                                'analytics' => [
                                    'ecGoogle' => [
                                        'active' => 0,
                                        'currency' => \app\modules\seo\handlers\AnalyticsHandler::CURRENCY_MAIN,
                                    ],
                                    'ecYandex' => [
                                        'active' => 0,
                                        'currency' => \app\modules\seo\handlers\AnalyticsHandler::CURRENCY_MAIN,
                                    ],
                                ]
                            ]
                        ]
                    ]);

                $writer = new \app\modules\config\helpers\ApplicationConfigWriter([
                    'filename' => '@app/config/web-configurables.php',
                    'loadExistingConfiguration' => false,
                ]);
                $writer->addValues($array);
                $writer->commit();
            }
        }

        $fileName = Yii::getAlias('@app/config/configurables-state/seo.php');
        if (true === is_file($fileName)) {
            $array = include_once($fileName);
            if (true === is_array($array)) {
                $array = array_replace_recursive(
                    $array, [
                        'analytics' => [
                            'ecGoogle' => [
                                'active' => 0,
                                'currency' => \app\modules\seo\handlers\AnalyticsHandler::CURRENCY_MAIN,
                            ],
                            'ecYandex' => [
                                'active' => 0,
                                'currency' => \app\modules\seo\handlers\AnalyticsHandler::CURRENCY_MAIN,
                            ],
                        ]
                    ]);

                $writer = new \app\modules\config\helpers\ApplicationConfigWriter([
                    'filename' => '@app/config/configurables-state/seo.php',
                    'loadExistingConfiguration' => false,
                ]);
                $writer->addValues($array);
                $writer->commit();
            }
        }
    }

    public function down()
    {
        echo "m151112_114139_config_fix cannot be reverted.\n";
        return false;
    }
}
