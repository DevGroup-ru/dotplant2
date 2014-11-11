<?php

namespace app\backend\widgets;

use Yii;
use kartik\helpers\Html;
use kartik\icons\Icon;

class BackendWidget extends \yii\widgets\ContentDecorator
{
    public $title = 'Widget';
    public $icon;
    public $header_append = '';
    public $body = '';
    public $footer = '';
    public $viewFile = '@app/backend/widgets/views/widget.php';

    public function run()
    {
        if (!empty($this->footer)) {
            $this->footer = Html::tag('div', $this->footer, ['class'=>'widget-footer']);
        }
        
        if (!empty($this->icon)) {
            $this->title = Icon::show($this->icon) . $this->title;
        }

        $this->params['title'] = $this->title;
        $this->params['header_append'] = $this->header_append;
        $this->params['footer'] = $this->footer;
        $this->params['_id'] = $this->getId();

        return parent::run();
    }
}
