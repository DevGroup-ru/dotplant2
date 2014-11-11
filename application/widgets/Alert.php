<?php

namespace app\widgets;

use yii\helpers\Html;

/**
 * Alert widget renders a message from session flash. You can set message as following:
 *
 * - \Yii::$app->getSession()->setFlash('error', 'This is the message');
 * - \Yii::$app->getSession()->setFlash('success', 'This is the message');
 * - \Yii::$app->getSession()->setFlash('info', 'This is the message');
 *
 * @author Alexander Makarov <sam@rmcerative.ru>
 */
class Alert extends \yii\bootstrap\Alert
{
    private $doNotRender = false;

    public function init()
    {
        // @todo думаю надо прогонять все это в цикле и рендерить все алерты
        if ($this->body = \Yii::$app->getSession()->getFlash('error', null, true)) {
            Html::addCssClass($this->options, 'alert-danger');
        } elseif ($this->body = \Yii::$app->getSession()->getFlash('success', null, true)) {
            Html::addCssClass($this->options, 'alert-success');
        } elseif ($this->body = \Yii::$app->getSession()->getFlash('info', null, true)) {
            Html::addCssClass($this->options, 'alert-info');
        } elseif ($this->body = \Yii::$app->getSession()->getFlash('warning', null, true)) {
            Html::addCssClass($this->options, 'alert-warning');
        } elseif ($this->body = \Yii::$app->getSession()->getFlash('danger', null, true)) {
            Html::addCssClass($this->options, 'alert-danger');
        } else {
            $this->doNotRender = true;
            return;
        }
        parent::init();
    }

    public function run()
    {
        if (!$this->doNotRender) {
            parent::run();
        }
    }
}
