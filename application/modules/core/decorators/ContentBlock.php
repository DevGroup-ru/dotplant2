<?php

namespace app\modules\core\decorators;

use app;

use Yii;
use app\modules\core\helpers\ContentBlockHelper;
use yii\caching\TagDependency;
use devgroup\TagDependencyHelper\ActiveRecordHelper;

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
            $dependency = new TagDependency([
                'tags' => [
                    ActiveRecordHelper::getCommonTag(app\modules\core\models\ContentBlock::className()),
                    ActiveRecordHelper::getCommonTag(get_class($params['model']))
                ]
            ]);
            $controller->view->blocks['content'] = ContentBlockHelper::compileContentString(
                $controller->view->blocks['content'],
                get_class($params['model']) . ':' . $params['model']->id,
                $dependency
            );
        }

    }
}