<?php

namespace app\modules\review\widgets;

use app\components\ObjectRule;
use app\models\Form;
use app\modules\review\models\Review;
use yii\base\InvalidParamException;
use yii\base\Widget;
use yii\data\ArrayDataProvider;
use Yii;
use app\models\Object;
use app\models\PropertyGroup;
use yii\db\ActiveRecord;

class ReviewsWidget extends Widget
{
    /**
     * @var array
     */
    public $additionalParams = [];

    /**
     * @var ActiveRecord
     */
    public $model;

    /**
     * @var string
     */
    public $viewFile = 'reviews';

    /**
     * @var string
     */
    public $ratingGroupName;

    /**
     * @var int
     */
    public $sort = SORT_ASC;

    /**
     * @var bool
     */
    public $registerCanonical = false;

    /**
     * @var int
     */
    public $formId;

    /**
     * @var bool
     */
    public $useCaptcha = false;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ((null === $form = Form::findById($this->formId))
            || null === $this->model
            || null === $this->model->object) {
            throw new InvalidParamException;
        }
        if ($this->registerCanonical === true) {
            $this->getView()->registerLinkTag(
                [
                    'rel' => 'canonical',
                    'href' => ObjectRule::canonical($this->additionalParams),
                ],
                'canonical'
            );
        }
        $formObject = Object::getForClass(Form::className());
        $groups = PropertyGroup::getForModel($formObject->id, $form->id);
        $models = Review::getForObjectModel($this->model->id, $this->model->object->id, $form->id);

        $review = new Review(['scenario' => 'check']);
        $review->useCaptcha = $this->useCaptcha;
        /** @var $module \app\modules\review\ReviewModule */
        $module = Yii::$app->getModule('review');
        $maxPerPage = $module->maxPerPage;
        $pageSize = $module->pageSize;
        if ($pageSize > $maxPerPage) {
            $pageSize = $maxPerPage;
        }
        $this->additionalParams['review-page'] = Yii::$app->request->get('review-page');
        return $this->render(
            $this->viewFile,
            [
                'reviews' => new ArrayDataProvider(
                    [
                        'id' => 'review',
                        'allModels' => $models,
                        'pagination' => [
                            'defaultPageSize' => $pageSize,
                            'params' =>  $this->additionalParams,
                            'forcePageParam' => false,
                        ],
                        'sort' => [
                            'attributes' => [
                                'submission_id',
                            ],
                            'defaultOrder' => [
                                'submission_id' => $this->sort,
                            ],
                        ],
                    ]
                ),
                'objectModel' => $this->model,
                'model' => $form,
                'review' => $review,
                'groups' => $groups,
                'ratingGroupName' => $this->ratingGroupName,
                'useCaptcha' => $this->useCaptcha,
                'additionalParams' => $this->additionalParams,
            ]
        );
    }
}
