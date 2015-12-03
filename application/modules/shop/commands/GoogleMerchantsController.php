<?php

namespace app\modules\shop\commands;

use app\modules\shop\components\GoogleMerchants\GoogleMerchants;
use yii\console\Controller;

class GoogleMerchantsController extends Controller
{
    public function actionGenerate($host, $siteName = '', $siteDescription = '', $fileName = 'feed.xml')
    {
        $gm = new GoogleMerchants([
            'host' => $host,
            'title' => $siteName,
            'description' => $siteDescription
        ]);
        return true === $gm->saveFeedInFs() ? 0 : 1;
    }
}
