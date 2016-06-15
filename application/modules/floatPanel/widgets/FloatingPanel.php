<?php

namespace app\modules\floatPanel\widgets;


use app\modules\floatPanel\events\BeforeRenderEvent;
use kartik\icons\Icon;
use yii\base\Widget;

class FloatingPanel extends  Widget
{
    const EVENT_BEFORE_RENDER = 'before-render';

    public $viewFile = 'FloatingPanel';
    public $bottom = false;

    public function run()
    {
        \app\backend\assets\FrontendEditingAsset::register($this->view);
        $items = [
            [
                'label' => Icon::show('dashboard') . ' ' . \Yii::t('app', 'Backend'),
                'url' => ['/backend/'],
            ]
        ];

        $event = new BeforeRenderEvent();
        $event->items = $items;
        $this->trigger(self::EVENT_BEFORE_RENDER, $event);

        return $this->render(
            $this->viewFile,
            [
                'items' => $event->items,
                'bottom' => $this->bottom
            ]
        );
    }
}