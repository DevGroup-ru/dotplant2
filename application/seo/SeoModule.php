<?php

namespace app\seo;

use app\backend\BackendModule;
use app\seo\controllers\ManageController;
use app\seo\models\Counter;
use Yii;
use yii\base\BootstrapInterface;
use yii\web\Application;
use yii\web\View;

class SeoModule extends BackendModule implements BootstrapInterface
{
    public $cacheConfig = [
        'metaCache' => [
            'name' => 'metas',
            'expire' => 86400,
        ],
        'counterCache' => [
            'name' => 'counters',
            'expire' => 86400,
        ],
        'robotsCache' => [
            'name' => 'robots',
            'expire' => 86400,
        ],
    ];
    public $include = [];
    public $mainPage = '';

    public function bootstrap($app)
    {
        $app->on(
            Application::EVENT_BEFORE_REQUEST,
            function () use ($app) {
                $app->getView()->on(View::EVENT_END_BODY, [Counter::className(), 'renderCounters'], $this->include);
            }
        );

        if (null !== $orderId = Yii::$app->session->get('cartOrderId')) {
            $app->on(
                Application::EVENT_BEFORE_REQUEST,
                function () use ($app, $orderId) {
                    $app->getView()->on(View::EVENT_END_BODY, [ManageController::className(), 'renderEcommerceCounters'], ['orderId' => $orderId]);
                }
            );

            Yii::$app->session->remove('cartOrderId');
        }
    }
}
