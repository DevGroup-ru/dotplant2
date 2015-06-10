<?php


namespace app\extensions\DefaultTheme\widgets\ContentBlock;

use app\extensions\DefaultTheme\components\BaseWidget;
use app\modules\core\helpers\ContentBlockHelper;

class Widget extends BaseWidget
{
    public $key = '';
    /**
     * Actual run function for all widget classes extending BaseWidget
     *
     * @return mixed
     */
    public function widgetRun()
    {
        $contentBlock = ContentBlockHelper::fetchChunkByKey($this->key);
        if ($contentBlock === null) {
            return '';
        }

        return $this->render(
            'content-block',
            [
                'content' => $contentBlock,
            ]
        );
    }
}