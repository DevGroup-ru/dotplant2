<?php

namespace app\modules\shop\widgets;

use app\modules\shop\models\Addon;
use app\modules\shop\models\AddonCategory;
use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;

class AddonsListWidget extends Widget
{
    public $object_id;
    public $object_model_id;
    public $bindedAddons;

    public function run()
    {
        $this->bindedAddons = ArrayHelper::map($this->bindedAddons, 'id', function(Addon $item){
            return $item;
        }, function(Addon $item) {
            return AddonCategory::findById($item->addon_category_id)->name;
        });

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