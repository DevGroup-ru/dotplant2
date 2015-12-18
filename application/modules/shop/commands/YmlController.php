<?php
namespace app\modules\shop\commands;

use app\modules\shop\models\Yml;
use Yii;

class YmlController extends \yii\console\Controller
{
    /**
     * @return bool
     */
    public function actionGenerate()
    {
        $config = new Yml();
        if (false === $config->loadConfig()) {
            return false;
        }

        $yml = new \app\modules\shop\components\yml\Yml($config);
        return true === $yml->generate() ? 0 : 1;
    }
}
