<?php

namespace app\modules\review;

use app\components\BaseModule;
use app\models\Object;
use app\modules\event\interfaces\EventInterface;
use app\modules\floatPanel\widgets\FloatingPanel;
use app\modules\page\models\Page;
use app\modules\review\models\Review;
use app\modules\shop\models\Category;
use app\modules\shop\models\Product;
use kartik\icons\Icon;
use yii\base\Event;

/**
 * Base configuration module for DotPlant2 CMS
 * @package app\modules\review
 */
class ReviewModule extends BaseModule implements EventInterface
{
    /**
     * @var int Max reviews on page
     */
    public $maxPerPage = 10;

    /**
     * @var int Default number of reviews on page
     */
    public $pageSize = 10;

    /**
     * @var bool Enable spam checking
     */
    public $enableSpamChecking = false;

    /**
     * @return array the behavior configurations.
     */
    public function behaviors()
    {
        return [
            'configurableModule' => [
                'class' => 'app\modules\config\behaviors\ConfigurableModuleBehavior',
                'configurationView' => '@app/modules/review/views/configurable/_config',
                'configurableModel' => 'app\modules\review\models\ConfigConfigurationModel',
            ]
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
            function ($event) {
                $objectId = 0;
                $modelId = 0;

                switch (\Yii::$app->requestedRoute) {
                    case "shop/product/show":
                        $objectId = Object::getForClass(Product::class)->id;
                        $modelId = $_GET['model_id'];
                        break;

                    case "shop/product/list":
                        $objectId = Object::getForClass(Category::class)->id;
                        $modelId = $_GET['last_category_id'];
                        break;

                    case "/page/page/show":
                    case "/page/page/list":
                        $objectId = Object::getForClass(Page::class)->id;
                        $modelId = $_GET['id'];
                        break;
                }
                $reviews = Review::getForObjectModel($modelId, $objectId, 1);
                if (!empty($reviews)) {
                    $event->items[] = [
                        "label" => Icon::show("pencil") . \Yii::t("app", "Edit reviews") . " (" . count($reviews) . ")",
                        "url" => [
                            "/review/backend-review/index",
                            "SearchModel" => [
                                "object_id" => $objectId,
                                "object_model_id" => $modelId
                            ]
                        ],
                        "target" => "_blank"
                    ];
                }
            }
        );
    }
}
