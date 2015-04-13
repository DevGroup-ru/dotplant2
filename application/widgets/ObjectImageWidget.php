<?php

namespace app\widgets;

use Yii;
use yii\base\Widget;

/**
 * Class ObjectImageWidget
 * @package app\widgets
 */
class ObjectImageWidget extends Widget
{
    /**
     * @var \app\properties\HasProperties|\yii\db\ActiveRecord|null
     */
    public $model = null;
    /**
     * @var string
     */
    public $viewFile = 'img';
    /**
     * @var null|int
     */
    public $limit = null;
    /**
     * @var int
     */
    public $offset = 0;
    /**
     * @var bool
     */
    public $thumbnailOnDemand = false;
    /**
     * @var int
     */
    public $thumbnailWidth = 400;
    /**
     * @var int
     */
    public $thumbnailHeight = 200;

    public function run()
    {

        $cacheKey = "ObjectImageWidget:" . implode(
                "_",
                [
                    $this->model->object->id,
                    $this->model->id,
                    $this->viewFile,
                    $this->limit,
                    $this->offset,
                    $this->thumbnailOnDemand ? '1' : '0',
                    $this->thumbnailWidth,
                    $this->thumbnailHeight
                ]
            );
        $result = Yii::$app->cache->get($cacheKey);
        if ($result === false) {
            if ($this->offset > 0 || !is_null($this->limit)) {
                $images = $this->model->getImages()->limit($this->limit)->offset($this->offset)->all();
            } else {
                $images = $this->model->images;
            }
            $result = $this->render(
                $this->viewFile,
                [
                    'images' => $images,
                    'thumbnailOnDemand' => $this->thumbnailOnDemand,
                    'thumbnailWidth' => $this->thumbnailWidth,
                    'thumbnailHeight' => $this->thumbnailHeight,
                ]
            );
            Yii::$app->cache->set(
                $cacheKey,
                $result,
                86400,
                new \yii\caching\TagDependency(
                    [
                        'tags' => 'Images:' . $this->model->object->id . ':' . $this->model->id
                    ]
                )
            );
        }


        return $result;
    }
}
