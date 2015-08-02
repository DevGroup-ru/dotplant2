<?php

namespace app\validators;

use Yii;
use yii\validators\Validator;

/**
 * ClassnameValidator checks if the attribute value is a valid class name that can be used by application
 *
 * Usage:
 *
 * ```
 * public function rules()
 * {
 *      return [
 *          [
 *              ['class_name_attribute'],
 *              app\validators\ClassnameValidator::className(),
 *          ]
 *      ];
 * }
 *
 * ```
 *
 * @package app\validators
 */
class ClassnameValidator extends Validator
{
    /**
     * @inheritdoc
     * @return null|array
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