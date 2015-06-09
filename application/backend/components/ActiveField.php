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

    public function init()
    {
        if (is_array($this->copyFrom)) {
            $id = Html::getInputId($this->model, $this->attribute);
            $buttonId = $id . '-copyButton';
            $this->addon['append']=[
                'content' => Html::button(
                    Icon::show('code'),
                    ['class' => 'btn btn-primary', 'id' => $buttonId]
                ),
                'asButton' => true,
            ];
            $encodedFrom = Json::encode($this->copyFrom);
            $encodedTo = Json::encode('#'.$id);
            $js = <<<EOT
$("#$buttonId").click(function(){
Admin.copyFrom(
    $encodedFrom,
    $encodedTo
);
});
EOT;

            $this->form->getView()->registerJs($js);
        } elseif (is_array($this->makeSlug)) {
            $id = Html::getInputId($this->model, $this->attribute);
            $buttonId = $id . '-slugButton';
            $this->addon['append']=[
                'content' => Html::button(
                    Icon::show('code'),
                    ['class' => 'btn btn-primary', 'id' => $buttonId]
                ),
                'asButton' => true,
            ];
            $encodedFrom = Json::encode($this->makeSlug);
            $encodedTo = Json::encode('#'.$id);
            $js = <<<EOT
$("#$buttonId").click(function(){
Admin.makeSlug(
    $encodedFrom,
    $encodedTo
);
});
EOT;

            $this->form->getView()->registerJs($js);
        }
        parent::init();
    }
}