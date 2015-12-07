<?php

namespace app\modules\shop\commands;

use app\modules\shop\components\GoogleMerchants\GoogleMerchants;
use app\modules\shop\models\GoogleFeed;
use yii\console\Controller;

class GoogleMerchantsController extends Controller
{
    public function actionGenerate()
    {
        $gm = new GoogleMerchants();
        return true === $gm->saveFeedInFs() ? 0 : 1;
    }
}
