<?php

namespace app\backend\widgets;

use app\models\RatingGroupObject;
use yii\base\Widget;
use app\backend\models\Notification as NotificationModel;

class Rating extends Widget
{
    public $object_id = null;
    public $object_model_id = null;
    public $form = null;

    public $viewFile = 'rating_widget';

    /**
     *
     */
    public function init()
    {
        parent::init();
    }

    /**
     *
     */
    public function run()
    {
        parent::run();

        if ((null === $this->object_id) || (null === $this->object_model_id)) {
            return '';
        }

        if (\Yii::$app->request->isPost) {
            if (null === $model = RatingGroupObject::getOneItemByAttributes(['object_id' => $this->object_id, 'object_model_id' => $this->object_model_id])) {
                $model = new RatingGroupObject(['scenario' => 'validate']);
                $model->object_id = $this->object_id;
                $model->object_model_id = $this->object_model_id;
            } else {
                $model->setScenario('validate');
            }

            if ($model->load(\Yii::$app->request->post('RatingGroupObject')) && $model->validate()) {
                $model->save();
            }
        }

        return $this->render(
            $this->viewFile,
            [
                'form' => $this->form,
                'object_id' => $this->object_id,
                'object_model_id' => $this->object_model_id
            ]
        );
    }
}
?>