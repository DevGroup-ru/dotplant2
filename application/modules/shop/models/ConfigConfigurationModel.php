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
                [
                    'maxLastViewedProducts',
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
                    'cartCountsUniqueProducts',
                    'deleteOrdersAbility',
                    'filterOnlyByParentProduct',
                ],
                'boolean',
            ],
            [
                [
                    'showProductsOfChildCategories',
                    'cartCountsUniqueProducts',
                    'deleteOrdersAbility',
                    'filterOnlyByParentProduct',
                ],
                'filter',
                'filter' => 'boolval',
            ],
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
        return [];
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
        ];
    }
}