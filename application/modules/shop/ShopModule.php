<?php

namespace app\modules\shop;

use app;
use app\components\BaseModule;
use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\web\UserEvent;

/**
 * Shop module is the base core module of DotPlant2 CMS handling all common e-commerce features
 * @package app\modules\shop
 */
class ShopModule extends BaseModule implements BootstrapInterface
{
    const BACKEND_PRODUCT_GRID = 'productEditGrid';
    const BACKEND_CATEGORY_GRID = 'categoryEditGrid';

    /**
     * @inheritdoc
     */
    public $controllerMap = [
        'backend-filter-sets' => 'app\modules\shop\backend\FilterSetsController',
    ];

    /**
     * @var int How much products per page to show
     */
    public $productsPerPage = 15;

    /**
     * @var string How show products in category
     */
    public $listViewType = 'blockView';
    /**
     * @var int How much products allow to compare at once
     */
    public $maxProductsToCompare = 3;

    /**
     * @var bool Should we show and query for products of subcategories
     */
    public $showProductsOfChildCategories = 1;

    /**
     * @var int How much products to show on search results page
     */
    public $searchResultsLimit = 9;

    /**
     * @var boolean Possible to search generated products
     */
    public $allowSearchGeneratedProducts = 0;

    /***
     * @var bool registration Guest User In Cart as new user and send data on e-mail
     */
    public $registrationGuestUserInCart = 0;

    /**
     * @var bool Show delete order in backend
     */
    public $deleteOrdersAbility = 0;

    /**
     * @var bool Filtration works only on parent products but not their children
     */
    public $filterOnlyByParentProduct = true;

    /**
     * @var int How much last viewed products ID's to store in session
     */
    public $maxLastViewedProducts = 9;

    /**
     * @var bool Allow to add same product in the order
     */
    public $allowToAddSameProduct = 0;

    /**
     * @var bool Count only unique products in the order
     */
    public $countUniqueProductsOnly = 1;

    /**
     * @var bool Count children products in the order
     */
    public $countChildrenProducts = 1;

    /**
     * @var int Default measure ID
     */
    public $defaultMeasureId = 1;

    /**
     * @var int Final order stage leaf
     */
    public $finalOrderStageLeaf = 0;

    /**
     * @var int Default filter for Orders by stage in backend
     */
    public $defaultOrderStageFilterBackend = 0;

    /**
     * @var int
     */
    public $showDeletedOrders = 0;

    /**
     * @var array
     */
    public $ymlConfig = [];

    /**
     * @var bool Show filter links in breadcrumbs
     */
    public $showFiltersInBreadcrumbs = false;

    /**
     * @var bool Use method ceilQuantity of Measure model
     */
    public $useCeilQuantity = true;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'configurableModule' => [
                'class' => 'app\modules\config\behaviors\ConfigurableModuleBehavior',
                'configurationView' => '@app/modules/shop/views/configurable/_config',
                'configurableModel' => 'app\modules\shop\models\ConfigConfigurationModel',
            ]
        ];
    }

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        /**
         * Move orders/order params from guest to logged/signed user
         */
        Event::on(
            \yii\web\User::className(),
            \yii\web\User::EVENT_AFTER_LOGIN,
            function ($event) {
                /** @var UserEvent $event */
                $orders = \Yii::$app->session->get('orders', []);
                foreach ($orders as $k => $id) {
                    /** @var app\modules\shop\models\Order $order */
                    $order = app\modules\shop\models\Order::findOne(['id' => $id]);
                    if (!empty($order) && 0 === intval($order->user_id)) {
                        $order->user_id = $event->identity->id;
                        $order->save();
                    }
                }
            }
        );
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (\Yii::$app instanceof \yii\console\Application) {
            $this->controllerMap = [];
        }
    }

    /** @inheritdoc */
    public function getBackendGrids()
    {
        return [
            [
                'defaultValue' => app\backend\BackendModule::BACKEND_GRID_ONE_TO_ONE,
                'key' => self::BACKEND_PRODUCT_GRID,
                'label' => Yii::t('app', 'Product edit'),
            ],
            [
                'defaultValue' => app\backend\BackendModule::BACKEND_GRID_ONE_TO_ONE,
                'key' => self::BACKEND_CATEGORY_GRID,
                'label' => Yii::t('app', 'Category edit'),
            ],
        ];
    }
}
