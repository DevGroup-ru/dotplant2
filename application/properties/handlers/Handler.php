<?php

namespace app\properties\handlers;

use Yii;
use yii\base\Widget;

class Handler extends Widget
{
    public $frontend_render_view;
    public $frontend_edit_view;
    public $backend_render_view;
    public $backend_edit_view;
    public $multiple = false;
    public $values = [];
    public $form;
    public $model;
    public $property_key;
    public $property_id;
    public $label;
    public $render_type = 'frontend_render_view';

    public function run()
    {
        return $this->render(
            $this->{$this->render_type},
            [
                'values' => $this->values,
                'form' => $this->form,
                'model' => $this->model,
                'attribute_name' => $this->attributeName(),
                'label' => $this->label,
                'property_key' => $this->property_key,
                'property_id' => $this->property_id,
                'multiple' => $this->multiple,
            ]
        );
    }

    public function attributeName()
    {
        return 'props['.$this->property_key.'][]';
    }
}
