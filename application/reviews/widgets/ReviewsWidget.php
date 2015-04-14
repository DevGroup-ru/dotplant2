<?php

namespace app\reviews\widgets;

use app\components\ObjectRule;
use app\reviews\models\Review;
use yii\base\Widget;
use yii\data\ArrayDataProvider;

class ReviewsWidget extends Widget
{
    public $object_id = null;
    public $object_model_id = null;
    public $additionalParams = [];
    public $maxPerPage = 50;
    public $model = null;
    public $viewFile = 'reviews';
    public $allow_rate = false;
    public $sort = SORT_ASC;
    public $registerCanonical = false;

    public $useCaptcha = false;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->registerCanonical === true) {
            $this->getView()->registerLinkTag(
                [
                    'rel' => 'canonical',
                    'href' => ObjectRule::canonical($this->additionalParams),
                ],
                'canonical'
            );
        }
        $reviews = Review::getForObjectModel($this->object_id, $this->object_model_id, $this->sort);
        $model = new Review();
        $model->useCaptcha = $this->useCaptcha;
        if (!\Yii::$app->getUser()->isGuest) {
            $model->author_user_id = \Yii::$app->getUser()->id;
        }
        $pageSize = \Yii::$app->request->get('review-per-page', 10);
        if ($pageSize > $this->maxPerPage) {
            $pageSize = $this->maxPerPage;
        }
        return $this->render(
            $this->viewFile,
            [
                'reviews' => new ArrayDataProvider(
                    [
                        'id' => 'review',
                        'allModels' => $reviews,
                        'pagination' => [
                            'pageSize' => $pageSize,
                            'params' => array_merge($_GET, $this->additionalParams),
                        ],
                        'sort' => [
                            'attributes' => [
                                'date_submitted',
                            ],
                            'defaultOrder' => [
                                'date_submitted' => $this->sort,
                            ],
                        ],
                    ]
                ),
                'model' => $model,
                'allow_rate' => $this->allow_rate,
                'useCaptcha' => $this->useCaptcha,
                'additionalParams' => $this->additionalParams,
            ]
        );
    }
}
