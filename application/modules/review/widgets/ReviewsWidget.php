<?php

namespace app\modules\review\widgets;

use app\components\ObjectRule;
use app\models\Form;
use app\modules\review\models\Review;
use yii\base\InvalidParamException;
use yii\base\Widget;
use yii\data\ArrayDataProvider;
use yii\helpers\VarDumper;
use yii\widgets\ActiveForm;
use Yii;
use app\models\Object;
use app\models\PropertyGroup;
use yii\helpers\Html;
use app\models\Property;

class ReviewsWidget extends Widget
{
    public $additionalParams = [];
    public $model = null;
    public $viewFile = 'reviews';
    public $allow_rate = false;
    public $sort = SORT_ASC;
    public $registerCanonical = false;

    public $formId = null;

    public $useCaptcha = false;

    /**
     * @inheritdoc
     */
    public function run()
    {
//        $reviews = Review::getForObjectModel($this->model->id);
//        return VarDumper::dump($reviews);

        if ((null === $form = Form::findById($this->formId)) || null === $this->model) {
            throw new InvalidParamException;
        }
        $formObject = Object::getForClass(Form::className());
        $groups = PropertyGroup::getForModel($formObject->id, $form->id);
        $review = new Review(['scenario' => 'check']);
        return $this->render(
            $this->viewFile,
            [
                'object_id' => $this->model->object->id,
                'object_model_id' => $this->model->id,
                'model' => $form,
                'review' => $review,
                'groups' => $groups,
            ]
        );

        return;

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
                'object_id' => $this->object_id,
                'object_model_id' => $this->object_model_id,
                'model' => $model,
                'allow_rate' => $this->allow_rate,
                'useCaptcha' => $this->useCaptcha,
                'additionalParams' => $this->additionalParams,
            ]
        );
    }
}
