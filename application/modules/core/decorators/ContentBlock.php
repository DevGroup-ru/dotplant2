<?php

namespace app\modules\core\decorators;

use app;

use Yii;

class ContentBlock extends PreDecorator
{

    /**
     * Handle decoration
     * @param \yii\base\Controller $controller
     * @param string $viewFile
     * @param array $params
     * @return void
     */
    public function decorate($controller, $viewFile, $params)
    {
        if (isset($controller->view->blocks['content'])) {
//            $controller->view->blocks['content'] = 'klgjshdfsglkjb';
        }
    }
}