<?php

namespace app\controllers;

use app\components\Controller;
use app\models\Config;
use app\models\Object;
use app\models\Page;
use app\models\Search;
use app\reviews\traits\ProcessReviews;
use app\seo\behaviors\MetaBehavior;
use app\traits\LoadModel;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class PageController extends Controller
{
    use LoadModel;
    use ProcessReviews;

    public function behaviors()
    {
        return [
            'seo' => [
                'class' => MetaBehavior::className(),
                'index' => $this->defaultAction,
            ],
        ];
    }

    public function actionShow($id)
    {
        if (null === $model = Page::findById($id)) {
            throw new NotFoundHttpException;
        }

        if (!empty($model->meta_description)) {
            $this->view->registerMetaTag(
                [
                    'name' => 'description',
                    'content' => $model->meta_description,
                ],
                'meta_description'
            );
        }

        $this->processReviews(Object::getForClass($model->className())->id, $model->id);

        $this->view->title = $model->title;

        return $this->render(
            $this->computeViewFile($model, 'show'),
            [
                'model' => $model,
                'breadcrumbs' => $this->buildBreadcrumbsArray($model)
            ]
        );
    }

    public function actionList($id)
    {
        if (null === $model = Page::findById($id)) {
            throw new NotFoundHttpException;
        }
        if (!empty($model->meta_description)) {
            $this->view->registerMetaTag(
                [
                    'name' => 'description',
                    'content' => $model->meta_description,
                ],
                'meta_description'
            );
        }

        /** @var ActiveQuery $children */
        $children = Page::find()
            ->where(['parent_id' => $model->id])
            ->orderBy('date_added DESC, sort_order');

        $countQuery = clone $children;

        $pages = new Pagination(
            [
                'defaultPageSize' => Config::getValue('page.pagesPerList', 10),
                'totalCount' => $countQuery->count()
            ]
        );

        $children = $children->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        $this->view->title = $model->title;

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

    public function actionSearch()
    {
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
                'defaultPageSize' => 10,
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
            )->addOrderBy('sort_order')->all();
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
        $crumbs[$model->slug] = $model->breadcrumbs_label;

        // get basic data
        $parent = Page::findById($model->parent_id);
        // if parent exists and not a main page
        while ($parent !== null && $parent->id != 1) {
            $crumbs[$parent->slug] = $parent->breadcrumbs_label;
            $parent = $parent->parent;
        }

        // build array for widget
        $url = '';
        $crumbs = array_reverse($crumbs);
        foreach ($crumbs as $slug => $label) {
            $url .= '/' . $slug;
            $breadcrumbs[] = [
                'label' => (string) $label,
                'url' => $url
            ];
        }
        unset($breadcrumbs[count($breadcrumbs) - 1]['url']); // last item is not a link

        return $breadcrumbs;
    }
}
