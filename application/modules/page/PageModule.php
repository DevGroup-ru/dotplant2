<?php

namespace app\modules\page;

use app;
use app\modules\event\interfaces\EventInterface;
use app\modules\floatPanel\widgets\FloatingPanel;
use kartik\icons\Icon;
use Yii;
use yii\base\Event;

/**
 * Base configuration module for DotPlant2 CMS
 * @package app\modules\page
 */
class PageModule extends app\components\BaseModule implements EventInterface
{
    const BACKEND_PAGE_GRID = 'pageGrid';
    /**
     * @var int minimum pages per list to show
     */
    public $minPagesPerList = 1;

    /**
     * @var int maximum pages per list to show
     */
    public $maxPagesPerList = 50;

    /**
     * @var int pages per list to show
     */
    public $pagesPerList = 10;

    /**
     * @var int How much pages to show on search results page
     */
    public $searchResultsLimit = 10;

    public $controllerMap = [
        'backend' => 'app\modules\page\backend\PageController',
    ];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'configurableModule' => [
                'class' => 'app\modules\config\behaviors\ConfigurableModuleBehavior',
                'configurationView' => '@app/modules/page/views/configurable/_config',
                'configurableModel' => 'app\modules\page\models\ConfigConfigurationModel',
            ]
        ];
    }

    /** @inheritdoc */
    public function getBackendGrids()
    {
        return [
            [
                'defaultValue' => app\backend\BackendModule::BACKEND_GRID_ONE_TO_ONE,
                'key' => self::BACKEND_PAGE_GRID,
                'label' => Yii::t('app', 'Page edit'),
            ],
        ];
    }

    /**
     * @return void
     */
    public static function attachEventsHandlers()
    {
        Event::on(
            FloatingPanel::class,
            FloatingPanel::EVENT_BEFORE_RENDER,
            function($event) {
                if (in_array(\Yii::$app->requestedRoute, ['/page/page/show', '/page/page/list']) && isset($_GET['id'])) {
                    $page = app\modules\page\models\Page::findById($_GET['id']);
                    $event->items[] = [
                        'label' => Icon::show('pencil') . ' ' . Yii::t('app', 'Edit page'),
                        'url' => [
                            '/page/backend/edit',
                            'id' => $page->id,
                            'parent_id' =>$page->parent_id,
                        ],
                    ];
                    if (Yii::$app->requestedRoute == "/page/page/list") {
                        $event->items[] = [
                            'label' => Icon::show('plus') .  Yii::t('app', 'Add child page'),
                            'url' => [
                                '/page/backend/edit',
                                'parent_id' => $page->id
                            ]
                        ];
                    }
                }
            }
        );
    }
}
