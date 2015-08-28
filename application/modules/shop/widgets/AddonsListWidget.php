<?php

namespace app\modules\shop\widgets;

use Yii;
use yii\base\Widget;

class AddonsListWidget extends Widget
{
    public $object_id;
    public $object_model_id;
    public $bindedAddons;

    public function run()
    {
        return $this->render(
            'addons-list-widget',
            [
                'object_id' => $this->object_id,
                'object_model_id' => $this->object_model_id,
                'bindedAddons' => $this->bindedAddons,
            ]
        );
    }
}