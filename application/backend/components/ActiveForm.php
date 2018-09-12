<?php

namespace app\backend\components;

/**
 * ActiveForm extends great kartik ActiveForm adding some useful functions
 * (mostly through \app\backend\components\ActiveField)
 * @package app\backend\components
 */
class ActiveForm extends \kartik\form\ActiveForm
{
    public function initForm()
    {
        if (!isset($this->fieldConfig['class'])) {
            $this->fieldConfig['class'] = ActiveField::className();
        }
        parent::initForm();
    }
}