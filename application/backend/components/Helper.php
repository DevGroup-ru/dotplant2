<?php

namespace app\backend\components;

use app\backend\BackendModule;
use app\components\BaseModule;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use kartik\icons\Icon;

class Helper
{
    private static $returnUrl;

    /**
     * @return string
     */
    public static function getReturnUrl()
    {
        if (is_null(self::$returnUrl)) {
            $url = parse_url(Yii::$app->request->url);
            $returnUrlParams = [];
            if (isset($url['query'])) {
                $parts = explode('&', $url['query']);
                foreach ($parts as $part) {
                    $pieces = explode('=', $part);
                    if (count($pieces) == 2 && strlen($pieces[1]) > 0) {
                        $returnUrlParams[] = $part;
                    }
                }
            }
            if (count($returnUrlParams) > 0) {
                self::$returnUrl = $url['path'] . '?' . implode('&', $returnUrlParams);
            } else {
                self::$returnUrl = $url['path'];
            }
        }
        return self::$returnUrl;
    }

    /**
     * @param ActiveRecord $model Model instance
     * @param string $indexAction Route path to index action
     * @return string Rendered save buttons with redurectUrl!
     */
    public static function saveButtons(ActiveRecord $model, $indexAction='index', $buttonClass='btn-sm', $onlySaveAndBack=false)
    {
        $result = '<div class="form-group no-margin btn-group">';
        if ($onlySaveAndBack === false) {
            $result .=
                Html::a(
                    Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
                    Yii::$app->request->get('returnUrl', [$indexAction, 'id' => $model->id]),
                    ['class' => 'btn btn-default ' . $buttonClass]
                );
        }

        if ($model->isNewRecord && $onlySaveAndBack === false) {
            $result .= Html::submitButton(
                Icon::show('save') . Yii::t('app', 'Save & Go next'),
                [
                    'class' => 'btn btn-success ' . $buttonClass,
                    'name' => 'action',
                    'value' => 'next',
                ]
            );
        }

        $result .=
            Html::submitButton(
                Icon::show('save') . Yii::t('app', 'Save & Go back'),
                [
                    'class' => 'btn btn-warning ' . $buttonClass,
                    'name' => 'action',
                    'value' => 'back',
                ]
            );
        if ($onlySaveAndBack === false) {
            $result .=
                Html::submitButton(
                    Icon::show('save') . Yii::t('app', 'Save'),
                    [
                        'class' => 'btn btn-primary ' . $buttonClass,
                        'name' => 'action',
                        'value' => 'save',
                    ]
                );
        }
        $result .= '</div>';

        return $result;
    }

    public static function toString($value)
    {
        return (string) $value;
    }

    public static function getBackendGridClass($moduleId, $key, $columnNumber, $defaultClass = '')
    {
        /** @var BackendModule $backendModule */
        $backendModule = Yii::$app->getModule('backend');
        if (isset($backendModule->backendEditGrids[$moduleId][$key])) {
            $type = $backendModule->backendEditGrids[$moduleId][$key];
        } else {
            /** @var BaseModule $module */
            $module = Yii::$app->getModule($moduleId);
            if ($module->hasMethod('getBackendGridDefaultValue')) {
                $type = $module->getBackendGridDefaultValue($key);
            } else {
                return $defaultClass;
            }
        }
        switch ($type) {
            case BackendModule::BACKEND_GRID_ONE_COLUMN:
                return 'backend-edit-grid col-lg-12 col-md-12 col-ms-12 col-xs-12';
            case BackendModule::BACKEND_GRID_ONE_TO_ONE:
                return 'backend-edit-grid col-lg-6 col-md-6 col-ms-6 col-xs-12';
            case BackendModule::BACKEND_GRID_TWO_TO_ONE:
                return $columnNumber === 1
                    ? 'backend-edit-grid col-lg-8 col-md-8 col-ms-8 col-xs-12'
                    : 'backend-edit-grid col-lg-4 col-md-4 col-ms-4 col-xs-12';
            case BackendModule::BACKEND_GRID_ONE_TO_TWO:
                return $columnNumber === 1
                    ? 'backend-edit-grid col-lg-4 col-md-4 col-ms-4 col-xs-12'
                    : 'backend-edit-grid col-lg-8 col-md-8 col-ms-8 col-xs-12';
            default:
                return $defaultClass;
        }
    }
}
