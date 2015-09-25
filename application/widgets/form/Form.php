<?php

namespace app\widgets\form;

use app\models\Object;
use app\models\PropertyGroup;
use kartik\helpers\Html;
use kartik\widgets\Widget;
use yii\bootstrap\Modal;

class Form extends Widget
{
    public $isModal = false;
    public $formId;
    public $route = 'default/submit-form';
    public $options = ['data-type' => 'form-widget','enctype' => 'multipart/form-data'];
    public $statusHeaderName = null;
    private $model;
    private $modal = null;

    public function getModal()
    {
        return $this->modal;
    }

    public function init()
    {
        FormAsset::register($this->view);
        $this->model = \app\models\Form::findById($this->formId);
        if ($this->getId(false) === null) {
            $this->setId('frm' . $this->formId);
        }
        if ($this->model === null) {
            throw new \InvalidArgumentException;
        }

        if ($this->isModal) {
            $this->modal = Modal::begin([
                'id' => 'modal-form-' . $this->id,
                'header' => $this->model->name,
            ]);
        }

    }

    public function run()
    {
        $object = Object::getForClass(\app\models\Form::className());
        $groups = PropertyGroup::getForModel($object->id, $this->formId);
        $view = !empty($this->model->form_view) ? $this->model->form_view : 'form';
        $successView = !empty($this->model->form_success_view) ? $this->model->form_success_view : 'success';

        if (!$this->isModal) {
            echo Html::beginTag(
                'div',
                [
                    'id' => 'form-info-' . $this->id,
                    'style' => 'display: none;',
                ]
            );
            echo $this->render($successView);
            echo '</div>';
        }

        echo $this->render($view, [
            'id' => $this->id,
            'model' => $this->model,
            'groups' => $groups,
            'options' => $this->options,
        ]);
        if ($this->isModal) {
            Modal::end();
            Modal::begin([
                'id' => 'modal-form-info-' . $this->id,
                'size' => Modal::SIZE_SMALL,
                'header' => $this->statusHeaderName ?
                    $this->statusHeaderName :
                    $this->model->name . ' ' . \Yii::t('app', 'status'),
            ]);
            echo $this->render($successView);
            Modal::end();
        }
    }
}
