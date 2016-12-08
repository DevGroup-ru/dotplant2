<?php

namespace app\modules\page\controllers;

use app\components\Controller;
use app\modules\core\helpers\ContentBlockHelper;
use app\modules\core\models\ContentBlock;
use app\modules\page\models\Page;
use app\models\Search;
use app\traits\LoadModel;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use app\modules\seo\behaviors\SetCanonicalBehavior;
use yii\helpers\Url;

class PageController extends Controller
{
    use LoadModel;

    public function behaviors()
    {
        return [
            [
                'class' => SetCanonicalBehavior::className()
            ]
        ];
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionShow($id)
    {
        if (null === $model = Page::findById($id)) {
            throw new NotFoundHttpException;
        }

        $this->registerMetaDescription($model);

        $this->view->title = $model->title;
        if (!empty($model->h1)) {
            $this->view->blocks['h1'] = $model->h1;
        }
        $this->view->blocks['content'] = $model->content;
        $this->view->blocks['announce'] = $model->announce;

        return $this->render(
            $this->computeViewFile($model, 'show'),
            [
                'model' => $model,
                'breadcrumbs' => $this->buildBreadcrumbsArray($model)
            ]
        );
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionList($id)
    {
        if (null === $model = Page::findById($id)) {
            throw new NotFoundHttpException;
        }

        $this->registerMetaDescription($model);

        $cacheKey = 'PagesList:'.$model->id;

        // query that needed for pages retrieve and pagination
        $children = Page::find()
            ->where(['parent_id' => $model->id, 'published' => 1])
            ->orderBy('date_added DESC, sort_order')
            ->with('images');

        // count all pages
        $count = Yii::$app->cache->get($cacheKey.';count');
        if ($count === false) {
            $countQuery = clone $children;
            $count = $countQuery->count();
            Yii::$app->cache->set($cacheKey.';count', $count, 86400, new TagDependency([
                'tags' => [
                    ActiveRecordHelper::getCommonTag(Page::className())
                ]
            ]));
        }

        $pages = new Pagination(
            [
                'defaultPageSize' => $this->module->pagesPerList,
                'forcePageParam' => false,
                'pageSizeLimit' => [
                    $this->module->minPagesPerList,
                    $this->module->maxPagesPerList
                ],
                'totalCount' => $count,
            ]
        );

        // append current page number to cache key
        $cacheKey .= ';page:' . $pages->page;

        $childrenModels = Yii::$app->cache->get($cacheKey);
        if ($childrenModels === false) {

            /** @var ActiveQuery $children */

            $children = $children->offset($pages->offset)
                ->limit($pages->limit)
                ->all();
            Yii::$app->cache->set($cacheKey, $children, 86400, new TagDependency([
                'tags' => [
                    ActiveRecordHelper::getCommonTag(Page::className())
                ]
            ]));
        } else {
            $children = $childrenModels;
        }

        $this->view->title = $model->title;
        if (!empty($model->h1)) {
            $this->view->blocks['h1'] = $model->h1;
        }
        $this->view->blocks['content'] = $model->content;
        $this->view->blocks['announce'] = $model->announce;

        return $this->render(
            $this->computeViewFile($model, 'list'),
            [
                'model' => $model,
                'children' => $children,
                'pages' => $pages,
                'breadcrumbs' => $this->buildBreadcrumbsArray($model),
            ]
        );
    }

    /**
     * @return array
     * @throws ForbiddenHttpException
     */
    public function actionSearch()
    {
        /**
         * @param $module \app\modules\page\PageModule
         */
        if (!Yii::$app->request->isAjax) {
            throw new ForbiddenHttpException();
        }
        $model = new Search();
        $model->load(Yii::$app->request->get());
        $cacheKey = 'PageSearchIds: ' . $model->q;
        $ids = Yii::$app->cache->get($cacheKey);
        if ($ids === false) {
            $ids = $model->searchPagesByDescription();
            Yii::$app->cache->set(
                $cacheKey,
                $ids,
                86400,
                new TagDependency(
                    [
                        'tags' => ActiveRecordHelper::getCommonTag(Page::className()),
                    ]
                )
            );
        }
        $pages = new Pagination(
            [
                'defaultPageSize' => $this->module->searchResultsLimit,
                'forcePageParam' => false,
                'pageSizeLimit' => [
                    $this->module->minPagesPerList,
                    $this->module->maxPagesPerList
                ],
                'totalCount' => count($ids),
            ]
        );
        $cacheKey .= ' : ' . $pages->offset;
        $pagelist = Yii::$app->cache->get($cacheKey);
        if ($pagelist === false) {
            $pagelist = Page::find()->where(
                [
                    'in',
                    '`id`',
                    array_slice(
                        $ids,
                        $pages->offset,
                        $pages->limit
                    )
                ]
            )->addOrderBy('sort_order')->with('images')->all();
            Yii::$app->cache->set(
                $cacheKey,
                $pagelist,
                86400,
                new TagDependency(
                    [
                        'tags' => ActiveRecordHelper::getCommonTag(Page::className()),
                    ]
                )
            );
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return [
            'view' => $this->renderPartial(
                'search',
                [
                    'model' => $model,
                    'pagelist' => $pagelist,
                    'pages' => $pages,
                ]
            ),
            'totalCount' => count($ids),
        ];
    }

    /*
    * This function build array for widget "Breadcrumbs"
    *   $model - model of current page
    * Return an array for widget or empty array
    */
    private function buildBreadcrumbsArray($model)
    {
        if ($model === null || $model->id === 1) {
            return [];
        }

        // init
        $breadcrumbs = [];
        $crumbs[$model->slug] = [
            'name' => $model->breadcrumbs_label,
            'show_type' => $model->show_type,
            'id' => $model->id
        ];

        // get basic data
        $parent = Page::findById($model->parent_id);
        // if parent exists and not a main page
        while ($parent !== null && $parent->id != 1) {
            $crumbs[$parent->slug] = [
                'name' => $parent->breadcrumbs_label,
                'show_type' => $parent->show_type,
                'id' => $parent->id
            ];
            $parent = $parent->parent;
        }

        // build array for widget
        $url = '';
        $crumbs = array_reverse($crumbs, true);
        foreach ($crumbs as $slug => $data) {
            $url = Url::to(
                [
                    '/page/page/' . $data['show_type'],
                    'id' => $data['id'],
                ]
            );
            $breadcrumbs[] = [
                'label' => (string) $data['name'],
                'url' => $url
            ];
        }
        unset($breadcrumbs[count($breadcrumbs) - 1]['url']); // last item is not a link

        return $breadcrumbs;
    }

    /**
     * @param Page $model
     */
    private function registerMetaDescription($model)
    {
        if (!empty($model->meta_description)) {
            $this->view->registerMetaTag(
                [
                    'name' => 'description',
                    'content' => ContentBlockHelper::compileContentString(
                        $model->meta_description,
                        Page::className() . ":{$model->id}:meta_description",
                        new TagDependency(
                            [
                                'tags' => [
                                    ActiveRecordHelper::getCommonTag(ContentBlock::className()),
                                    ActiveRecordHelper::getCommonTag(Page::className())
                                ]
                            ]
                        )
                    )
                ],
                'meta_description'
            );
        }
    }
}
