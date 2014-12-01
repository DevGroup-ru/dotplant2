<?php

namespace app\controllers;

use app\components\Controller;
use app\components\fabric\FilterQueryInterface;
use app\components\LastViewedProducts;
use app\models\Category;
use app\models\Config;
use app\models\Object;
use app\models\Product;
use app\models\ProductListingSort;
use app\models\Search;
use app\models\UserPreferences;
use app\properties\PropertiesHelper;
use app\reviews\traits\ProcessReviews;
use app\traits\DynamicContentTrait;
use \devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class ProductController extends Controller
{
    use DynamicContentTrait;
    use ProcessReviews;

    /**
     * Products listing by category with filtration support.
     *
     * @return string
     * @throws \Exception
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionList()
    {
        $request = Yii::$app->request;

        if (null === $request->get('category_group_id')) {
            throw new NotFoundHttpException;
        }

        if (null === $object = Object::getForClass(Product::className())) {
            throw new ServerErrorHttpException('Object not found.');
        }

        $category_group_id = intval($request->get('category_group_id', 0));

        $title_append = $request->get('title_append', '');
        if (!empty($title_append)) {
            $title_append = is_array($title_append) ? implode(' ', $title_append) : $title_append;
        }

        $values_by_property_id = $request->get('properties', []);
        if (!is_array($values_by_property_id)) {
            $values_by_property_id = [$values_by_property_id];
        }

        $selected_category_ids = $request->get('categories', []);
        if (!is_array($selected_category_ids)) {
            $selected_category_ids = [$selected_category_ids];
        }

        if (null !== $selected_category_id = $request->get('last_category_id')) {
            $selected_category_id = intval($selected_category_id);
        }

        $query = Product::find();
        $query->andWhere([Product::tableName() . '.parent_id' => 0, Product::tableName() . '.active' => 1]);

        /** @var $filter FilterQueryInterface */
        if (null !== $filter = Yii::$app->filterquery->getFilter()) {
            $query = $filter->filter($query);
        }

        if (null !== $selected_category_id) {
            $query->innerJoin(
                $object->categories_table_name . ' ocats',
                'ocats.category_id = :catid AND ocats.object_model_id = ' . Product::tableName() . '.id',
                [':catid' => $selected_category_id]
            );
        } else {
            $query->innerJoin(
                $object->categories_table_name . ' ocats',
                'ocats.object_model_id = ' . Product::tableName() . '.id'
            );
        }

        $query->innerJoin(
            Category::tableName() . ' ocatt',
            'ocatt.id = ocats.category_id AND ocatt.category_group_id = :gcatid',
            [':gcatid' => $category_group_id]
        );

        $allSorts = ProductListingSort::enabledSorts();
        $userSelectedSortingId = UserPreferences::preferences()->getAttributes()['productListingSortId'];
        if (isset($allSorts[$userSelectedSortingId])) {
            $query->addOrderBy(
                $allSorts[$userSelectedSortingId]['sort_field'] .
                ' ' .
                $allSorts[$userSelectedSortingId]['asc_desc']
            );
        } else {
            $query->addOrderBy(Product::tableName() . '.sort_order');
        }

        $productsPerPage = UserPreferences::preferences()->getAttributes()['productsPerPage'];

        PropertiesHelper::appendPropertiesFilters(
            $object,
            $query,
            $values_by_property_id,
            $request->get('p', [])
        );

        $products_query = clone $query;
        $cacheKey = 'ProductsCount:' . implode(
            '_',
            [
                $request->pathInfo,
                $request->queryString,
                Json::encode(Yii::$app->request->get('p', [])),
                $userSelectedSortingId,
                $productsPerPage
            ]
        );

        if (false === $pages = Yii::$app->cache->get($cacheKey)) {
            $pages = new Pagination(
                [
                    'defaultPageSize' => $productsPerPage,
                    'totalCount' => $products_query->count(),
                ]
            );

            Yii::$app->cache->set(
                $cacheKey,
                $pages,
                86400,
                new TagDependency(
                    [
                        'tags' => [
                            ActiveRecordHelper::getCommonTag(Category::className()),
                            ActiveRecordHelper::getCommonTag(Product::className()),
                            ActiveRecordHelper::getCommonTag(Config::className()),
                        ]
                    ]
                )
            );
        }

        $query->offset($pages->offset)->limit($pages->limit);

        $cacheKey .= '-' . $request->get('page', 1);

        if (false === $products = Yii::$app->cache->get($cacheKey)) {
            $products = $query->all();

            Yii::$app->cache->set(
                $cacheKey,
                $products,
                86400,
                new TagDependency(
                    [
                        'tags' => [
                            ActiveRecordHelper::getCommonTag(Category::className()),
                            ActiveRecordHelper::getCommonTag(Product::className()),
                            ActiveRecordHelper::getCommonTag(Config::className()),
                        ]
                    ]
                )
            );
        }

        if (null !== $selected_category = $selected_category_id) {
            if ($selected_category_id > 0) {
                if (null !== $selected_category = Category::findById($selected_category_id, null, null)) {
                    if (!empty($selected_category->meta_description)) {
                        $this->view->registerMetaTag(
                            [
                                'name' => 'description',
                                'content' => $selected_category->meta_description,
                            ],
                            'meta_description'
                        );
                    }
                    $this->view->title = $selected_category->title;
                }
            }
        }

        $this->loadDynamicContent($object->id, 'product/list', $request->get());

        return $this->render(
            $this->computeViewFile($selected_category, 'list'),
            [
                'selected_category' => $selected_category,
                'selected_category_id' => $selected_category_id,
                'selected_category_ids' => $selected_category_ids,
                'values_by_property_id' => $values_by_property_id,
                'products' => $products,
                'object' => $object,
                'category_group_id' => $category_group_id,
                'pages' => $pages,
                'title_append' => $title_append,
                'selections' => $request->get(),
                'breadcrumbs' => $this->buildBreadcrumbsArray($selected_category),
                'allSorts' => $allSorts,
            ]
        );
    }

    /**
     * Product page view
     *
     * @param null $model_id
     * @return string
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actionShow($model_id = null)
    {
        if (null === $object = Object::getForClass(Product::className())) {
            throw new ServerErrorHttpException('Object not found.');
        }

        $cacheKey = 'Product:' . $model_id;
        if (false === $product = Yii::$app->cache->get($cacheKey)) {
            if (null === $product = Product::findById($model_id)) {
                throw new NotFoundHttpException;
            }
            Yii::$app->cache->set(
                $cacheKey,
                $product,
                86400,
                new TagDependency(
                    [
                        'tags' => [
                            ActiveRecordHelper::getObjectTag(Product::className(), $model_id),
                        ]
                    ]
                )
            );
        }

        $request = Yii::$app->request;

        $values_by_property_id = $request->get('properties', []);
        if (!is_array($values_by_property_id)) {
            $values_by_property_id = [$values_by_property_id];
        }

        $selected_category_id = $request->get('last_category_id');

        $selected_category_ids = $request->get('categories', []);
        if (!is_array($selected_category_ids)) {
            $selected_category_ids = [$selected_category_ids];
        }

        $category_group_id = intval($request->get('category_group_id', 0));

        (new LastViewedProducts())->saveToSession($product->id);

        if (!empty($product->meta_description)) {
            $this->view->registerMetaTag(
                [
                    'name' => 'description',
                    'content' => $product->meta_description,
                ],
                'meta_description'
            );
        }

        $selected_category = ($selected_category_id > 0) ? Category::findOne($selected_category_id) : null;

        $this->processReviews($object->id, $product->id);

        $this->view->title = $product->title;

        return $this->render(
            $this->computeViewFile($product, 'show'),
            [
                'model' => $product,
                'category_group_id' => $category_group_id,
                'values_by_property_id' => $values_by_property_id,
                'selected_category_id' => $selected_category_id,
                'selected_category' => $selected_category,
                'selected_category_ids' => $selected_category_ids,
                'object' => $object,
                'breadcrumbs' => $this->buildBreadcrumbsArray($selected_category, $product)
            ]
        );
    }

    /**
     * Search handler
     * @return array
     */
    public function actionSearch()
    {
        $model = new Search();
        $model->load(Yii::$app->request->get());
        $ids = ArrayHelper::merge(
            $model->searchProductsByDescription(),
            $model->searchProductsByProperty()
        );
        $pages = new Pagination(
            [
                'defaultPageSize' => 6,
                'totalCount' => count($ids),
            ]
        );
        $products = Product::find()->where(
            [
                'in',
                '`id`',
                array_slice(
                    $ids,
                    $pages->offset,
                    $pages->limit
                )
            ]
        )->addOrderBy('sort_order')->all();

        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'view' => $this->renderPartial(
                'search',
                [
                    'model' => $model,
                    'pages' => $pages,
                    'products' => $products,
                ]
            ),
            'totalCount' => count($ids),
        ];

    }

    /*
    * This function build array for widget "Breadcrumbs"
    *   $selCat - model of current category
    *   $product - model of product, if current page is a page of product
    * Return an array for widget or empty array
    */
    private function buildBreadcrumbsArray($selCat, $product = null)
    {
        if ($selCat === null) {
            return [];
        }

        // init
        $breadcrumbs = [];
        if ($product !== null) {
            $crumbs[$product->slug] = !empty($product->breadcrumbs_label) ? $product->breadcrumbs_label : '';
        }
        $crumbs[$selCat->slug] = $selCat->breadcrumbs_label;

        // get basic data
        $parent = $selCat->parent_id > 0 ? Category::findById($selCat->parent_id) : null;
        while ($parent !== null) {
            $crumbs[$parent->slug] = $parent->breadcrumbs_label;
            $parent = $parent->parent;
        }

        // build array for widget
        $url = '';
        $crumbs = array_reverse($crumbs);
        foreach ($crumbs as $slug => $label) {
            $url .= '/' . $slug;
            $breadcrumbs[] = [
                'label' => $label,
                'url' => $url
            ];
        }
        unset($breadcrumbs[count($breadcrumbs) - 1]['url']); // last item is not a link
        return $breadcrumbs;
    }
}
