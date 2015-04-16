<?php

namespace app\validators;

use Yii;
use yii\validators\Validator;

/**
 * ClassnameValidator checks if the attribute value is a valid class name that can be used by application
 * @package app\validators
 */
class ClassnameValidator extends Validator
{
    /**
     * @inheritdoc
     */
    public function validateValue($value)
    {
        if (class_exists($value) === false) {
            return [
                Yii::t('app', 'Unable to find specified class.'),
                []
            ];
        } else {
            return null;
        }
    }
}