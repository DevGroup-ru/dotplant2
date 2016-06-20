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
               null  !== $onlyParent = ArrayHelper::getValue($array, 'modules.shop.filterOnlyByParentProduct', null)
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
            }
        }

    }

    public function down()
    {
        echo "m160620_112036_config_fix_parent_only cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
