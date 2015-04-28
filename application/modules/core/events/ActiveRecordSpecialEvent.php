<?php

namespace app\modules\core\events;

use app;

class ActiveRecordSpecialEvent extends SpecialEvent
{
    public $model_class_name = '';
    public $model_id = 0;

    public $modelInstanceNeeded = false;
    /** @var \yii\db\ActiveRecord model */
    public $model = null;

    /**
     * @return array Array of event data that will be passed though application or through js
     */
    public function eventData()
    {
        if (isset($this->model)) {
            return [
                'model_class_name' => $this->model->className(),
                'model_id' => $this->model->id,
            ];
        } else {
            return [
                'model_class_name' => $this->model_class_name,
                'model_id' => $this->model_id,
            ];
        }
    }
}