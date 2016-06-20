<?php

namespace app\modules\shop;

use app;
use app\backend\BackendModule;
use app\components\BaseModule;
use app\modules\shop\handlers\UserHandler;
use app\modules\shop\models\ConfigConfigurationModel;
use kartik\icons\Icon;
use Yii;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\helpers\Json;
use app\models\Object;
use app\modules\shop\models\Product;
use yii\web\User;

/**
 * Shop module is the base core module of DotPlant2 CMS handling all common e-commerce features
 * @package app\modules\shop
 */
class ShopModule extends BaseModule implements BootstrapInterface, app\modules\event\interfaces\EventInterface
{
    const BACKEND_PRODUCT_GRID = 'productEditGrid';
    const BACKEND_CATEGORY_GRID = 'categoryEditGrid';

    /**
     * @inheritdoc
     */
    public $controllerMap = [
        'backend-filter-sets' => 'app\modules\shop\backend\FilterSetsController',
        'backend-addons' => 'app\modules\shop\backend\AddonsController',
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
     * @var string the mode of products filtering
     */
    public $productsFilteringMode = ConfigConfigurationModel::FILTER_PARENTS_ONLY;

    /**
     * @var string Filtration mode
     */
    public $multiFilterMode = ConfigConfigurationModel::MULTI_FILTER_MODE_INTERSECTION;

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
     * @var array
     */
    public $googleFeedConfig = [];


    /**
     * @var bool Show filter links in breadcrumbs
     */
    public $showFiltersInBreadcrumbs = false;

    /**
     * @var bool Use method ceilQuantity of Measure model
     */
    public $useCeilQuantity = true;

    /**
     * @var string View file for render product item content block
     */
    public $itemView = '@app/modules/shop/views/product/item-row';

    /**
     * @var string View file for render product list content block
     */
    public $listView = '@app/modules/shop/views/product/item-list';

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
            User::className(),
            User::EVENT_AFTER_LOGIN,
            [UserHandler::className(), 'moveOrdersGuestToRegistered']
        );

        /**
         * Move wishlists/wishlist params from guest to logged/signed user
         */
        Event::on(
            User::className(),
            User::EVENT_AFTER_LOGIN,
            [UserHandler::className(), 'moveWishlistsGuestToRegistered']
        );
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerMap = [];
        }
    }

    /** @inheritdoc */
    public function getBackendGrids()
    {
        return [
            [
                'defaultValue' => BackendModule::BACKEND_GRID_ONE_TO_ONE,
                'key' => self::BACKEND_PRODUCT_GRID,
                'label' => Yii::t('app', 'Product edit'),
            ],
            [
                'defaultValue' => BackendModule::BACKEND_GRID_ONE_TO_ONE,
                'key' => self::BACKEND_CATEGORY_GRID,
                'label' => Yii::t('app', 'Category edit'),
            ],
        ];
    }

    /**
     * @return void
     */
    public static function attachEventsHandlers()
    {
        Event::on(
            app\modules\floatPanel\widgets\FloatingPanel::class,
            app\modules\floatPanel\widgets\FloatingPanel::EVENT_BEFORE_RENDER,
            function ($event) {
                switch (Yii::$app->requestedRoute) {
                    case 'shop/product/list':
                        if (isset($_GET['properties'])) {
                            $apply_if_params = [];
                            foreach ($_GET['properties'] as $property_id => $values) {
                                if (isset($values[0])) {
                                    $apply_if_params[$property_id] = $values[0];
                                }
                            }
                            if (Yii::$app->response->dynamic_content_trait === true) {
                                $event->items[] = [
                                    'label' => Icon::show('puzzle') . ' ' . Yii::t('app', 'Edit Dynamic Content'),
                                    'url' => [
                                        '/backend/dynamic-content/edit',
                                        'id' => Yii::$app->response->matched_dynamic_content_trait_model->id,
                                    ],
                                ];
                            } else {
                                if (isset($_GET['properties'], $_GET['last_category_id'])) {
                                    $event->items[] = [
                                        'label' => Icon::show('puzzle') . ' ' . Yii::t('app', 'Add Dynamic Content'),
                                        'url' => [
                                            '/backend/dynamic-content/edit',
                                            'DynamicContent' => [
                                                'apply_if_params' => Json::encode($apply_if_params),
                                                'apply_if_last_category_id' => $_GET['last_category_id'],
                                                'object_id' => Object::getForClass(Product::class)->id,
                                                'route' => 'shop/product/list',
                                            ]
                                        ],
                                    ];

                                }

                            }
                        } else {
                            // no properties selected - go to category edit page
                            if (isset($_GET['last_category_id'])) {
                                $cat = app\modules\shop\models\Category::findById($_GET['last_category_id']);
                                $event->items[] = [
                                    'label' => Icon::show('pencil') . ' ' . Yii::t('app', 'Edit category'),
                                    'url' => [
                                        '/shop/backend-category/edit',
                                        'id' => $cat->id,
                                        'parent_id' => $cat->parent_id,
                                    ],
                                ];
                            }
                        }
                        break;
                    case 'shop/product/show':
                        if (isset($_GET['model_id'])) {
                            $event->items[] = [
                                'label' => Icon::show('pencil') . ' ' . Yii::t('app', 'Edit product'),
                                'url' => [
                                    '/shop/backend-product/edit',
                                    'id' => intval($_GET['model_id'])
                                ],
                            ];
                        }
                        break;
                }
            }
        );
    }
}
