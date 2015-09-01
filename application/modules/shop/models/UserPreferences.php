<?php

namespace app\modules\shop\models;

use app;
use Yii;
use yii\base\Model;

/**
 * Class for handling User's preferences ie:
 * - listing view type(rows/items)
 * - products per page
 * - sort order in listing
 *
 * @package app\models
 */
class UserPreferences extends Model {

    public $listViewType;

    public $productListingSortId;

    public $productsPerPage;

    public static $_cachedPreferences = null;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['productListingSortId'], 'integer'],

            ['productListingSortId', 'default', 'value' => 1,],
            [
                'productListingSortId',
                'in',
                'range' => array_keys(ProductListingSort::enabledSorts())
            ],

            ['listViewType', 'default', 'value' => Yii::$app->getModule('shop')->listViewType],
            [
                'listViewType',
                'in',
                'range'=>[
                    'listView',
                    'blockView'
                ],
                'strict'=>true
            ],

            [
                'productsPerPage',
                'default',
                'value' => Yii::$app->getModule('shop')->productsPerPage,
            ],
            [
                'productsPerPage',
                'integer',
                'max' => 50,
            ]



        ];
    }

    /**
     * Loads UserPreferences model from session or creates new one
     * @return UserPreferences
     */
    public static function preferences()
    {
        if (static::$_cachedPreferences === null) {
            $model = new UserPreferences();
            $model->load([]);
            $model->validate();

            $attributes = Yii::$app->session->get('UserPreferencesModel');
            if (isset($attributes) === true) {
                $model->setAttributes($attributes);
            }
            static::$_cachedPreferences = $model;
        }
        return static::$_cachedPreferences;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'productListingSortId' => Yii::t('app', 'Sort by:'),
            'productsPerPage' => Yii::t('app', 'Products per page:'),
        ];
    }
} 