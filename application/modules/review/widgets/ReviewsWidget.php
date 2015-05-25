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

class ReviewsWidget extends Widget
{
    public $additionalParams = [];
    public $model = null;
    public $viewFile = 'reviews';
    public $allow_rate = false;
    public $sort = SORT_ASC;
    public $registerCanonical = false;
    public $object_id;

    public $formId = null;

    public $useCaptcha = false;

    /**
     * @inheritdoc
     */
    public function run()
    {

        if ((null === $form = Form::findById($this->formId))
            || null === $this->model
            || null === $object = Object::findById($this->object_id)) {
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
        $models = Review::getForObjectModel($this->model->id, $object->id, $form->id);

        $review = new Review(['scenario' => 'check']);
        $review->useCaptcha = $this->useCaptcha;
        /** @var $module \app\modules\review\ReviewModule */
        $module = Yii::$app->getModule('review');
        $maxPerPage = $module->maxPerPage;
        $pageSize = $module->pageSize;
        if ($pageSize > $maxPerPage) {
            $pageSize = $maxPerPage;
        }
        return $this->render(
            $this->viewFile,
            [
                'reviews' => new ArrayDataProvider(
                    [
                        'id' => 'review',
                        'allModels' => $models,
                        'pagination' => [
                            'pageSize' => $pageSize,
                            'params' =>  array_merge($_GET, $this->additionalParams),
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
                'object_id' => $object->id,
                'object_model_id' => $this->model->id,
                'model' => $form,
                'review' => $review,
                'groups' => $groups,
                'allow_rate' => $this->allow_rate,
                'useCaptcha' => $this->useCaptcha,
                'additionalParams' => $this->additionalParams,
            ]
        );
    }
}
