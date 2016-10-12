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
        if (!Yii::$app->getModule("backend")->isBackend() && !empty($params['model'])) {
            $baseContentKey = get_class($params['model'])
                . ':'
                . (isset($params['model']->id) ? $params['model']->id : '')
                . ':';
            $view = $controller->view;
            $dependency = new TagDependency([
                'tags' => [
                    ActiveRecordHelper::getCommonTag(app\modules\core\models\ContentBlock::className()),
                    ActiveRecordHelper::getCommonTag(get_class($params['model']))
                ]
            ]);
            $view->title = $this->processChunks(
                $view->title,
                $baseContentKey . "title",
                $dependency
            );
            if ($view->blocks !== null) {
                foreach ($view->blocks as $blockName => &$blockContent) {
                    if (!empty($blockContent)) {
                        $blockContent = $this->processChunks(
                            $blockContent,
                            $baseContentKey . $blockName,
                            $dependency
                        );
                    }
                }
            }
        }
    }

    /**
     * @param $content
     * @param $contentKey
     * @param $dependency
     * @return string
     */
    private function processChunks($content, $contentKey, $dependency)
    {
        return ContentBlockHelper::compileContentString(
            $content,
            $contentKey,
            $dependency
        );
    }
}
