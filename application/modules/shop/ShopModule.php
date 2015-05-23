<?php

namespace app\modules\shop;

use app;
use app\components\BaseModule;
use app\modules\core\helpers\EventTriggeringHelper;

/**
 * Shop module is the base core module of DotPlant2 CMS handling all common e-commerce features
 * @package app\modules\shop
 */
class ShopModule extends BaseModule
{
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
     * @var int How much products allow to compare at once
     */
    public $maxProductsToCompare = 3;

    /**
     * @var bool Should we show and query for products of subcategories
     */
    public $showProductsOfChildCategories = true;

    /**
     * @var bool Should cart count unique products or sum all quantity
     */
    public $cartCountsUniqueProducts = false;

    /**
     * @var int How much products to show on search results page
     */
    public $searchResultsLimit = 9;

    /**
     * @var bool Show delete order in backend
     */
    public $deleteOrdersAbility = false;

    /**
     * @var bool Filtration works only on parent products but not their children
     */
    public $filterOnlyByParentProduct = true;

    /**
     * @var int How much last viewed products ID's to store in session
     */
    public $maxLastViewedProducts = 9;

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
}