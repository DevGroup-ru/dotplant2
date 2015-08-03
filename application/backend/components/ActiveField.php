<?php

namespace app\backend\components;

use kartik\icons\Icon;
use yii\helpers\Json;
use kartik\helpers\Html;

/**
 * ActiveField extends great kartik ActiveField adding new useful features for DotPlant backend
 * @package app\backend\components
 */
class ActiveField extends \kartik\form\ActiveField
{
    /**
     * Array of field #ids to copy content from.
     * If not null then a copypasting button will be appended to field.
     * Used to quick fill forms with repeated data in backend(ie. Page[title,h1,breadcrumbs_label]).
     *
     * Example use:
     * ``` php
     *
     *  <?=
     *     $form->field($model, 'title',
     *         [
     *             'copyFrom'=>[
     *                 "#page-name",
     *                 "#page-h1",
     *             ]
     *         ]
     *     )
     * ?>
     *
     * ```
     *
     * In this example if we have empty title field - clicking copypaste button will fill it with
     * value of #page-name or if it's empty #page-h1 field.
     *
     * @var null|array
     */
    public $copyFrom = null;

    /**
     * Similar to $copyFrom, but for making slugs
     * @var null|array
     */
    public $makeSlug = null;
    /**
     * Similar to $makeSlug, but for making keys
     * @var null|array
     */
    public $makeKey = null;

    public function init()
    {
        $fields = [
            'copyFrom' => [
                'buttonIdSuffix' => '-copyButton',
                'buttonIcon' => 'code',
                'jsMethodName' => 'Admin.copyFrom',
            ],
            'makeSlug' => [
                'buttonIdSuffix' => '-slugButton',
                'buttonIcon' => 'code',
                'jsMethodName' => 'Admin.makeSlug',
            ],
            'makeKey' => [
                'buttonIdSuffix' => '-keyButton',
                'buttonIcon' => 'code',
                'jsMethodName' => 'Admin.makeKey',
            ],
        ];
        foreach ($fields as $fieldName => $params) {
            if (true === is_array($this->$fieldName)) {
                $id = Html::getInputId($this->model, $this->attribute);
                $buttonId = $id . $params['buttonIdSuffix'];
                $this->addon['append'] = [
                    'content' => Html::button(
                        Icon::show($params['buttonIcon']),
                        ['class' => 'btn btn-primary', 'id' => $buttonId]
                    ),
                    'asButton' => true,
                ];
                $encodedFrom = Json::encode($this->$fieldName);
                $encodedTo = Json::encode('#' . $id);
                $js = <<<EOT
$("#$buttonId").click(function() {
    {$params['jsMethodName']}($encodedFrom, $encodedTo);
});
EOT;
                $this->form->getView()->registerJs($js);
            }
        }
        parent::init();
    }
}
