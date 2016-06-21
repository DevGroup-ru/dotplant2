<?php

namespace app\modules\shop\models;

use app;
use app\modules\config\models\BaseConfigurationModel;
use Yii;

/**
 * Class ConfigConfigurationModel represents configuration model for retrieving user input
 * in backend configuration subsystem.
 *
 * @package app\modules\shop\models
 */
class ConfigConfigurationModel extends BaseConfigurationModel
{
    const MULTI_FILTER_MODE_UNION = 'union';
    const MULTI_FILTER_MODE_INTERSECTION = 'intersection';
    const FILTER_PARENTS_ONLY = 'parents_only';
    const FILTER_CHILDREN_ONLY = 'children_only';
    const FILTER_ALL = 'all';

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
    public $showProductsOfChildCategories = true;

    /**
     * @var int How much products to show on search results page
     */
    public $searchResultsLimit = 9;

    /**
     * @var boolean Possible to search generated products
     */
    public $allowSearchGeneratedProducts = 0;

    /**
     * @var bool Show delete order in backend
     */
    public $deleteOrdersAbility = false;


    /**
     * @var string the mode of products filtering
     */
    public $productsFilteringMode = self::FILTER_PARENTS_ONLY;

    /**
     * @var string Filtration mode
     */
    public $multiFilterMode = self::MULTI_FILTER_MODE_INTERSECTION;

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

    /***
     * @var bool registration Guest User In Cart as new user and send data on e-mail
     */
    public $registrationGuestUserInCart = 0;
    /**
     * @var int Show deleted orders in backend or not
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
    public $showFiltersInBreadcrumbs = true;

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
    public function rules()
    {
        return [
            [
                [
                    'productsPerPage',
                    'maxProductsToCompare',
                    'searchResultsLimit',
                ],
                'integer',
                'min' => 1,
            ],
            [
                'listViewType',
                'in',
                'range' => [
                    'listView',
                    'blockView'
                ],
                'strict' => true
            ],
            [
                [
                    'maxLastViewedProducts',
                    'finalOrderStageLeaf',
                    'defaultOrderStageFilterBackend',
                ],
                'integer',
            ],
            [
                [
                    'productsPerPage',
                    'maxProductsToCompare',
                    'searchResultsLimit',
                ],
                'filter',
                'filter' => 'intval',
            ],
            [
                [
                    'productsPerPage',
                    'maxProductsToCompare',
                    'searchResultsLimit',
                    'maxLastViewedProducts',
                ],
                'required',
            ],
            [
                [
                    'showProductsOfChildCategories',
                    'deleteOrdersAbility',
                    'showDeletedOrders',
                    'showFiltersInBreadcrumbs',
                    'useCeilQuantity',
                ],
                'boolean',
            ],
            [
                [
                    'showProductsOfChildCategories',
                    'deleteOrdersAbility',
                ],
                'filter',
                'filter' => 'boolval',
            ],
            [
                [
                    'allowToAddSameProduct',
                    'countUniqueProductsOnly',
                    'countChildrenProducts',
                    'allowSearchGeneratedProducts',
                    'registrationGuestUserInCart'
                ],
                'boolean'
            ],
            [['defaultMeasureId'], 'integer'],
            [
                ['ymlConfig'],
                function ($attribute, $params) {
                    if (!is_array($this->$attribute)) {
                        $this->$attribute = [];
                    }
                }
            ],
            [
                'productsFilteringMode',
                'in',
                'range' => array_keys($this->getFilterModes())
            ],
            [['itemView', 'listView', 'multiFilterMode', 'productsFilteringMode'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'allowToAddSameProduct' => Yii::t('app', 'Allow to add same product'),
            'countUniqueProductsOnly' => Yii::t('app', 'Count unique products only'),
            'countChildrenProducts' => Yii::t('app', 'Count children products'),
            'defaultMeasureId' => Yii::t('app', 'Default measure'),
            'registrationGuestUserInCart' => Yii::t('app', 'Registration Guest User In Cart'),
            'multiFilterMode' => Yii::t('app', 'MultiFilter mode'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function defaultValues()
    {
        /** @var app\modules\shop\ShopModule $module */
        $module = $this->getModuleInstance();

        $attributes = array_keys($this->getAttributes());
        foreach ($attributes as $attribute) {
            $this->{$attribute} = $module->{$attribute};
        }
    }

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for web only.
     *
     * @return array
     */
    public function webApplicationAttributes()
    {
        $attributes = $this->getAttributes();
        return [
            'modules' => [
                'shop' => $attributes,
            ],
        ];
    }

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for console only.
     *
     * @return array
     */
    public function consoleApplicationAttributes()
    {
        return [
            'modules' => [
                'shop' => [
                    'ymlConfig' => $this->ymlConfig,
                    'googleFeedConfig' => $this->googleFeedConfig
                ]
            ]
        ];
    }

    /**
     * Returns array of module configuration that should be stored in application config.
     * Array should be ready to merge in app config.
     * Used both for web and console.
     *
     * @return array
     */
    public function commonApplicationAttributes()
    {
        return [];
    }

    /**
     * Returns array of key=>values for configuration.
     *
     * @return mixed
     */
    public function keyValueAttributes()
    {
        return [];
    }

    /**
     * Returns array of aliases that should be set in common config
     * @return array
     */
    public function aliases()
    {
        return [
            '@shop' => dirname(__FILE__) . '/../',
            '@category' => '/shop/product/list',
            '@product' => '/shop/product/show',
        ];
    }

    public static function getMultiFilterModes()
    {
        return [
            self::MULTI_FILTER_MODE_UNION => 'Union',
            self::MULTI_FILTER_MODE_INTERSECTION => 'Intersection',
        ];
    }

    /**
     * Get dropdown list options for productsFilteringMode
     * @return array
     */
    public static function getFilterModes()
    {
        return [
            self::FILTER_PARENTS_ONLY => Yii::t('app', 'Parents only'),
            self::FILTER_CHILDREN_ONLY => Yii::t('app', 'Children only'),
            self::FILTER_ALL => Yii::t('app', 'Parents and children'),
        ];
    }
}
