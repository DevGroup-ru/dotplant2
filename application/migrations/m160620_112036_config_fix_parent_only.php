<?php

use app\modules\shop\models\ConfigConfigurationModel;
use yii\db\Migration;
use yii\helpers\ArrayHelper;

class m160620_112036_config_fix_parent_only extends Migration
{
    public function up()
    {
        $fileName = Yii::getAlias('@app/config/web-configurables.php');
        if (true === is_file($fileName)) {
            $array = include_once($fileName);
            if (true === is_array($array) &&
                null !== $onlyParent = ArrayHelper::getValue($array, 'modules.shop.filterOnlyByParentProduct', null)
            ) {
                unset($array['modules']['shop']['filterOnlyByParentProduct']);
                $array['modules']['shop']['productsFilteringMode'] = $onlyParent === true ?
                    ConfigConfigurationModel::FILTER_PARENTS_ONLY :
                    ConfigConfigurationModel::FILTER_CHILDREN_ONLY;

                $writer = new \app\modules\config\helpers\ApplicationConfigWriter([
                    'filename' => '@app/config/web-configurables.php',
                    'loadExistingConfiguration' => false,
                ]);
                $writer->addValues($array);
                $writer->commit();

            } else {
                echo "file @app/config/web-configurables.php cannot be changed. \n";
            }
        }


        $shopFilename = Yii::getAlias('@app/config/configurables-state/shop.php');
        if (true === file_exists($shopFilename)) {
            $shopConfigurablesArray = include($shopFilename);
            if (true === is_array($shopConfigurablesArray) &&
                null !== $onlyParent = ArrayHelper::getValue($shopConfigurablesArray, 'filterOnlyByParentProduct', null)
            ) {
                unset($shopConfigurablesArray['filterOnlyByParentProduct']);
                $shopConfigurablesArray['productsFilteringMode'] = $onlyParent === true ?
                    ConfigConfigurationModel::FILTER_PARENTS_ONLY :
                    ConfigConfigurationModel::FILTER_CHILDREN_ONLY;
                $writer = new \app\modules\config\helpers\ApplicationConfigWriter([
                    'filename' => '@app/config/configurables-state/shop.php',
                    'loadExistingConfiguration' => false,
                ]);
                $writer->addValues($shopConfigurablesArray);
                $writer->commit();
            } else {
                echo " file@app/config/configurables-state/shop.php cannot be changed. \n";
            }
        }

    }

    public function down()
    {
        $fileName = Yii::getAlias('@app/config/web-configurables.php');
        if (true === is_file($fileName)) {
            $array = include_once($fileName);
            if (true === is_array($array) &&
                null !== $productsFilteringMode = ArrayHelper::getValue($array,
                    'modules.shop.productsFilteringMode', null)
            ) {
                unset($array['modules']['shop']['productsFilteringMode']);
                $array['modules']['shop']['filterOnlyByParentProduct'] =
                    $productsFilteringMode !== ConfigConfigurationModel::FILTER_CHILDREN_ONLY ?
                        true :
                        false;

                $writer = new \app\modules\config\helpers\ApplicationConfigWriter([
                    'filename' => '@app/config/web-configurables.php',
                    'loadExistingConfiguration' => false,
                ]);
                $writer->addValues($array);
                $writer->commit();

            } else {
                echo "file @app/config/web-configurables.php cannot be revert. \n";
            }
        }


        $shopFilename = Yii::getAlias('@app/config/configurables-state/shop.php');
        if (true === file_exists($shopFilename)) {
            $shopConfigurablesArray = include($shopFilename);
            if (true === is_array($shopConfigurablesArray) &&
                null !== $productsFilteringMode = ArrayHelper::getValue($shopConfigurablesArray,
                    'productsFilteringMode', null)
            ) {
                unset($shopConfigurablesArray['productsFilteringMode']);
                $shopConfigurablesArray['filterOnlyByParentProduct'] =
                    $productsFilteringMode !== ConfigConfigurationModel::FILTER_CHILDREN_ONLY ?
                        true :
                        false;

                $writer = new \app\modules\config\helpers\ApplicationConfigWriter([
                    'filename' => '@app/config/configurables-state/shop.php',
                    'loadExistingConfiguration' => false,
                ]);
                $writer->addValues($shopConfigurablesArray);
                $writer->commit();
            } else {
                echo "file @app/config/configurables-state/shop.php cannot be revert. \n";
            }
        }


    }
}
