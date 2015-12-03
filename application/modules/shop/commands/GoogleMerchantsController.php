<?php

namespace app\modules\shop\commands;

use app\modules\shop\components\GoogleMerchants\GoogleMerchants;
use yii\console\Controller;

class GoogleMerchantsController extends Controller
{
    public function actionGenerate()
    {
        $gm = new GoogleMerchants();
        return true === $gm->generate() ? 0 : 1;
    }
}
