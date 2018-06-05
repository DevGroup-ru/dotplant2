<?php

namespace app\modules\shop\widgets;

use Yii;
use yii\base\Widget;
use app;
use app\modules\shop\models\AddAddonModel;
use app\modules\shop\models\AddonCategory;

class AddonsWidget extends Widget
{
    /** @var app\backend\components\ActiveForm */
    public $form;

    /** @var \yii\db\ActiveRecord */
    public $model;

    public function run()
    {
        $object = app\models\BaseObject::getForClass($this->model->className());
        /** @var \app\modules\shop\models\AddonCategory $addonCategories */
        $addonCategories = app\components\Helper::getModelMap(AddonCategory::className(), 'id', 'name');

        /** @var app\modules\shop\models\Addon $bindedAddons */
        $bindedAddons = $this->model->bindedAddons;

        $addAddonModel = new AddAddonModel();

        return $this->render(
            'addons-widget',
            [
                'object' => $object,
                'addonCategories' => $addonCategories,
                'bindedAddons' => $bindedAddons,
                'model' => $this->model,
                'form' => $this->form,
                'addAddonModel' => $addAddonModel,
            ]
        );
    }
}