<?php

namespace app\backend\components;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use kartik\icons\Icon;

class Helper
{
    private static $returnUrl;

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
}
